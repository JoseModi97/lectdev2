<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * Manage marks approvals
 */

namespace app\controllers;

use app\components\SmisHelper;
use app\models\ApprovalLevel;
use app\models\AssessmentType;
use app\models\Course;
use app\models\CourseWorkAssessment;
use app\models\Department;
use app\models\EmpVerifyView;
use app\models\ExamUpdateApproval;
use app\models\Faculty;
use app\models\Group;
use app\models\LevelOfStudy;
use app\models\MarksApprovalFilter;
use app\models\Marksheet;
use app\models\MarksheetDef;
use app\models\search\CoursesToApproveSearch;
use app\models\search\MarksApprovalAssessmentSearch;
use app\models\search\MarksApprovalSearch;
use app\models\search\MarksSearch;
use app\models\search\MarksToApproveSearch;
use app\models\search\ProgrammesInAFacultySearch;
use app\models\Semester;
use app\models\StudentCoursework;
use app\models\TempMarksheet;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class MarksApprovalController extends BaseController
{
    const MARKS_COMPLETE = 1;
    const MARKS_PUBLISHED = 1;
    const MARKS_NOT_PUBLISHED = 0;

    /**
     * Set component behaviors
     * @return array[] component behaviors
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
                    ],
                ],
            ],
        ];
    }

    /**
     * @throws ServerErrorHttpException
     * @throws ForbiddenHttpException
     */
    public function init()
    {
        parent::init();
        SmisHelper::allowAccess(['LEC_SMIS_HOD', 'LEC_SMIS_DEAN']);
    }

    /**
     * Get programmes in a user's faculty
     * @param string $level
     * @return string page to display programmes in a faculty
     * @throws ServerErrorHttpException
     */
    public function actionProgrammesInFaculty(string $level): string
    {
        try {
            $searchModel = new ProgrammesInAFacultySearch();
            $programmesProvider = $searchModel->search(Yii::$app->request->queryParams, ['facCode' => $this->facCode]);

            return $this->render('programmesInFaculty', [
                'title' => 'Programmes in the faculty',
                'searchModel' => $searchModel,
                'programmesProvider' => $programmesProvider,
                'deptCode' => $this->deptCode,
                'facCode' => $this->facCode,
                'level' => $level,
                'panelHeading' => 'Programmes in the ' . strtolower($this->facName)
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
     * Set some filters for marks approval be used by the original approval process
     * @param string $approvalLevel
     * @param string $degreeCode
     * @return string display page to set additional filters for marks approval
     * @throws ServerErrorHttpException
     */
    public function actionFilters(string $approvalLevel, string $degreeCode): string
    {
        try {
            if ($approvalLevel !== 'hod' && $approvalLevel !== 'dean') {
                throw new Exception('The correct approval level must be provided.');
            }

            $filter = new MarksApprovalFilter();
            $filter->approvalLevel = $approvalLevel;
            $filter->degreeCode = $degreeCode;

            return $this->render('filter', [
                'title' => 'Course filters',
                'filter' => $filter
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
     * Set some filters for marks approval to be used by the new approval process
     * @param string $level
     * @param string $filtersInterface
     * @param string|null $resultsType
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionNewFilters(string $level, string $filtersInterface, string $resultsType = null): string
    {
        try {
            if ($level !== 'hod' && $level !== 'dean') {
                throw new Exception('The correct approval level must be provided.');
            }

            if ($filtersInterface !== '1' && $filtersInterface !== '2') {
                throw new Exception('The correct filters ui must be provided.');
            }

            if ($resultsType !== null && $resultsType !== 'pending' && $resultsType !== 'approved') {
                throw new Exception('The correct result type must be provided.');
            }

            $filter = new MarksApprovalFilter();
            $filter->approvalLevel = $level;

            $filterPage = 'interfaceOneFilters';
            if ($filtersInterface === '2') {
                $filterPage = 'interfaceTwoFilters';
            }

            return $this->render($filterPage, [
                'title' => 'Course filters',
                'filter' => $filter,
                'filtersInterface' => $filtersInterface,
                'resultsType' => $resultsType
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
     * Get available academic years configured for this system
     * @return Response
     */
    public function actionGetAcademicYears(): Response
    {
        try {
            return $this->asJson(['status' => 200, 'academicYears' => Yii::$app->params['academicYears']]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return $this->asJson(['status' => 500, 'message' => $message]);
        }
    }

    /**
     * Get available semesters in an academic year and programme
     * @return Response
     */
    public function actionGetSemesters(): Response
    {
        try {
            $get = Yii::$app->request->get();

            /**
             * A programme might have multiple timetables under the same semester. Remove duplicate semesters.
             * Therefore, we don't read semester id
             */
            $semesters = Semester::find()->alias('SM')
                ->select(['SM.SEMESTER_CODE', 'SM.DESCRIPTION_CODE'])
                ->joinWith(['semesterDescription SD' => function ($q) {
                    $q->select([
                        'SD.DESCRIPTION_CODE',
                        'SD.SEMESTER_DESC'
                    ]);
                }], true, 'INNER JOIN')
                ->where([
                    'SM.ACADEMIC_YEAR' => $get['year'],
                    'SM.DEGREE_CODE' => $get['degreeCode']
                ])
                ->distinct()
                ->orderBy(['SM.SEMESTER_CODE' => SORT_ASC])
                ->asArray()
                ->all();

            return $this->asJson(['semesters' => $semesters]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return $this->asJson(['status' => 500, 'message' => $message]);
        }
    }

    /**
     * @return string page to display courses with submitted marks
     * @throws ServerErrorHttpException
     */
    public function actionCoursesWithMarks(): string
    {
        try {
            $filter = new MarksApprovalFilter();

            $session = Yii::$app->session;

            // Save marks approval filters in the session for retrieval on page redirects
            if (!empty(Yii::$app->request->get()['MarksApprovalFilter'])) {
                $session['MarksApprovalFilter'] = Yii::$app->request->get();
            }

            if (!$filter->load($session->get('MarksApprovalFilter')) || !$filter->validate()) {
                throw new Exception('Failed to load filters for marks approval.');
            }

            $approvalLevel = $filter->approvalLevel;

            $searchModel = new MarksApprovalSearch();
            $marksProvider = $searchModel->search(Yii::$app->request->queryParams, [
                'filter' => $filter,
                'facCode' => $this->facCode,
                'deptCode' => $this->deptCode
            ]);

            $groups = Group::find()->select(['GROUP_CODE', 'GROUP_NAME'])->all();
            $listOfGroups = ArrayHelper::map($groups, 'GROUP_CODE', function ($group) {
                return $group->GROUP_NAME;
            });

            $levelsOfStudy = LevelOfStudy::find()->all();
            $listOfLevels = ArrayHelper::map($levelsOfStudy, 'LEVEL_OF_STUDY', function ($level) {
                return $level->NAME;
            });

            $panelHeading = 'Courses with marks in the department of ' . strtolower($this->deptName);
            if ($approvalLevel === 'dean') {
                $panelHeading = 'Courses with marks in the ' . strtolower($this->facName);
            }

            return $this->render('courses', [
                'title' => 'Courses with marks',
                'searchModel' => $searchModel,
                'marksProvider' => $marksProvider,
                'deptCode' => $this->deptCode,
                'deptName' => $this->deptName,
                'facCode' => $this->facCode,
                'facName' => $this->facName,
                'level' => $approvalLevel,
                'filter' => $filter,
                'panelHeading' => $panelHeading,
                'listOfGroups' => $listOfGroups,
                'listOfLevels' => $listOfLevels
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
     * Create the new interface for marks approval
     * @return string page to display courses with submitted marks
     * @throws ServerErrorHttpException
     */
    public function actionCoursesToApprove(): string
    {
        try {
            $get = Yii::$app->request->get();
            $filtersInterface = $get['filtersInterface'];
            $resultsType = $get['resultsType'];

            $filter = new MarksApprovalFilter();

            $session = Yii::$app->session;

            // Save marks approval filters in the session for retrieval on page redirects
            if (!empty($get['MarksApprovalFilter'])) {
                $session['MarksApprovalFilter'] = $get;
            }

            if (!$filter->load($session->get('MarksApprovalFilter')) || !$filter->validate()) {
                throw new Exception('Failed to load filters for marks approval.');
            }

            if ($resultsType === 'pending' || $resultsType === 'approved') {

                $coursesSearchModel = new CoursesToApproveSearch();
                if ($resultsType === 'pending') {
                    $coursesDataProvider = $coursesSearchModel->search($filter, 'PENDING', $filtersInterface, $this->deptCode, $this->facCode);
                } else {
                    $coursesDataProvider = $coursesSearchModel->search($filter, 'APPROVED', $filtersInterface, $this->deptCode, $this->facCode);
                }

                if ($resultsType === 'pending') {
                    $panelHeading = 'Pending marks';
                } else {
                    $panelHeading = 'Approved marks';
                }

                return $this->render('pendingOrApprovedCourses', [
                    'title' => $panelHeading,
                    'panelHeading' => $panelHeading,
                    'coursesDataProvider' => $coursesDataProvider,
                    'coursesSearchModel' => $coursesSearchModel,
                    'filter' => $filter,
                    'filtersInterface' => $filtersInterface,
                    'resultsType' => $resultsType
                ]);
            } else {
                $approvedCoursesSearchModel = new CoursesToApproveSearch();
                $approvedCoursesDataProvider = $approvedCoursesSearchModel->search($filter, 'APPROVED', $filtersInterface, $this->deptCode, $this->facCode);

                $pendingCoursesSearchModel = new CoursesToApproveSearch();
                $pendingCoursesDataProvider = $pendingCoursesSearchModel->search($filter, 'PENDING', $filtersInterface, $this->deptCode, $this->facCode);

                return $this->render('coursesToApprove', [
                    'title' => 'Marks approval',
                    'pendingPanelHeading' => 'Pending marks',
                    'approvedPanelHeading' => 'Approved marks',
                    'pendingCoursesDataProvider' => $pendingCoursesDataProvider,
                    'approvedCoursesDataProvider' => $approvedCoursesDataProvider,
                    'pendingCoursesSearchModel' => $pendingCoursesSearchModel,
                    'approvedCoursesSearchModel' => $approvedCoursesSearchModel,
                    'filter' => $filter,
                    'filtersInterface' => $filtersInterface,
                    'resultsType' => $resultsType
                ]);
            }
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * @param string $marksheetId
     * @param string $level
     * @return string page to display assessments in a marksheet
     * @throws ServerErrorHttpException
     */
    public function actionAssessmentsWithMarks(string $marksheetId, string $level): string
    {
        try {
            if (empty($marksheetId)) {
                throw new Exception('A course marksheet must be provided.');
            }

            $reportDetails = SmisHelper::performanceReportDetails($marksheetId);

            $courseCode = $reportDetails['courseCode'];
            $courseName = $reportDetails['courseName'];

            $searchModel = new MarksApprovalAssessmentSearch();
            $assessmentsDataProvider = $searchModel->search($marksheetId);

            $panelHeading = $courseName . ' (' . $courseCode . ')' . ' | department of ' . strtolower($this->deptName);

            $mkModel = MarksheetDef::find()->select(['SEMESTER_ID'])->where(['MRKSHEET_ID' => $marksheetId])
                ->asArray()->one();

            $semester = Semester::find()->select(['DEGREE_CODE'])->where(['SEMESTER_ID' => $mkModel['SEMESTER_ID']])
                ->asArray()->one();

            return $this->render('assessments', [
                'title' => 'Course assessments',
                'panelHeading' => $panelHeading,
                'searchModel' => $searchModel,
                'assessmentsDataProvider' => $assessmentsDataProvider,
                'reportDetails' => $reportDetails,
                'deptCode' => $this->deptCode,
                'deptName' => $this->deptName,
                'facCode' => $this->facCode,
                'facName' => $this->facName,
                'level' => $level,
                'degreeCode' => $semester['DEGREE_CODE']
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
     * @param string $id
     * @param string $level
     * @param string $degreeCode
     * @return string page to display marks of an assessment
     * @throws ServerErrorHttpException
     */
    public function actionMarksToApprove(string $id, string $level, string $degreeCode): string
    {
        try {
            if ($level !== 'hod' && $level !== 'dean') {
                throw new Exception('The correct approval level must be provided.');
            }

            $assessmentModel = CourseWorkAssessment::findOne($id);
            $assessmentTypeModel = AssessmentType::findOne($assessmentModel->ASSESSMENT_TYPE_ID);
            if (strpos($assessmentTypeModel->ASSESSMENT_NAME, 'EXAM_COMPONENT') !== false) {
                $isExamComponent = true;
            } else {
                $isExamComponent = false;
            }

            $marksheetDefModel = MarksheetDef::find()
                ->select(['COURSE_ID'])->where(['MRKSHEET_ID' => $assessmentModel->MARKSHEET_ID])->one();
            $courseModel = Course::find()
                ->select(['COURSE_ID', 'COURSE_CODE', 'COURSE_NAME', 'DEPT_CODE'])
                ->where(['COURSE_ID' => $marksheetDefModel->COURSE_ID])->one();

            $searchModel = new MarksToApproveSearch();
            $marksProvider = $searchModel->search(Yii::$app->request->queryParams, [
                'assessmentId' => $id,
                'assessmentName' => $assessmentTypeModel->ASSESSMENT_NAME,
                'level' => $level,
                'deptCode' => ($level === 'hod') ? $this->deptCode : $courseModel->DEPT_CODE,
                'facCode' => $this->facCode,
                'isExamComponent' => $isExamComponent
            ]);

            return $this->render('marks', [
                'title' => 'Student marks',
                'searchModel' => $searchModel,
                'marksProvider' => $marksProvider,
                'deptCode' => $this->deptCode,
                'facCode' => $this->facCode,
                'level' => $level,
                'assessmentId' => $id,
                'courseModel' => $courseModel,
                'assessmentTypeModel' => $assessmentTypeModel,
                'assessmentModel' => $assessmentModel,
                'degreeCode' => $degreeCode,
                'isExamComponent' => $isExamComponent,
                'marksheetId' => $assessmentModel->MARKSHEET_ID
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
     * @param string $id course work assessment id
     * @param string $level approval level
     * @param string $degreeCode $degree code
     * @return string student course assessment/exam marks view
     * @throws ServerErrorHttpException
     */
    public function actionIndex(string $id, string $level, string $degreeCode): string
    {
        try {
            if ($level !== 'hod' && $level !== 'dean') {
                throw new Exception('The correct approval level must be provided.');
            }

            $assessmentModel = CourseWorkAssessment::findOne($id);
            $assessmentTypeModel = AssessmentType::findOne($assessmentModel->ASSESSMENT_TYPE_ID);
            if (strpos($assessmentTypeModel->ASSESSMENT_NAME, 'EXAM_COMPONENT') !== false) {
                $isExamComponent = true;
            } else {
                $isExamComponent = false;
            }

            $marksheetDefModel = MarksheetDef::find()
                ->select(['COURSE_ID'])->where(['MRKSHEET_ID' => $assessmentModel->MARKSHEET_ID])->one();
            $courseModel = Course::find()
                ->select(['COURSE_ID', 'COURSE_CODE', 'COURSE_NAME', 'DEPT_CODE'])
                ->where(['COURSE_ID' => $marksheetDefModel->COURSE_ID])->one();

            $searchModel = new MarksSearch();
            $marksProvider = $searchModel->search(Yii::$app->request->queryParams, [
                'assessmentId' => $id,
                'assessmentName' => $assessmentTypeModel->ASSESSMENT_NAME,
                'level' => $level,
                'deptCode' => ($level === 'hod') ? $this->deptCode : $courseModel->DEPT_CODE,
                'facCode' => $this->facCode,
            ]);

            return $this->render('index', [
                'title' => 'Student marks',
                'searchModel' => $searchModel,
                'marksProvider' => $marksProvider,
                'deptCode' => $this->deptCode,
                'facCode' => $this->facCode,
                'level' => $level,
                'assessmentId' => $id,
                'courseModel' => $courseModel,
                'assessmentTypeModel' => $assessmentTypeModel,
                'assessmentModel' => $assessmentModel,
                'degreeCode' => $degreeCode,
                'isExamComponent' => $isExamComponent
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
     * Read student coursework record
     * @param string $studentCourseworkId student coursework record
     * @param string $level of the approval
     * @return string edit marks view
     * @throws ServerErrorHttpException
     */
    public function actionEditMarks(string $studentCourseworkId, string $level): string
    {
        try {
            $cwModel = StudentCoursework::findOne($studentCourseworkId);
            $cwAssessment = CourseWorkAssessment::findOne($cwModel->ASSESSMENT_ID);
            $examUpdate = new ExamUpdateApproval();
            return $this->renderAjax('_editMarks', [
                'title' => 'Edit Marks',
                'cwModel' => $cwModel,
                'examUpdateModel' => $examUpdate,
                'studentCourseworkId' => $studentCourseworkId,
                'level' => $level,
                'maximumMarks' => $cwAssessment->DIVIDER
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
    public function actionUpdateMarks(): Response
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $coursework = Yii::$app->request->post('StudentCoursework');
            $examUpdate = Yii::$app->request->post('ExamUpdateApproval');
            $level = Yii::$app->request->post('LEVEL');
            $marks = (int)$examUpdate['CHANGD_TO'];
            $recommendedMarks = (int)$examUpdate['RECOMMENDED_MARKS'];
            $remarks = $coursework['REMARKS'];

            // Update the student marks
            $scModel = StudentCoursework::findOne($coursework['COURSE_WORK_ID']);
            $changedFrom = (int)$scModel->MARK;
            $assessment = CourseWorkAssessment::find()
                ->where(['ASSESSMENT_ID' => $scModel->ASSESSMENT_ID])
                ->one();
            $divider = $assessment->DIVIDER;
            if ($marks > (int)$divider) {
                $this->setFlash('danger', 'Edit Exam Marks',
                    'Failed to update. Marks must not exceed the maximum.');
            }

            $assessmentTypeModel = AssessmentType::findOne($assessment->ASSESSMENT_TYPE_ID);
            if (strpos($assessmentTypeModel->ASSESSMENT_NAME, 'EXAM_COMPONENT') !== false) {
                $scModel->MARK = round(((int)$marks / $divider) * $assessment->WEIGHT, 2);
            } else {
                $scModel->MARK = $marks;
            }
            $scModel->RAW_MARK = $marks;
            $scModel->REMARKS = $remarks;
            $cwSaved = $scModel->save();

            if ($cwSaved) {
                SmisHelper::updateMarksConsolidatedFlag($assessment->MARKSHEET_ID);
            }

            // Record the update history
            $examUpdate = new ExamUpdateApproval();
            $examUpdate->COURSEWORK_ID = $scModel->COURSE_WORK_ID;
            $examUpdate->APPROVAL_DATE = new Expression('CURRENT_DATE');
            if ($level === 'hod') {
                $searchLevel = 'HOD';
            } elseif ($level === 'dean') {
                $searchLevel = 'DEAN_DIRECTOR';
            }
            $approval = ApprovalLevel::find()->where(['NAME' => $searchLevel])->one();
            $examUpdate->APPROVAL_LEVEL_ID = $approval->APPROVAL_LEVEL_ID;
            $examUpdate->RECOMMENDED_MARKS = $recommendedMarks;
            $examUpdate->CHANGED_FROM = $changedFrom;
            $examUpdate->CHANGD_TO = $marks;
            $examUpdate->REMARKS = $remarks;
            $examUpdate->APPROVER_ID = $this->payrollNo;
            $updateSave = $examUpdate->save();

            /**
             * Check for levels and send notifications
             * If level = HOD send email alert to the Lecturer
             * If level = Dean/Director send email alert to the HOD and Lecturer
             */
            $marksheetId = $assessment->MARKSHEET_ID;
            $mkModel = MarksheetDef::find()->select(['PAYROLL_NO', 'COURSE_ID'])
                ->where(['MRKSHEET_ID' => $marksheetId])->one();

            // On marks update, the course leader must receive an email notification
            if (is_null($mkModel->PAYROLL_NO)) {
                throw new Exception('The marksheet being update has no course leader. Please provide one.');
            }

            $course = Course::find()->select(['COURSE_CODE', 'COURSE_NAME', 'DEPT_CODE'])
                ->where(['COURSE_ID' => $mkModel->COURSE_ID])->one();

            $lecturer = EmpVerifyView::find()
                ->select(['PAYROLL_NO', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE', 'EMAIL'])
                ->where(['PAYROLL_NO' => $mkModel->PAYROLL_NO, 'STATUS_DESC' => 'ACTIVE'])
                ->one();

            if (empty($lecturer)) {
                $this->setFlash('danger', 'Marks update',
                    'Course lead was not found. Please allocate this course again.');
                $transaction->rollBack();
                return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
            }

            $department = Department::findOne($course->DEPT_CODE);
            $faculty = Faculty::findOne($department->FAC_CODE);
            $assessmentTYpe = AssessmentType::findOne($assessment->ASSESSMENT_TYPE_ID);

            $emailSubject = 'MARKS UPDATE';
            $deptName = $department->DEPT_NAME;
            $facName = $faculty->FACULTY_NAME;
            $registrationNumber = $scModel->REGISTRATION_NUMBER;
            $assessmentName = $assessmentTYpe->ASSESSMENT_NAME;
            $courseName = $course->COURSE_NAME;
            $courseCode = $course->COURSE_CODE;

            // If update occurs at the HOD level, we only send the emails to the course leader.
            $lecturerEmail = [
                'recipientEmail' => $lecturer->EMAIL,
                'subject' => $emailSubject,
                'params' => [
                    'recipient' => $lecturer->EMP_TITLE . ' ' . $lecturer->SURNAME . ' ' . $lecturer->OTHER_NAMES,
                    'recipientLevel' => 'lecturer',
                    'courseCode' => $courseCode,
                    'courseName' => $courseName,
                    'assessmentName' => $assessmentName,
                    'registrationNumber' => $registrationNumber,
                    'originalMarks' => $changedFrom,
                    'newMarks' => $marks,
                    'level' => $level,
                    'deptName' => $deptName,
                    'facName' => $facName,
                    'remarks' => $remarks
                ]
            ];
            $emails = [];
            $emails[] = $lecturerEmail;

            // If update occurs at the Dean/Director level, we send the emails to the HOD
            // and the course leader.
            if ($level === 'dean') {
                $hodEmail = [
                    'recipientEmail' => $department->EMAIL,
                    'subject' => $emailSubject,
                    'params' => [
                        'recipient' => 'HOD ' . $deptName,
                        'recipientLevel' => 'hod',
                        'courseCode' => $courseCode,
                        'courseName' => $courseName,
                        'assessmentName' => $assessmentName,
                        'registrationNumber' => $registrationNumber,
                        'originalMarks' => $changedFrom,
                        'newMarks' => $marks,
                        'level' => $level,
                        'deptName' => $deptName,
                        'facName' => $facName,
                        'remarks' => $remarks
                    ]
                ];
                $emails[] = $hodEmail;
            }

            $layout = '@app/mail/layouts/html';
            $view = '@app/mail/views/marksChanged';
            SmisHelper::sendEmails($emails, $layout, $view);

            if ($cwSaved && $updateSave) {
                $this->setFlash('success', 'Edit Marks', 'This record has been updated successfully!');
            } else {
                $this->setFlash('danger', 'Edit Marks', 'This record failed to update!');
            }
            $transaction->commit();
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
     * Submit marks as final by the hod|dean
     * @return Response|object
     * @throws InvalidConfigException
     */
    public function actionApproveMarks()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = Yii::$app->request->post();
            $assessmentId = $post['assessmentId'];
            $level = $post['level'];
            if ($level === 'hod') {
                $statusToUpdate = 'HOD_APPROVAL_STATUS';

                // Only seek to approve marks that are already approved by the lecturer
                $scModels = StudentCoursework::find()
                    ->where(['ASSESSMENT_ID' => $assessmentId, 'LECTURER_APPROVAL_STATUS' => 'APPROVED'])->all();
            } else {
                $statusToUpdate = 'DEAN_APPROVAL_STATUS';

                // Only seek to approve marks that are already approved by the hod
                $scModels = StudentCoursework::find()
                    ->where(['ASSESSMENT_ID' => $assessmentId, 'HOD_APPROVAL_STATUS' => 'APPROVED'])->all();
            }

            $marksApproved = false;
            foreach ($scModels as $scModel) {
                $scModel->$statusToUpdate = 'APPROVED';

                // Marks awaiting final processing after Dean/Director approval have this value set to 0.
                // The cron job looks for entries with these flag to determine which marks are ready for final processing
                if ($level === 'dean') {
                    $processedAsFinalFlag = 0;
                    $scModel->PROCESSED_AS_FINAL = $processedAsFinalFlag;
                }

                if ($scModel->save()) {
                    $marksApproved = true;
                } else {
                    $marksApproved = false;
                    break;
                }
            }

            if ($marksApproved) {
                $this->setFlash('success', 'Approve Marks', 'These marks have been approved successfully.');
            } else {
                throw new Exception('These marks failed to approve.');
            }

            // After approvals at the HOD, send an email alert to the Dean
            if ($level === 'hod') {
                $assessment = CourseWorkAssessment::find()
                    ->where(['ASSESSMENT_ID' => $assessmentId])
                    ->one();
                $assessmentTYpe = AssessmentType::findOne($assessment->ASSESSMENT_TYPE_ID);
                $marksheetId = $assessment->MARKSHEET_ID;
                $mkModel = MarksheetDef::find()->select(['COURSE_ID'])->where(['MRKSHEET_ID' => $marksheetId])->one();
                $course = Course::find()->select(['COURSE_CODE', 'COURSE_NAME', 'DEPT_CODE'])
                    ->where(['COURSE_ID' => $mkModel->COURSE_ID])->one();
                $department = Department::findOne($course->DEPT_CODE);
                $faculty = Faculty::findOne($department->FAC_CODE);

                $deptName = $department->DEPT_NAME;
                $courseName = $course->COURSE_NAME;
                $courseCode = $course->COURSE_CODE;
                $facName = $faculty->FACULTY_NAME;
                $assessmentName = $assessmentTYpe->ASSESSMENT_NAME;

                $email = [
                    'recipientEmail' => $faculty->EMAIL,
                    'subject' => 'MARKS SUBMISSION',
                    'params' => [
                        'recipient' => 'Dean ' . $facName,
                        'courseCode' => $courseCode,
                        'courseName' => $courseName,
                        'submittedBy' => $deptName,
                        'assessmentName' => $assessmentName,
                    ]
                ];
                $emails = [];
                $emails[] = $email;
                $layout = '@app/mail/layouts/html';
                $view = '@app/mail/views/marksSubmitted';
                SmisHelper::sendEmails($emails, $layout, $view);
            }

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
     * Approve marks for an exam or exam components in a course
     * @return Response
     */
    public function actionApproveCourseMarks(): Response
    {
        $transaction = Yii::$app->db->beginTransaction();
        $spTransaction = Yii::$app->db2->beginTransaction();
        try {
            $post = Yii::$app->request->post();
            $marksheetIds = $post['marksheetIds'];
            $level = $post['level'];

            // Get the exam or exam components of a marksheet
            for ($i = 0; $i < count($marksheetIds); $i++) {
                $marksheetId = $marksheetIds[$i];
                $assessmentIds = [];
                if (SmisHelper::hasMultipleExamComponents($marksheetId)) {
                    $examAssessments = CourseWorkAssessment::find()->alias('CW')
                        ->joinWith(['assessmentType AT'])
                        ->where(['CW.MARKSHEET_ID' => $marksheetId])
                        ->andWhere(['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT'])
                        ->asArray()
                        ->all();

                    foreach ($examAssessments as $examAssessment) {
                        $assessmentIds[] = $examAssessment['ASSESSMENT_ID'];
                    }
                } else {
                    $examAssessment = CourseWorkAssessment::find()->alias('CW')
                        ->joinWith(['assessmentType AT'])
                        ->where(['CW.MARKSHEET_ID' => $marksheetId])
                        ->andWhere(['AT.ASSESSMENT_NAME' => 'EXAM'])
                        ->asArray()
                        ->one();

                    $assessmentIds[] = $examAssessment['ASSESSMENT_ID'];
                }

                $studentMarks = [];
                if ($level === 'hod') {
                    // Only seek to approve marks that are already approved by the lecturer
                    $studentMarks = StudentCoursework::find()
                        ->where(['IN', 'ASSESSMENT_ID', $assessmentIds])
                        ->andWhere(['LECTURER_APPROVAL_STATUS' => 'APPROVED'])
                        ->all();
                } elseif ($level === 'dean') {
                    // Only seek to approve marks that are already approved by the lecturer and HOD
                    $studentMarks = StudentCoursework::find()
                        ->where(['IN', 'ASSESSMENT_ID', $assessmentIds])
                        ->andWhere(['LECTURER_APPROVAL_STATUS' => 'APPROVED', 'HOD_APPROVAL_STATUS' => 'APPROVED'])
                        ->all();
                }

                $marksApproved = false;

                foreach ($studentMarks as $studentMark) {
                    if ($level === 'hod') {
                        $studentMark->HOD_APPROVAL_STATUS = 'APPROVED';
                    } elseif ($level === 'dean') {
                        $studentMark->DEAN_APPROVAL_STATUS = 'APPROVED';
                        /**
                         * After approving marks at the dean level, marks are consolidated and graded by a cron task.
                         * This task looks for marks with the PROCESSED_AS_FINAL flag set to 0.
                         * The cron job looks for entries with these flag to determine which marks are ready for final processing.
                         */
                        $studentMark->PROCESSED_AS_FINAL = 0;
                    }

                    if ($studentMark->save()) {
                        $marksApproved = true;
                    } else {
                        $marksApproved = false;
                        break;
                    }
                }

                if ($marksApproved) {
                    // consolidate, grade and publish the marks
                    if ($level === 'dean') {
                        $this->consolidateAndPublishMarks($marksheetId);
                    }
                    $this->setFlash('success', 'Approve Marks', 'These marks have been approved successfully.');
                } else {
                    throw new Exception('These marks failed to approve.');
                }
            }

            /**
             * @todo send email to dean
             * After approvals at the HOD, send an email alert to the Dean
             */

            $transaction->commit();
            $spTransaction->commit();
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        } catch (Exception $ex) {
            $transaction->rollBack();
            $spTransaction->rollBack();
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return $this->asJson(['success' => false, 'message' => $message]);
        }
    }

    /**
     * Send marks of a course back to the lecturer for further editing
     * @return Response
     */
    public function actionMarkBackMarks(): Response
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = Yii::$app->request->post();
            $marksheetId = $post['marksheetId'];
            $level = $post['level'];

            // Get all courses works, exam or exam components of a marksheet
            $assessmentIds = [];
            $assessments = CourseWorkAssessment::find()->where(['MARKSHEET_ID' => $marksheetId])->asArray()->all();
            foreach ($assessments as $assessment) {
                $assessmentIds[] = $assessment['ASSESSMENT_ID'];
            }

            $studentMarks = StudentCoursework::find()->where(['IN', 'ASSESSMENT_ID', $assessmentIds])->all();

            $sentBack = false;

            foreach ($studentMarks as $studentMark) {
                $studentMark->LECTURER_APPROVAL_STATUS = 'PENDING';
                $studentMark->HOD_APPROVAL_STATUS = 'PENDING';
                $studentMark->DEAN_APPROVAL_STATUS = 'PENDING';
                $studentMark->PROCESSED_AS_FINAL = null;
                $studentMark->IS_CONSOLIDATED = 0;

                if ($studentMark->save()) {
                    $sentBack = true;
                } else {
                    $sentBack = false;
                    break;
                }
            }

            if ($sentBack) {
                $this->setFlash('success', 'Send back Marks', 'Marks have been sent back to the lecturer successfully.');
            } else {
                throw new Exception('These marks failed to send back.');
            }

            /**
             *  On marks send back, the course leader must receive an email notification
             */
            $mkModel = MarksheetDef::find()->select(['PAYROLL_NO', 'COURSE_ID'])->where(['MRKSHEET_ID' => $marksheetId])
                ->one();

            if (is_null($mkModel->PAYROLL_NO)) {
                throw new Exception('The marksheet being sent back has no course leader. Please provide one.');
            }

            $course = Course::find()->select(['COURSE_CODE', 'COURSE_NAME', 'DEPT_CODE'])
                ->where(['COURSE_ID' => $mkModel->COURSE_ID])->one();

            $lecturer = EmpVerifyView::find()
                ->select(['PAYROLL_NO', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE', 'EMAIL'])
                ->where(['PAYROLL_NO' => $mkModel->PAYROLL_NO, 'STATUS_DESC' => 'ACTIVE'])
                ->one();

            $emails = [];
            if (!is_null($lecturer)) {
                $lecturerEmail = [
                    'recipientEmail' => $lecturer->EMAIL,
                    'subject' => 'MARKS SENT BACK',
                    'params' => [
                        'recipient' => $lecturer->EMP_TITLE . ' ' . $lecturer->SURNAME . ' ' . $lecturer->OTHER_NAMES,
                        'courseCode' => $course->COURSE_NAME,
                        'courseName' => $course->COURSE_CODE,
                        'deptName' => $this->deptName,
                        'facName' => $this->facName,
                        'level' => $level
                    ]
                ];

                $emails[] = $lecturerEmail;
            }

            $department = Department::findOne($course->DEPT_CODE);

            if (!is_null($department->EMAIL)) {
                $hodEmail = [
                    'recipientEmail' => $department->EMAIL,
                    'subject' => 'MARKS SENT BACK',
                    'params' => [
                        'recipient' => 'HOD ' . $department->DEPT_NAME,
                        'courseCode' => $course->COURSE_NAME,
                        'courseName' => $course->COURSE_CODE,
                        'deptName' => $this->deptName,
                        'facName' => $this->facName,
                        'level' => $level
                    ]
                ];

                $emails[] = $hodEmail;
            }

            if (!empty($emails)) {
                $layout = '@app/mail/layouts/html';
                $view = '@app/mail/views/marksSentBack';
                SmisHelper::sendEmails($emails, $layout, $view);

                $transaction->commit();
            } else {
                $this->setFlash('danger', 'Send marks back to lecturer',
                    'Course lead or department was not found. Allocate this course again.');

                $transaction->rollBack();
            }

            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        } catch (Exception $ex) {
            $transaction->rollBack();
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return $this->asJson(['success' => false, 'message' => $message]);
        }
    }

    /**
     * Consolidate course work marks and exam marks and publish
     *
     * @param string $marksheetId
     * @return void
     * @throws Exception
     */
    private function consolidateAndPublishMarks(string $marksheetId)
    {
        if (SmisHelper::marksheetExists($marksheetId)) {
            SmisHelper::consolidateCwMarks($marksheetId);

            if (SmisHelper::hasMultipleExamComponents($marksheetId)) {
                SmisHelper::consolidateMultipleExamMarks($marksheetId);
            } else {
                SmisHelper::consolidateSingleExamMarks($marksheetId);
            }

            $this->publishMarks($marksheetId);
        }
    }

    /**
     * @throws \yii\db\Exception
     * @throws Exception
     */
    private function publishMarks(string $marksheetId)
    {
        $connection = Yii::$app->getDb();

        $studentsToPublishSql = "
            SELECT
                MUTHONI.LEC_MARKSHEETS.MRKSHEET_ID,
                MUTHONI.LEC_MARKSHEETS.REGISTRATION_NUMBER,
                MUTHONI.LEC_CW_ASSESSMENT.ASSESSMENT_ID,
                MUTHONI.LEC_ASSESSMENT_TYPES.ASSESSMENT_NAME
            FROM
                MUTHONI.LEC_MARKSHEETS
                INNER JOIN MUTHONI.LEC_CW_ASSESSMENT 
                    ON MUTHONI.LEC_MARKSHEETS.MRKSHEET_ID = MUTHONI.LEC_CW_ASSESSMENT.MARKSHEET_ID
                INNER JOIN MUTHONI.LEC_ASSESSMENT_TYPES 
                    ON MUTHONI.LEC_CW_ASSESSMENT.ASSESSMENT_TYPE_ID = MUTHONI.LEC_ASSESSMENT_TYPES.ASSESSMENT_TYPE_ID
                INNER JOIN MUTHONI.LEC_STUDENT_COURSE_WORK 
                    ON MUTHONI.LEC_CW_ASSESSMENT.ASSESSMENT_ID = MUTHONI.LEC_STUDENT_COURSE_WORK.ASSESSMENT_ID
                    AND MUTHONI.LEC_STUDENT_COURSE_WORK.REGISTRATION_NUMBER = MUTHONI.LEC_MARKSHEETS.REGISTRATION_NUMBER
            WHERE 
                MUTHONI.LEC_MARKSHEETS.GRADE IS NOT NULL
                AND MUTHONI.LEC_MARKSHEETS.PUBLISH_STATUS = :notPublished
                AND MUTHONI.LEC_STUDENT_COURSE_WORK.MARK_TYPE = :markType
                AND MUTHONI.LEC_STUDENT_COURSE_WORK.LECTURER_APPROVAL_STATUS = :approvalStatus
                AND MUTHONI.LEC_STUDENT_COURSE_WORK.HOD_APPROVAL_STATUS = :approvalStatus
                AND MUTHONI.LEC_STUDENT_COURSE_WORK.DEAN_APPROVAL_STATUS = :approvalStatus
                AND MUTHONI.LEC_ASSESSMENT_TYPES.ASSESSMENT_NAME LIKE :assessmentName
                AND MUTHONI.LEC_MARKSHEETS.MRKSHEET_ID = :marksheetId
            ORDER BY
                MUTHONI.LEC_MARKSHEETS.MRKSHEET_ID ASC
        ";

        $studentsToPublishParams = [
            ':notPublished' => self::MARKS_NOT_PUBLISHED,
            ':markType' => 'EXAM',
            ':approvalStatus' => 'APPROVED',
            ':assessmentName' => 'EXAM%',
            ':marksheetId' => $marksheetId
        ];

        $studentsToPublish = $connection->createCommand($studentsToPublishSql)->bindValues($studentsToPublishParams)->queryAll();

        foreach ($studentsToPublish as $studentToPublish) {
            $regNumber = $studentToPublish['REGISTRATION_NUMBER'];

            $tempMarksheet = TempMarksheet::find()->where(['MRKSHEET_ID' => $marksheetId, 'REGISTRATION_NUMBER' => $regNumber])->one();

            $studentIsInMarksheet = Marksheet::find()->where(['MRKSHEET_ID' => $marksheetId, 'REGISTRATION_NUMBER' => $regNumber])->count();

            /**
             * If a student has marks but no longer in the marksheet, update their marks complete to 1 and
             * publish status to 1 but not push the marks to smis
             */
            if (intval($studentIsInMarksheet) === 0) {
                $tempMarksheet->MARKS_COMPLETE = self::MARKS_COMPLETE;
                $tempMarksheet->PUBLISH_STATUS = self::MARKS_PUBLISHED;
                if (!$tempMarksheet->save()) {
                    if (!empty($tempMarksheet->getErrors())) {
                        $message = 'Missing ' . $regNumber . ' in ' . $marksheetId . ' failed to update publish and marks complete. '
                            . json_encode($tempMarksheet->getErrors());
                        throw new Exception($message);
                    } else {
                        throw new Exception('Missing student failed to update publish and marks complete status.');
                    }
                }
                continue;
            }

            $marksheetStudent = Marksheet::find()->where(['MRKSHEET_ID' => $marksheetId, 'REGISTRATION_NUMBER' => $regNumber])->one();
            $marksheetStudent->COURSE_MARKS = $tempMarksheet->COURSE_MARKS;
            $marksheetStudent->EXAM_MARKS = $tempMarksheet->EXAM_MARKS;
            $marksheetStudent->FINAL_MARKS = $tempMarksheet->FINAL_MARKS;
            $marksheetStudent->GRADE = $tempMarksheet->GRADE;
            if (is_null($marksheetStudent->ENTRY_DATE)) {
                $marksheetStudent->ENTRY_DATE = new Expression('CURRENT_DATE');
            }
            $marksheetStudent->LAST_UPDATE = new Expression('CURRENT_DATE');

            if ($marksheetStudent->save()) {

                $tempMarksheet->PUBLISH_STATUS = self::MARKS_PUBLISHED;

                if (!$tempMarksheet->save()) {
                    if (!empty($tempMarksheet->getErrors())) {
                        $message = $marksheetId . ' failed to update publish status. Errors: ' . json_encode($marksheetStudent->getErrors());
                        throw new Exception($message);
                    } else {
                        throw new Exception('Marks failed to publish');
                    }
                }

                SmisHelper::pushToStudentCourses($marksheetId);

                SmisHelper::pushToSmisPortalMarksheets($marksheetStudent);

            } else {
                if (!empty($marksheetStudent->getErrors())) {
                    $message = $marksheetId . ' not published in smis. Errors: ' . json_encode($marksheetStudent->getErrors());
                    throw new Exception($message);
                } else {
                    throw new Exception('Marks failed to publish');
                }
            }
        }
    }
}
