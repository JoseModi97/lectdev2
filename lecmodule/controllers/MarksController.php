<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 17-06-2021 12:25:51
 * @modify date 17-06-2021 12:25:51
 * @desc Controller to manage marks
 */

namespace app\controllers;

use app\components\SmisHelper;
use app\models\AssessmentType;
use app\models\Course;
use app\models\CourseWorkAssessment;
use app\models\Department;
use app\models\EmpVerifyView;
use app\models\Marksheet;
use app\models\MarksheetDef;
use app\models\MarksUpload;
use app\models\ProjectDescription;
use app\models\search\CreateMarksSearch;
use app\models\search\MarksPreviewSearch;
use app\models\search\StudentCourseworkSearch;
use app\models\StudentCoursework;
use app\models\TempMarksheet;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\helpers\FileHelper;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

/**
 * Manage CRUD operations for exam and assessment marks
 */
class MarksController extends BaseController
{
    // Set on marks ready for consolidation by cron job
    const PROCESSED_AS_FINAL = 0;

    /**
     * @throws ServerErrorHttpException
     * @throws ForbiddenHttpException
     */
    public function init()
    {
        parent::init();
        SmisHelper::allowAccess(['LEC_SMIS_DEAN', 'LEC_SMIS_HOD', 'LEC_SMIS_LECTURER']);
    }

    /**
     * Set component behaviors
     *
     * @return array component behaviors
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ]
            ]
        ];
    }

    /**
     * For course works and courses with multiple components,
     * we get the list of students who we want to enter cw/exam marks for.
     * @param string $assessmentId
     * @param string $type
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionCreateAssessment(string $assessmentId, string $type): string
    {
        try {
            if (empty($assessmentId)) {
                throw new Exception('The assessment to enter marks for must be specified.');
            }

            if ($type !== 'component' && $type !== 'coursework') {
                throw new Exception('The correct assessment must be specified.');
            }

            $cwAssessment = CourseWorkAssessment::find()->alias('AS')
                ->joinWith(['assessmentType AT'])
                ->where(['AS.ASSESSMENT_ID' => $assessmentId])
                ->asArray()->one();

            $assessmentName = $cwAssessment['assessmentType']['ASSESSMENT_NAME'];
            $marksheetId = $cwAssessment['MARKSHEET_ID'];
            $maximumMarks = $cwAssessment['DIVIDER'];
            $assessmentWeight = $cwAssessment['WEIGHT'];

            //For courses with multiple exams, we must enter cw marks before exam marks for any component
            if ($type === 'component' && !$this->isSupplementary($marksheetId) && SmisHelper::requiresCoursework($marksheetId)) {
                $cwAssessments = CourseWorkAssessment::find()->alias('AS')->joinWith(['assessmentType AT'])
                    ->where(['AS.MARKSHEET_ID' => $marksheetId])
                    ->andWhere(['NOT', ['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT']])
                    ->andWhere(['NOT', ['AT.ASSESSMENT_NAME' => 'EXAM']])
                    ->asArray()->all();

                if (empty($cwAssessments)) {
                    throw new Exception(
                        'This marksheet has no course work available. You must create one or more.');
                } else {
                    foreach ($cwAssessments as $assessment) {
                        $cwMark = StudentCoursework::find()->where(['ASSESSMENT_ID' => $assessment['ASSESSMENT_ID']])
                            ->one();
                        if (is_null($cwMark)) {
                            throw new Exception(
                                'This marksheet has ' . count($cwAssessments) . ' course works. Before 
                                entering exam marks, you must enter marks for all the course works.');
                        }
                    }
                }
            }

            $ratios = SmisHelper::getRatios($marksheetId);

            $marksheetDetails = SmisHelper::marksheetDetails($marksheetId);

            $searchModel = new CreateMarksSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, [
                'marksheetId' => $marksheetId,
                'assessmentId' => $assessmentId,
                'semesterType' => $marksheetDetails['semesterType']
            ]);

            return $this->render('createAssessment', [
                'title' => 'Enter Marks',
                'marksheetId' => $marksheetId,
                'cwWeight' => $ratios['cwRatio'],
                'assessmentId' => $assessmentId,
                'assessmentName' => ($type === 'component') ?
                    str_replace('EXAM_COMPONENT ', '', $assessmentName) : $assessmentName,
                'assessmentWeight' => $assessmentWeight,
                'maximumMarks' => $maximumMarks,
                'marksUploadModel' => new MarksUpload(),
                'courseCode' => $marksheetDetails['courseCode'],
                'courseName' => $marksheetDetails['courseName'],
                'searchModel' => $searchModel,
                'studentMarksheetProvider' => $dataProvider,
                'type' => $type
            ]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * For courses with single exam components, we get the list of students who we want to exam enter marks for.
     * For courses with multiple components, @param string $marksheetId marksheet
     * @return string page to enter exam marks
     * @throws ServerErrorHttpException
     * @throws Exception
     * @see actionCreateAssessment()
     */
    public function actionCreateExam(string $marksheetId): string
    {
        $assessmentId = null;
        $assessmentName = null;
        $maximumMarks = null;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (empty($marksheetId)) {
                throw new Exception('The marksheet to enter exam marks for must be specified.');
            }

            $marksheetDetails = SmisHelper::marksheetDetails($marksheetId);

            $requiresCoursework = SmisHelper::requiresCoursework($marksheetId);

            $isSupplementary = $this->isSupplementary($marksheetId);

            if ($requiresCoursework && !$isSupplementary) {
                /**
                 * Before entering exam marks for non-supplementary courses,
                 * they must have one or more course work(s) defined
                 * and each course work must have one or more record(s) of entered marks
                 */
                $cwAssessments = CourseWorkAssessment::find()->alias('AS')
                    ->joinWith(['assessmentType AT'])
                    ->where(['AS.MARKSHEET_ID' => $marksheetId])->asArray()->all();

                $nonExamAssessments = [];
                if (!empty($cwAssessments)) {
                    foreach ($cwAssessments as $cwAssessment) {
                        if ($cwAssessment['assessmentType']['ASSESSMENT_NAME'] === 'EXAM') {
                            $assessmentId = $cwAssessment['ASSESSMENT_ID'];
                            $assessmentName = 'EXAM';
                            $maximumMarks = $cwAssessment['DIVIDER'];
                            continue;
                        }
                        $nonExamAssessments[] = $cwAssessment;
                    }
                }

                if (empty($nonExamAssessments)) {
                    throw new Exception(
                        'This marksheet has no course work available. You must create one or more.');
                } else {
                    foreach ($nonExamAssessments as $nonExamAssessment) {
                        $scModel = StudentCoursework::find()->select(['COURSE_WORK_ID'])
                            ->where(['ASSESSMENT_ID' => $nonExamAssessment['ASSESSMENT_ID']])->one();
                        if (is_null($scModel)) {
                            throw new Exception(
                                'This marksheet has ' . count($nonExamAssessments) . ' course works. Before 
                                entering exam marks, you must enter marks for all the course works.');
                        }
                    }
                }

                $ratios = SmisHelper::getRatios($marksheetId);
                $examWeight = $ratios['examRatio'];
            } else {
                /**
                 * Exam marks are saved under the assessment of type EXAM which is created when course works are created.
                 * All supplementary and some courses don't require coursework, and so we create this assessment of EXAM type here.
                 * This is only done for marksheets with single exams.
                 */
                $examEntry = $this->createExamEntry($marksheetId, $requiresCoursework, $isSupplementary);
                $assessmentId = $examEntry['assessmentId'];
                $assessmentName = $examEntry['assessmentName'];
                $maximumMarks = $examEntry['maximumMarks'];

                $examWeight = 100;
            }

            $transaction->commit();

            /**
             * There might be a requirement to only enter exam marks for students with marks in each cw.
             * In that case we might have to change the way we return the list of students to enter marks for.
             * This is ok for now.
             */
            $searchModel = new CreateMarksSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, [
                'marksheetId' => $marksheetId,
                'assessmentId' => $assessmentId,
                'semesterType' => $marksheetDetails['semesterType']
            ]);

            return $this->render('createExam', [
                'title' => 'Enter exam marks',
                'marksheetId' => $marksheetId,
                'examWeight' => $examWeight,
                'assessmentId' => $assessmentId,
                'assessmentName' => $assessmentName,
                'maximumMarks' => $maximumMarks,
                'marksUploadModel' => new MarksUpload(),
                'courseCode' => $marksheetDetails['courseCode'],
                'courseName' => $marksheetDetails['courseName'],
                'searchModel' => $searchModel,
                'studentMarksheetProvider' => $dataProvider
            ]);
        } catch (Exception $ex) {
            $transaction->rollBack();
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * Save students exam marks
     * @throws InvalidConfigException
     */
    public function actionSave()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = Yii::$app->request->post();
            $marksheetId = $post['marksheetId'];
            $assessmentId = $post['assessmentId'];
            $marksType = strtoupper($post['marksType']);
            $weight = $post['weight'];
            $maximumMarks = $post['maximumMarks'];
            $students = $post['students'];
            $marksExceedMaximum = false;

            $marksheet = MarksheetDef::find()->select(['COURSE_ID'])->where(['MRKSHEET_ID' => $marksheetId])->asArray()->one();
            $courseId = $marksheet['COURSE_ID'];

            foreach ($students as $student) {
                $marks = $student['marks'];
                if ($marks > $maximumMarks) {
                    $marksExceedMaximum = true;
                    continue;
                }

                $registrationNumber = $student['registrationNumber'];
                $remarks = $student['remarks'];

                if (array_key_exists('projectTitle', $student)) {
                    $this->saveProjectDescription($courseId, $student);
                }

                /**
                 * If marks are already entered for this student for this exam|assessment, skip
                 */
                $scModel = StudentCoursework::find()->select(['COURSE_WORK_ID'])
                    ->where(['REGISTRATION_NUMBER' => $registrationNumber, 'ASSESSMENT_ID' => $assessmentId])->one();
                if (!is_null($scModel)) {
                    continue;
                }

                $markDetails = [
                    'registrationNumber' => $registrationNumber,
                    'assessmentId' => $assessmentId,
                    'marksType' => $marksType,
                    'marks' => $marks,
                    'divider' => $maximumMarks,
                    'weight' => $weight,
                    'remarks' => $remarks,
                ];

                $this->store($markDetails);
            }

            $marksheetAssessment = CourseWorkAssessment::findOne($assessmentId);
            SmisHelper::updateMarksConsolidatedFlag($marksheetAssessment->MARKSHEET_ID);

            $transaction->commit();

            $this->setFlash('success', 'Save Marks', 'Marks saved successfully.');
            if ($marksExceedMaximum) {
                $this->addFlash('danger', 'Save Marks',
                    'Some marks failed to save. They exceed the given weight.');
            }
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        } catch (Exception $ex) {
            $transaction->rollBack();
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return Yii::createObject([
                'class' => 'yii\web\Response',
                'format' => Response::FORMAT_JSON,
                'data' => [
                    'success' => false,
                    'message' => $message
                ]
            ]);
        }
    }

    /**
     * Save excel marks file
     * @return Response
     * @throws ServerErrorHttpException
     */
    public function actionUpload(): Response
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = Yii::$app->request->post();
            $marksheetId = $post['MARKSHEET-ID'];
            $assessmentId = $post['ASSESSMENT-ID'];
            $assessmentName = $post['ASSESSMENT-NAME'];
            $marksType = strtoupper($post['MARKS-TYPE']);
            $weight = $post['WEIGHT'];
            $maximumMarks = $post['MAXIMUM-MARKS'];

            // For courses designated as projects
            $isAProjectCourse = SmisHelper::isAProjectCourse($marksheetId);
            $courseId = null;
            $projectTitleColumn = null;
            $projectHoursColumn = null;

            // Excel file columns to read
            if ($marksType === 'EXAM') {
                $registrationNumberColumn = 'B';
                $marksColumn = 'F';
                $remarksColumn = 'G';

                if ($isAProjectCourse) {
                    $marksheet = MarksheetDef::find()->select(['COURSE_ID'])->where(['MRKSHEET_ID' => $marksheetId])->asArray()->one();
                    $courseId = $marksheet['COURSE_ID'];
                    $projectTitleColumn = 'H';
                    $projectHoursColumn = 'I';
                }
            } elseif ($marksType === 'ASSESSMENT') {
                $registrationNumberColumn = 'B';
                $marksColumn = 'F';
                $remarksColumn = 'G';
            } else {
                throw new Exception('This file can\'t be processed. Marks type must be specified.');
            }

            $marksUpload = new MarksUpload();
            $file = UploadedFile::getInstance($marksUpload, 'marksFile');
            $marksUpload->marksFile = $file;
            if ($marksUpload->validate()) {
                $readMarks = $this->saveAndReadMarksFile($file, $marksheetId, $assessmentName);
            }

            if (empty($readMarks)) {
                throw new Exception('This file is empty and can\'t be processed.');
            }

            $marksThatExceedMaximum = 0;
            foreach ($readMarks as $readMark) {
                if (!array_key_exists($registrationNumberColumn, $readMark) ||
                    !array_key_exists($marksColumn, $readMark)) {
                    continue;
                }

                $registrationNumber = $readMark[$registrationNumberColumn];

                if (!array_key_exists($projectTitleColumn, $readMark)) {
                    $projectTitle = '';
                } else {
                    $projectTitle = $readMark[$projectTitleColumn];
                }

                if (!array_key_exists($projectHoursColumn, $readMark)) {
                    $projectHours = '';
                } else {
                    $projectHours = $readMark[$projectHoursColumn];
                }

                if ($isAProjectCourse) {
                    $student = [
                        'registrationNumber' => $registrationNumber,
                        'projectTitle' => $projectTitle,
                        'projectHours' => $projectHours
                    ];

                    $this->saveProjectDescription($courseId, $student);
                }

                $marks = $readMark[$marksColumn];
                if ($marks > $maximumMarks) {
                    $marksThatExceedMaximum++;
                    continue;
                }

                // Check if student is registered for the marksheet
                if (!$this->isStudentInMarksheet($marksheetId, $registrationNumber)) {
                    continue;
                }

                if (array_key_exists($remarksColumn, $readMark)) {
                    $remarks = $readMark[$remarksColumn];
                } else {
                    $remarks = '';
                }

                $markDetails = [
                    'registrationNumber' => $registrationNumber,
                    'assessmentId' => $assessmentId,
                    'marksType' => $marksType,
                    'marks' => $marks,
                    'divider' => $maximumMarks,
                    'weight' => $weight,
                    'remarks' => $remarks
                ];

                $this->store($markDetails);
            }

            SmisHelper::updateMarksConsolidatedFlag($marksheetId);

            $transaction->commit();

            if ($marksThatExceedMaximum === 0) {
                $this->setFlash('success', 'Upload Marks', 'All marks saved successfully.');
            } else {
                if ($marksThatExceedMaximum === count($readMarks)) {
                    $this->setFlash('danger', 'Upload Marks', 'All marks failed to save because they
                    exceed the maximum weight.');
                } else {
                    $this->setFlash('success', 'Upload Marks', 'Some marks saved successfully.');
                    $this->addFlash('danger', 'Upload Marks', 'Some marks failed to save because they
                    exceed the maximum weight.');
                }
            }
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        } catch (Exception $ex) {
            $transaction->rollBack();
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, '500');
        }
    }

    /**
     * Check if a student is registered for a marksheet
     * @param string $marksheetId
     * @param string $regNumber
     * @return bool
     */
    private function isStudentInMarksheet(string $marksheetId, string $regNumber): bool
    {
        $student = Marksheet::find()->select(['REGISTRATION_NUMBER'])
            ->where(['REGISTRATION_NUMBER' => $regNumber, 'MRKSHEET_ID' => $marksheetId])
            ->one();

        if ($student) {
            return true;
        }

        return false;
    }

    /**
     * Check for duplicate marks in for students in an assessment.
     * Delete all duplicates but one.
     * @param string $assessmentId
     * @return void
     * @throws Throwable
     */
    private function removeDuplicateMarks(string $assessmentId)
    {
        $students = StudentCoursework::find()
            ->select(['REGISTRATION_NUMBER'])
            ->where(['ASSESSMENT_ID' => $assessmentId])
            ->asArray()
            ->all();

        foreach ($students as $student) {
            $regNumber = $student['REGISTRATION_NUMBER'];
            $count = StudentCoursework::find()
                ->where(['REGISTRATION_NUMBER' => $regNumber, 'ASSESSMENT_ID' => $assessmentId])
                ->count();
            if ($count > 1) {
                $studentMark = StudentCoursework::find()->select(['COURSE_WORK_ID'])
                    ->where(['REGISTRATION_NUMBER' => $regNumber, 'ASSESSMENT_ID' => $assessmentId])
                    ->one();
                try {
                    $studentMark->delete();
                } catch (Exception $ex) {
                    $message = $ex->getMessage();
                    if (YII_ENV_DEV) {
                        $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
                    }
                    throw new Exception('Failed to delete duplicate marks. Error: ' . $message);
                }
            }
        }
    }

    /**
     * @param string $courseId
     * @param array $student
     * @return void
     * @throws Exception
     */
    private function saveProjectDescription(string $courseId, array $student)
    {
        $registrationNumber = $student['registrationNumber'];

        $projectDescription = ProjectDescription::find()
            ->where([
                'REGISTRATION_NUMBER' => $registrationNumber,
                'PROJECT_CODE' => $courseId
            ])->one();

        if (!$projectDescription) {
            $projectDescription = new ProjectDescription();
        }

        $projectDescription->REGISTRATION_NUMBER = $registrationNumber;
        $projectDescription->PROJECT_CODE = $courseId;
        $projectDescription->PROJECT_TITLE = $student['projectTitle'];
        $projectDescription->HOURS = $student['projectHours'];
        if (!$projectDescription->save()) {
            if (!$projectDescription->validate()) {
                throw new Exception(SmisHelper::getModelErrors($projectDescription->getErrors()));
            }
            throw new Exception('Failed to update project description.');
        }
    }

    /**
     * Save marks
     * @param array $markDetails
     * @return void
     * @throws Exception
     */
    private function store(array $markDetails)
    {
        $registrationNumber = $markDetails['registrationNumber'];
        $assessmentId = $markDetails['assessmentId'];
        $marksType = $markDetails['marksType'];
        $marks = $markDetails['marks'];
        $divider = $markDetails['divider'];
        $weight = $markDetails['weight'];
        $remarks = $markDetails['remarks'];

        /**
         * If a student has marks for this assessment/exam entered, Update the record.
         * This is used for marks upload with Excel.
         */
        $studentCW = StudentCoursework::find()
            ->where(['REGISTRATION_NUMBER' => $registrationNumber, 'ASSESSMENT_ID' => $assessmentId])->one();

        if (is_null($studentCW)) {
            $studentCW = new StudentCoursework();
        }

        $studentCW->REGISTRATION_NUMBER = $registrationNumber;
        $studentCW->ASSESSMENT_ID = $assessmentId;
        $studentCW->RAW_MARK = $marks;

        $assessment = CourseWorkAssessment::findOne($assessmentId);

        // For assessments, we compute the marks against the weight. Exam marks are entered as they are.
        if ($marksType === 'ASSESSMENT') {
            /**
             * Marks for cats, assignments and exam components are processed the same way.
             * However, for exam components we save the MARK_TYPE as EXAM
             */
            $assessmentType = AssessmentType::findOne($assessment->ASSESSMENT_TYPE_ID);
            if (strpos($assessmentType->ASSESSMENT_NAME, 'EXAM_COMPONENT') !== false) {
                $studentCW->MARK_TYPE = 'EXAM';
            } else {
                $studentCW->MARK_TYPE = $marksType;
            }

            $studentCW->MARK = round(((int)$marks / $divider) * $weight, 2);
        } elseif ($marksType === 'EXAM') {
            $studentCW->MARK_TYPE = $marksType;

            if (SmisHelper::isSupplementary($assessment->MARKSHEET_ID)) {
                $passmark = SmisHelper::getPassmark($assessment->MARKSHEET_ID);
                if (intval($marks) >= $passmark) {
                    $studentCW->MARK = $passmark;
                } else {
                    $studentCW->MARK = $marks;
                }
            } else {
                $studentCW->MARK = $marks;
            }
        }

        if ($remarks !== '') {
            $studentCW->REMARKS = $remarks;
        }

        $studentCW->USER_ID = $this->payrollNo;
        $studentCW->DATE_ENTERED = new Expression('CURRENT_DATE');

        if (!$studentCW->save()) {
            $msg = json_encode($studentCW->getErrors());
            throw new Exception('Marks failed to save. ' . $msg);
        }
    }

    /**
     * we get the list of students with assessment marks.
     * @param string $assessmentId
     * @param string $type
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionEditAllAssessment(string $assessmentId, string $type): string
    {
        try {
            if (empty($assessmentId)) {
                throw new Exception('The assessment to edit marks for must be specified.');
            }

            if ($type !== 'component' && $type !== 'coursework') {
                throw new Exception('The correct assessment type must be specified.');
            }

            $assessment = CourseWorkAssessment::find()->alias('AS')->joinWith(['assessmentType AT'])
                ->where(['AS.ASSESSMENT_ID' => $assessmentId])->asArray()->one();
            $marksheetId = $assessment['MARKSHEET_ID'];
            $assessmentName = $assessment['assessmentType']['ASSESSMENT_NAME'];
            $maximumMarks = $assessment['DIVIDER'];
            $assessmentWeight = $assessment['WEIGHT'];

            //For courses with multiple exams, we must enter cw marks before editing exam marks for any component
            if ($type === 'component' && !$this->isSupplementary($marksheetId) && SmisHelper::requiresCoursework($marksheetId)) {
                $cwAssessments = CourseWorkAssessment::find()->alias('AS')->joinWith(['assessmentType AT'])
                    ->where(['AS.MARKSHEET_ID' => $marksheetId])
                    ->andWhere(['NOT', ['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT']])
                    ->andWhere(['NOT', ['AT.ASSESSMENT_NAME' => 'EXAM']])
                    ->asArray()->all();

                if (empty($cwAssessments)) {
                    throw new Exception(
                        'This marksheet has no course work available. You must create one or more.');
                } else {
                    foreach ($cwAssessments as $cwAssessment) {
                        $cwMark = StudentCoursework::find()->where(['ASSESSMENT_ID' => $cwAssessment['ASSESSMENT_ID']])
                            ->one();
                        if (is_null($cwMark)) {
                            throw new Exception(
                                'This marksheet has ' . count($cwAssessments) . ' course works. Before 
                                entering exam marks, you must enter marks for all the course works.');
                        }
                    }
                }
            }

            $marksheetDetails = SmisHelper::getMarksheetDetails($marksheetId);

            $searchModel = new StudentCourseworkSearch();
            $cwProvider = $searchModel->search(Yii::$app->request->queryParams, [
                'assessmentId' => $assessmentId
            ]);

            return $this->render('editAssessment', [
                'title' => 'Edit Marks',
                'marksheetId' => $marksheetId,
                'assessmentId' => $assessmentId,
                'assessmentName' => ($type === 'component') ?
                    str_replace('EXAM_COMPONENT ', '', $assessmentName) : $assessmentName,
                'assessmentWeight' => $assessmentWeight,
                'maximumMarks' => $maximumMarks,
                'courseCode' => $marksheetDetails['courseCode'],
                'courseName' => $marksheetDetails['courseName'],
                'searchModel' => $searchModel,
                'cwProvider' => $cwProvider,
                'type' => $type
            ]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * For courses with single exam components, we get the list of students with exam marks.
     * For courses with multiple components, @param string $marksheetId marksheet
     * @return string page to edit exam marks
     * @throws ServerErrorHttpException
     * @see actionEditAllAssessment()
     */
    public function actionEditAllExam(string $marksheetId): string
    {
        try {
            if (empty($marksheetId)) {
                throw new Exception('The marksheet to edit exam marks for must be specified.');
            }

            $assessment = CourseWorkAssessment::find()->alias('AS')
                ->joinWith(['assessmentType AT'])
                ->where(['AS.MARKSHEET_ID' => $marksheetId, 'AT.ASSESSMENT_NAME' => 'EXAM'])
                ->asArray()->one();

            if (is_null($assessment)) {
                throw new Exception('This course is missing exam marks.');
            }

            $assessmentId = $assessment['ASSESSMENT_ID'];
            $assessmentName = $assessment['assessmentType']['ASSESSMENT_NAME'];
            $maximumMarks = $assessment['DIVIDER'];

            $mkModel = MarksheetDef::find()->alias('MK')
                ->select(['MK.MRKSHEET_ID', 'MK.COURSE_ID'])
                ->where(['MK.MRKSHEET_ID' => $marksheetId])
                ->joinWith(['course CS' => function (ActiveQuery $q) {
                    $q->select(['CS.COURSE_ID', 'CS.COURSE_CODE', 'CS.COURSE_NAME']);
                }
                ], true, 'INNER JOIN')
                ->asArray()->one();

            if (SmisHelper::requiresCoursework($marksheetId) && !$this->isSupplementary($marksheetId)) {
                $ratios = SmisHelper::getRatios($marksheetId);
                $examWeight = $ratios['examRatio'];
            } else {
                $examWeight = 100;
            }

            $searchModel = new StudentCourseworkSearch();
            $cwProvider = $searchModel->search(Yii::$app->request->queryParams, [
                'assessmentId' => $assessmentId
            ]);

            return $this->render('editExam', [
                'title' => 'Edit Marks',
                'marksheetId' => $marksheetId,
                'examWeight' => $examWeight,
                'assessmentId' => $assessmentId,
                'assessmentName' => $assessmentName,
                'maximumMarks' => $maximumMarks,
                'courseCode' => $mkModel['course']['COURSE_CODE'],
                'courseName' => $mkModel['course']['COURSE_NAME'],
                'searchModel' => $searchModel,
                'cwProvider' => $cwProvider,
            ]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * Get student's marks to update
     * @param string $studentCourseworkId
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionEdit(string $studentCourseworkId): string
    {
        try {
            $scModel = StudentCoursework::find()
                ->select(['ASSESSMENT_ID', 'COURSE_WORK_ID', 'MARK', 'RAW_MARK', 'REMARKS', 'REGISTRATION_NUMBER'])
                ->where(['COURSE_WORK_ID' => $studentCourseworkId])
                ->one();

            $assessment = CourseWorkAssessment::find()->where(['ASSESSMENT_ID' => $scModel->ASSESSMENT_ID])->one();
            $assessmentType = AssessmentType::findOne($assessment->ASSESSMENT_TYPE_ID);
            $marksheetId = $assessment->MARKSHEET_ID;

            $marksheet = MarksheetDef::find()->select(['COURSE_ID'])->where(['MRKSHEET_ID' => $marksheetId])->asArray()->one();
            $courseId = $marksheet['COURSE_ID'];

            return $this->renderAjax('_editMarks', [
                'title' => 'Edit Marks',
                'scModel' => $scModel,
                'maximumMarks' => $assessment->DIVIDER,
                'marksheetId' => $marksheetId,
                'assessmentName' => $assessmentType->ASSESSMENT_NAME,
                'courseId' => $courseId
            ]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * Update marks
     * @return Response
     * @throws ServerErrorHttpException
     */
    public function actionUpdate(): Response
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            $courseworkId = $post['COURSE-WORK-ID'];
            if ($courseworkId === '' || is_null($courseworkId)) {
                throw new Exception('Failed to update. The record must be specified.');
            }

            $marks = $post['MARKS'];
            if ($marks === '') {
                throw new Exception('Failed to update. The new marks must be entered.');
            }

            $scModel = StudentCoursework::findOne($courseworkId);
            $assessment = CourseWorkAssessment::find()->alias('AS')->joinWith(['assessmentType AT'])
                ->where(['AS.ASSESSMENT_ID' => $scModel->ASSESSMENT_ID])->asArray()->one();

            $maximumMarks = $assessment['DIVIDER'];
            if ($marks > $maximumMarks) {
                throw new Exception('Failed to update. Marks must not exceed the maximum.');
            }

            $assessmentName = $assessment['assessmentType']['ASSESSMENT_NAME'];
            if ($assessmentName === 'EXAM') {
                if (SmisHelper::isSupplementary($assessment['MARKSHEET_ID'])) {
                    $passmark = SmisHelper::getPassmark($assessment['MARKSHEET_ID']);
                    if (intval($marks) >= $passmark) {
                        $scModel->MARK = $passmark;
                    } else {
                        $scModel->MARK = $marks;
                    }
                } else {
                    $scModel->MARK = $marks;
                }

                /**
                 * Update project description if the course is a project
                 */
                if (SmisHelper::isAProjectCourse($assessment['MARKSHEET_ID'])) {

                    $marksheet = MarksheetDef::find()->select(['COURSE_ID'])
                        ->where(['MRKSHEET_ID' => $assessment['MARKSHEET_ID']])->asArray()->one();

                    $courseId = $marksheet['COURSE_ID'];

                    $this->saveProjectDescription($courseId, [
                        'registrationNumber' => $scModel->REGISTRATION_NUMBER,
                        'projectTitle' => $post['TITLE'] ?? '',
                        'projectHours' => $post['HOURS'] ?? 0
                    ]);
                }

            } else {
                $scModel->MARK = round(($marks / $maximumMarks) * $assessment['WEIGHT'], 2);
            }

            $scModel->RAW_MARK = $marks;
            $scModel->REMARKS = $post['REMARKS'];
            $scModel->USER_ID = $this->payrollNo; // User updating marks
            $saved = $scModel->save();
            if ($saved) {
                SmisHelper::updateMarksConsolidatedFlag($assessment['MARKSHEET_ID']);
            }
            $transaction->commit();

            if ($saved) {
                $this->setFlash('success', 'Update Marks', 'This record has been updated successfully!');
            } else {
                $this->setFlash('danger', 'Update Marks', 'This record failed to update!');
            }
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        } catch (Exception $ex) {
            $transaction->rollBack();
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * Get all marks in an assessment
     * @param string|null $assessmentId
     * @param string|null $type
     * @param string|null $marksheetId
     * @return string page to delete marks
     * @throws ServerErrorHttpException
     */
    public function actionMarksToDelete(string $assessmentId = null, string $type = null, string $marksheetId = null): string
    {
        try {
            if (is_null($marksheetId)) {
                $marksForSingleExam = false; // set to true when reading exam marks for marksheets with single exam components
                if (empty($assessmentId)) {
                    throw new Exception('The assessment to delete marks for must be specified.');
                }

                if ($type !== 'component' && $type !== 'coursework') {
                    throw new Exception('You must provide a proper assessment type.');
                }
            } else {
                $marksForSingleExam = true;
                $assessment = CourseWorkAssessment::find()->alias('AS')->joinWith(['assessmentType AT'])
                    ->where(['AS.MARKSHEET_ID' => $marksheetId, 'AT.ASSESSMENT_NAME' => 'EXAM'])
                    ->asArray()->one();
                if (empty($assessment)) {
                    throw new Exception('This marksheet has no exam entry to delete marks from.');
                }
                $assessmentId = $assessment['ASSESSMENT_ID'];
            }

            $assessment = CourseWorkAssessment::find()->alias('AS')->joinWith(['assessmentType AT'])
                ->where(['AS.ASSESSMENT_ID' => $assessmentId])->asArray()->one();
            $marksheetId = $assessment['MARKSHEET_ID'];
            $assessmentName = $assessment['assessmentType']['ASSESSMENT_NAME'];

            $mkModel = MarksheetDef::find()->alias('MK')
                ->select(['MK.MRKSHEET_ID', 'MK.COURSE_ID'])
                ->where(['MK.MRKSHEET_ID' => $marksheetId])
                ->joinWith(['course CS' => function (ActiveQuery $q) {
                    $q->select(['CS.COURSE_ID', 'CS.COURSE_CODE', 'CS.COURSE_NAME']);
                }], true, 'INNER JOIN')
                ->asArray()
                ->one();

            $searchModel = new StudentCourseworkSearch();
            $cwProvider = $searchModel->search(Yii::$app->request->queryParams, ['assessmentId' => $assessmentId]);

            return $this->render('deleteAssessment', [
                'title' => 'Delete Marks',
                'assessmentId' => $assessmentId,
                'marksheetId' => $marksheetId,
                'assessmentName' => ($type === 'component') ?
                    str_replace('EXAM_COMPONENT ', '', $assessmentName) : $assessmentName,
                'courseCode' => $mkModel['course']['COURSE_CODE'],
                'courseName' => $mkModel['course']['COURSE_NAME'],
                'searchModel' => $searchModel,
                'cwProvider' => $cwProvider,
                'type' => $type,
                'marksForSingleExam' => $marksForSingleExam
            ]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * Delete marks
     * @return Response
     * @throws Throwable
     */
    public function actionDelete(): Response
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = Yii::$app->request->post();
            $studentCourseWork = StudentCoursework::findOne($post['id']);
            if (!is_null($studentCourseWork)) {
                $courseWorksAssessment = CourseWorkAssessment::find()->select(['MARKSHEET_ID'])
                    ->where(['ASSESSMENT_ID' => $studentCourseWork->ASSESSMENT_ID])->asArray()->one();

                if (!$studentCourseWork->delete()) {
                    throw new Exception('Marks failed to delete.');
                }

                SmisHelper::updateMarksConsolidatedFlag($courseWorksAssessment['MARKSHEET_ID']);

                $tempMarksheet = TempMarksheet::find()
                    ->where([
                        'MRKSHEET_ID' => $courseWorksAssessment['MARKSHEET_ID'],
                        'REGISTRATION_NUMBER' => $studentCourseWork->REGISTRATION_NUMBER
                    ])->one();

                if (!is_null($tempMarksheet)) {
                    if (!$tempMarksheet->delete()) {
                        throw new Exception('Consolidated marks failed to delete.');
                    }
                }

                $marksheetStudent = Marksheet::find()
                    ->where([
                        'MRKSHEET_ID' => $courseWorksAssessment['MARKSHEET_ID'],
                        'REGISTRATION_NUMBER' => $studentCourseWork->REGISTRATION_NUMBER
                    ])->one();

                if ($marksheetStudent) {
                    $marksheetStudent->COURSE_MARKS = 0;
                    $marksheetStudent->EXAM_MARKS = 0;
                    $marksheetStudent->FINAL_MARKS = 0;
                    $marksheetStudent->GRADE = null;
                    $marksheetStudent->USERID = "'" . $this->payrollNo . "'";
                    $marksheetStudent->LAST_UPDATE = new Expression('CURRENT_DATE');
                    if (!$marksheetStudent->save()) {
                        throw new Exception('Consolidated marks failed to update.');
                    }
                }

                SmisHelper::updateAndReadConsolidatedMarks($courseWorksAssessment['MARKSHEET_ID']);

                $transaction->commit();

            } else {
                throw new Exception('Marks to delete not found');
            }

            $this->setFlash('success', 'Status update', 'Marks deleted successfully.');
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        } catch (Exception $ex) {
            $transaction->rollBack();
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return $this->asJson(['status' => 500, 'message' => $message]);
        }
    }

    /**
     * Submit marks as final by the lecturer
     * @return Response|string
     * @throws InvalidConfigException
     */
    public function actionSubmit()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = Yii::$app->request->post();
            $assessmentId = $post['assessmentId'];

            $assessment = CourseWorkAssessment::find()->alias('AS')->joinWith(['assessmentType AT'])
                ->where(['AS.ASSESSMENT_ID' => $assessmentId])->asArray()->one();
            $assessmentName = $assessment['assessmentType']['ASSESSMENT_NAME'];
            $marksheetId = $assessment['MARKSHEET_ID'];
            $isSupplementary = $this->isSupplementary($marksheetId);

            if (strpos($assessmentName, 'EXAM_COMPONENT') !== false) {
                $isExamComponent = true;
                $assessmentName = str_replace('EXAM_COMPONENT ', '', $assessmentName);
            } else {
                $isExamComponent = false;
            }

            // All course work marks for this exam's marksheet must submitted before submitting any exam marks
            if ($isExamComponent || $assessmentName === 'EXAM') {
                if ($isExamComponent) {
                    $cwAssessments = CourseWorkAssessment::find()->alias('AS')->joinWith(['assessmentType AT'])
                        ->where(['AS.MARKSHEET_ID' => $marksheetId])
                        ->andWhere(['NOT', ['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT']])
                        ->andWhere(['NOT', ['AT.ASSESSMENT_NAME' => 'EXAM']])
                        ->asArray()->all();
                } else {
                    $cwAssessments = CourseWorkAssessment::find()->alias('AS')->joinWith(['assessmentType AT'])
                        ->where(['AS.MARKSHEET_ID' => $marksheetId])
                        ->andWhere(['NOT', ['AT.ASSESSMENT_NAME' => 'EXAM']])
                        ->asArray()->all();
                }

                if (!$isSupplementary && SmisHelper::requiresCoursework($marksheetId)) {
                    if (count($cwAssessments) > 0) {
                        foreach ($cwAssessments as $cwAssessment) {
                            $marksPending = SmisHelper::marksPending($cwAssessment['ASSESSMENT_ID'],
                                'LECTURER_APPROVAL_STATUS');
                            if ($marksPending) {
                                throw new Exception(
                                    'Before submitting the exam marks, all coursework marks must be submitted.');
                            }
                        }
                    } else {
                        throw new Exception('This marksheet has 0 course work. You must create 1 or more.');
                    }
                }
            }

            /**
             * Only course works can be processed as FINAL at the lecturer level.
             * To identify this as cw, the assessment must not be an exam component,
             * and it must not be a single exam either
             */
            $cwMarks = StudentCoursework::find()->where(['ASSESSMENT_ID' => $assessmentId])->all();
            if (count($cwMarks) > 0) {
                foreach ($cwMarks as $cwMark) {
                    if (!$isExamComponent && $assessmentName !== 'EXAM') {
                        $cwMark->PROCESSED_AS_FINAL = self::PROCESSED_AS_FINAL;
                    }
                    $cwMark->LECTURER_APPROVAL_STATUS = 'APPROVED';
                    if (!$cwMark->save()) {
                        throw new Exception('These marks failed to submit.');
                    }
                }
            } else {
                throw new Exception('Failed. Attempting to submit empty marks.');
            }

            // Only send notifications to the HOD for exam marks submissions
            if ($assessmentName === 'EXAM' || $isExamComponent) {
                $mkModel = MarksheetDef::find()->select(['COURSE_ID'])->where(['MRKSHEET_ID' => $marksheetId])->one();
                $course = Course::find()->select(['COURSE_CODE', 'COURSE_NAME', 'DEPT_CODE'])
                    ->where(['COURSE_ID' => $mkModel->COURSE_ID])->one();
                $lecturer = EmpVerifyView::find()
                    ->select(['PAYROLL_NO', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE', 'EMAIL'])
                    ->where(['PAYROLL_NO' => $this->payrollNo, 'STATUS_DESC' => 'ACTIVE'])
                    ->one();
                $department = Department::findOne($course->DEPT_CODE);

                $lecturerName = $lecturer->EMP_TITLE . ' ' . $lecturer->SURNAME . ' ' . $lecturer->OTHER_NAMES;
                $deptEmail = $department->EMAIL;
                $courseName = $course->COURSE_NAME;
                $courseCode = $course->COURSE_CODE;
                if ($isSupplementary) {
                    $assessmentName = 'SUPPLEMENTARY ' . $assessmentName;
                }
                $email = [
                    'recipientEmail' => $deptEmail,
                    'subject' => $courseCode . ' EXAM MARKS SUBMISSION',
                    'params' => [
                        'recipient' => 'HOD ' . $department->DEPT_NAME,
                        'courseCode' => $courseCode,
                        'courseName' => $courseName,
                        'submittedBy' => $lecturerName,
                        'assessmentName' => $assessmentName,
                    ]
                ];
                $emails = [];
                $emails[] = $email;
                $layout = '@app/mail/layouts/html';
                $view = '@app/mail/views/marksSubmitted';
                SmisHelper::sendEmails($emails, $layout, $view);
            }

            $this->setFlash('success', 'Submit marks', 'These marks have been submitted successfully.');
            $transaction->commit();
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        } catch (Exception $ex) {
            $transaction->rollBack();
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return Yii::createObject([
                'class' => 'yii\web\Response',
                'format' => Response::FORMAT_JSON,
                'data' => [
                    'success' => false,
                    'message' => $message
                ]
            ]);
        }
    }

    /**
     * The consolidated marks from here are for lecturers' preview. Not for publishing to smis.
     * @throws ServerErrorHttpException
     */
    public function actionConsolidate(): string
    {
        try {
            $queryParams = Yii::$app->request->queryParams;
            $marksheetId = $queryParams['marksheetId'];

            if (!SmisHelper::marksheetExists($marksheetId)) {
                throw new Exception('Marksheet: ' . $marksheetId . ' is not found in the timetable.');
            }

            /**
             * Only try to consolidate marks when first using this feature.
             * This prevents marks consolidation when searching on the table grid from the UI.
             * User must search against what they see.
             */
            if (!array_key_exists('MarksPreviewSearch', $queryParams)) {
                SmisHelper::updateAndReadConsolidatedMarks($marksheetId);
            }

            $marksPreviewSearch = new MarksPreviewSearch();
            $marksPreviewProvider = $marksPreviewSearch->search($queryParams);

            $marksheetDef = MarksheetDef::find()->select(['COURSE_ID'])
                ->where(['MRKSHEET_ID' => $marksheetId])->asArray()->one();

            $course = Course::find()->select(['COURSE_CODE', 'COURSE_NAME'])
                ->where(['COURSE_ID' => $marksheetDef['COURSE_ID']])->asArray()->one();

            $title = 'Consolidated marks for ' . $course['COURSE_CODE'];

            return $this->render('marksPreview', [
                'title' => $title,
                'marksPreviewSearch' => $marksPreviewSearch,
                'marksPreviewProvider' => $marksPreviewProvider,
                'panelHeading' => $title,
                'course' => $course,
                'marksheetId' => $marksheetId
            ]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * Save the uploaded Excel file marks then read marks from it.
     * @param UploadedFile $file
     * @param string $marksheetId
     * @param string $assessmentName
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \yii\base\Exception
     */
    private function saveAndReadMarksFile(UploadedFile $file, string $marksheetId, string $assessmentName): array
    {
        // Amend the filename. Append the datetime of upload to make each name unique
        $dateTime = preg_replace('/\s/', '_', SmisHelper::getDateTime('now', true));
        $dateTime = preg_replace('/\-/', '_', $dateTime);
        $dateTime = preg_replace('/\s/', '_', $dateTime);
        $dateTime = preg_replace('/\:/', '_', $dateTime);
        $fileName = preg_replace('/\s/', '_', $file->baseName) . '_' . $dateTime . '.' .
            $file->extension;

        // Create upload directory and save file
        $marksheetId = preg_replace('/\//', '_', $marksheetId);
        $assessmentName = preg_replace('/\s/', '_', $assessmentName);
        $path = Yii::getAlias('@app') . '/uploads/marks/' . $this->payrollNo . '/' . $marksheetId . '/' .
            $assessmentName;
        FileHelper::createDirectory($path);
        $file->saveAs($path . '/' . $fileName);

        // Read marks
        $newSheetData = [];
        $inputFileName = $path . '/' . $fileName;
        $inputFileType = IOFactory::identify($inputFileName);
        $reader = IOFactory::createReader($inputFileType);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($inputFileName);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true,
            true);

        // Some cells contain null values. Take these out
        foreach ($sheetData as $data) {
            $dataKeys = array_keys($data);
            foreach ($dataKeys as $key) {
                if (is_null($data[$key])) {
                    unset($data[$key]);
                }
            }
            $newSheetData[] = $data;
        }
        // Remove column titles
        array_shift($newSheetData);

        return $newSheetData;
    }
}
