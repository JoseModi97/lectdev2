<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc Generate shared reports for the lecturer/hod/dean/administrators
 */

namespace app\controllers;

set_time_limit(0);

use app\components\SmisHelper;
use app\models\CourseAnalysisFilter;
use app\models\CourseWorkAssessment;
use app\models\DegreeProgramme;
use app\models\Marksheet;
use app\models\MarksheetDef;
use app\models\search\CourseAnalysisSearch;
use app\models\search\MarksPreviewSearch;
use app\models\search\MissingMarksAssessmentsSearch;
use app\models\search\MissingMarksSearch;
use app\models\Semester;
use app\models\StudentConsolidatedMarksFilter;
use app\models\TempMarksheet;
use Exception;
use kartik\mpdf\Pdf;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class SharedReportsController extends BaseController
{
    /**
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
                ],
            ],
        ];
    }

    /**
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     */
    public function init()
    {
        parent::init();
        SmisHelper::allowAccess(['LEC_SMIS_DEAN', 'LEC_SMIS_HOD', 'LEC_SMIS_LECTURER']);
    }

    /**
     * Get available academic years configured for this system
     * @return Response
     */
    public function actionGetAcademicYears(): Response
    {
        try{
            return $this->asJson(['status' => 200, 'academicYears' => Yii::$app->params['academicYears']]);
        }catch (Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            return $this->asJson(['status' => 500, 'message' => $message]);
        }
    }

    /**
     * Get available programmes in a user's faculty
     * @return Response
     */
    public function actionGetProgrammes(): Response
    {
        try{
            $degreeProgrammes = DegreeProgramme::find()->select(['DEGREE_NAME', 'DEGREE_CODE'])
                ->where(['FACUL_FAC_CODE' => $this->facCode])
                ->orderBy(['DEGREE_CODE' => SORT_ASC])->asArray()->all();

            return $this->asJson(['programmes' => $degreeProgrammes]);
        }catch (Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            return $this->asJson(['status' => 500, 'message' => $message]);
        }
    }

    /**
     * Get available levels of study in an academic year and programme
     * @return Response
     */
    public function actionGetLevelsOfStudy(): Response
    {
        try{
            $get = Yii::$app->request->get();
            $levels = Semester::find()->alias('SM')->select(['SM.LEVEL_OF_STUDY'])->distinct()
                ->where(['SM.ACADEMIC_YEAR' => $get['year'], 'SM.DEGREE_CODE' => $get['degreeCode']])
                ->joinWith(['levelOfStudy LVL' => function(ActiveQuery $q){
                    $q->select([
                        'LVL.LEVEL_OF_STUDY',
                        'LVL.NAME'
                    ]);
                }], true, 'INNER JOIN')
                ->orderBy(['SM.LEVEL_OF_STUDY' => SORT_ASC])->asArray()->all();

            return $this->asJson(['levels' => $levels]);
        }catch (Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            return $this->asJson(['status' => 500, 'message' => $message]);
        }
    }

    /**
     * Get available student groups in an academic year, programme and study level
     * @return Response
     */
    public function actionGetGroups(): Response
    {
        try{
            $get = Yii::$app->request->get();
            $groups = Semester::find()->alias('SM')
                ->select(['SM.GROUP_CODE'])
                ->joinWith(['group GR' => function($q){
                    $q->select([
                        'GR.GROUP_CODE',
                        'GR.GROUP_NAME'
                    ]);
                }], true, 'INNER JOIN')
                ->where([
                    'SM.ACADEMIC_YEAR' => $get['year'],
                    'SM.DEGREE_CODE' => $get['degreeCode'],
                    'SM.LEVEL_OF_STUDY' => $get['level']
                ])
                ->distinct()
                ->orderBy(['SM.GROUP_CODE' => SORT_ASC])
                ->asArray()->all();
            return $this->asJson(['groups' => $groups]);
        }catch (Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            return $this->asJson(['status' => 500, 'message' => $message]);
        }
    }

    /**
     * Get available semesters in an academic year, programme, study level and student group
     * @return Response
     */
    public function actionGetSemesters(): Response
    {
        try{
            $get = Yii::$app->request->get();
            $semesters = Semester::find()->alias('SM')
                ->select(['SM.SEMESTER_ID', 'SM.SEMESTER_CODE', 'SM.DESCRIPTION_CODE'])
                ->joinWith(['semesterDescription SD' => function($q){
                    $q->select([
                        'SD.DESCRIPTION_CODE',
                        'SD.SEMESTER_DESC'
                    ]);
                }], true, 'INNER JOIN')
                ->where([
                    'SM.ACADEMIC_YEAR' => $get['year'],
                    'SM.DEGREE_CODE' => $get['degreeCode'],
                    'SM.LEVEL_OF_STUDY' => $get['level'],
                    'SM.GROUP_CODE' => $get['group']
                ])
                ->distinct()
                ->orderBy(['SM.SEMESTER_CODE' => SORT_ASC])
                ->asArray()->all();
            return $this->asJson(['semesters' => $semesters]);
        }catch (Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            return $this->asJson(['status' => 500, 'message' => $message]);
        }
    }

    /**
     * @param string $level
     * @param string $restrictedTo
     * @return string page to set filters
     * @throws ServerErrorHttpException
     */
    public function actionCourseAnalysisFilters(string $level, string $restrictedTo=''): string
    {
        try{
            if($level !== 'lecturer' && $level !== 'hod' && $level !== 'dean'){
                throw new Exception('You must provide the correct access level.');
            }

            $filter = new CourseAnalysisFilter();

            $filter->approvalLevel = $level;
            $filter->restrictedTo = $restrictedTo;

            return $this->render('//shared-reports/courseAnalysisFilters', [
                'title' => 'Course analysis report filters',
                'filter' => $filter,
            ]);
        }catch(Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            throw new ServerErrorHttpException($message, '500');
        }
    }

    /**
     * @return string page to display courses with analysis report
     * @throws ServerErrorHttpException
     */
    public function actionCourseAnalysis(): string
    {
        try{
            $courseFilter = new CourseAnalysisFilter();

            $session = Yii::$app->session;

            // Save course filters in the session for retrieval on page redirects
            if(!empty(Yii::$app->request->get()['CourseAnalysisFilter'])){
                $session['CourseAnalysisFilter'] = Yii::$app->request->get();
            }

            if(!$courseFilter->load($session->get('CourseAnalysisFilter')) || !$courseFilter->validate()){
                throw new Exception('Failed to load filters for course analysis report.');
            }

            $searchModel = new CourseAnalysisSearch();
            $dataProvider = $searchModel->search($courseFilter, $this->deptCode, $this->facCode);

            return $this->render('//shared-reports/gradeAnalysis', [
                'title' => 'Grade analysis',
                'panelHeading' => 'Grade analysis report',
                'dataProvider' => $dataProvider,
                'filter' => $courseFilter,
            ]);
        } catch(Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            throw new ServerErrorHttpException($message, '500');
        }
    }

    /**
     * @return string consolidated marks report
     * @throws ServerErrorHttpException
     */
    public function actionConsolidatedMarks(): string
    {
        try{
            $params = Yii::$app->request->queryParams;
            $marksheetId = $params['marksheetId'];

            SmisHelper::updateAndReadConsolidatedMarks($marksheetId);

            $marksPreviewSearch = new MarksPreviewSearch();
            $marksPreviewProvider = $marksPreviewSearch->search($params);

            $reportDetails = SmisHelper::performanceReportDetails($marksheetId);
            $panelHeading = 'Consolidated marks for ' . $reportDetails['courseCode'];

            return $this->render('//shared-reports/consolidatedMarks', [
                'title' => 'Consolidated marks report',
                'marksPreviewSearch' => $marksPreviewSearch,
                'marksPreviewProvider' => $marksPreviewProvider,
                'panelHeading' => $panelHeading,
                'reportDetails' => $reportDetails
            ]);
        }catch(Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            throw new ServerErrorHttpException($message, '500');
        }
    }

    /**
     * @throws ServerErrorHttpException
     */
    public function actionClassPerformance(): string
    {
        try{
            $params = Yii::$app->request->queryParams;
            $marksheetId = $params['marksheetId'];

            // Get grading system
            $marksheet = MarksheetDef::find()->alias('MD')
                ->select([
                    'MD.MRKSHEET_ID',
                    'MD.SEMESTER_ID'
                ])
                ->joinWith(['semester SM' => function(ActiveQuery $q){
                    $q->select([
                        'SM.SEMESTER_ID',
                        'SM.DEGREE_CODE'
                    ]);
                }], true, 'INNER JOIN')
                ->joinWith(['semester.degreeProgramme DEG' => function(ActiveQuery $q){
                    $q->select([
                        'DEG.DEGREE_CODE',
                        'DEG.GRADINGSYSTEM'
                    ]);
                }], true, 'INNER JOIN')
                ->joinWith(['semester.degreeProgramme.gradingSystem GS' => function(ActiveQuery $q){
                    $q->select(['GS.GRADINGCODE', 'GS.GRADINGNAME']);
                }], true, 'INNER JOIN')
                ->where(['MD.MRKSHEET_ID' => $marksheetId])
                ->asArray()
                ->one();

            $gradingName = $marksheet['semester']['degreeProgramme']['gradingSystem']['GRADINGNAME'];

            if($gradingName === 'MASTERS' || $gradingName === 'PhD'){
                $view = 'mastersPhdClassPerformance';
            }elseif ($gradingName === 'DEGREE'){
                $view = 'degreeClassPerformance';
            }elseif($gradingName === 'DIPLOMA'){
                $view = 'diplomaClassPerformance';
            }else{
                throw new Exception('The grading system for the programme has not been configured.');
            }

            $reportDetails = SmisHelper::performanceReportDetails($marksheetId);

            $user = Yii::$app->user->identity->EMP_TITLE . ' ' . Yii::$app->user->identity->SURNAME . ' ' .
                Yii::$app->user->identity->OTHER_NAMES;

            return $this->render($view, [
                'title' => 'class Performance report',
                'reportDetails' => $reportDetails,
                'user' => $user,
                'date' => date('d-M-Y')
            ]);
        }catch(Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            throw new ServerErrorHttpException($message, '500');
        }
    }

    /**
     * @param string $marksheetId The marksheet for which we want to get analysis report
     * @throws InvalidConfigException
     */
    public function actionClassPerformanceStats(string $marksheetId)
    {
        try{
            $totalStudents = Marksheet::find()->where(['MRKSHEET_ID' => $marksheetId])->count();

            // Average total marks
            $totalFinalMarks = TempMarksheet::find()->where(['MRKSHEET_ID'=>$marksheetId])->sum('FINAL_MARKS');
            $avgFinalMarks = round(($totalFinalMarks / $totalStudents), 2);

            // Average exam marks
            $totalExamMarks = TempMarksheet::find()->where(['MRKSHEET_ID'=>$marksheetId])->sum('EXAM_MARKS');
            $avgExamMarks = round(($totalExamMarks / $totalStudents), 2);

            // Average course works marks
            $totalCourseMarks = TempMarksheet::find()->where(['MRKSHEET_ID'=>$marksheetId])->sum('COURSE_MARKS');
            $avgCourseMarks = round(($totalCourseMarks / $totalStudents), 2);

            // Get grades count
            $gradesDistribution = TempMarksheet::find()->select(['GRADE', 'COUNT(*) AS cnt'])
                ->where(['MRKSHEET_ID' => $marksheetId])->groupBy(['GRADE'])->orderBy(['GRADE' => SORT_ASC])
                ->asArray()->all();

            return Yii::createObject([
                'class' => 'yii\web\Response',
                'format' => Response::FORMAT_JSON,
                'data' => [
                    'status' => true,
                    'totalStudents' => $totalStudents,
                    'totalFinalMarks' => $totalFinalMarks,
                    'totalExamMarks' => $totalExamMarks,
                    'totalCourseMarks' => $totalCourseMarks,
                    'avgFinalMarks' => $avgFinalMarks,
                    'avgExamMarks' => $avgExamMarks,
                    'avgCourseMarks' => $avgCourseMarks,
                    'gradesDistribution' => $gradesDistribution
                ]
            ]);
        } catch(Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            return Yii::createObject([
                'class' => 'yii\web\Response',
                'format' => Response::FORMAT_JSON,
                'data' => [
                    'status' => false,
                    'message' => $message
                ]
            ]);
        }
    }

    /**
     * @param string $marksheetId
     * @return string assessments in a marksheet
     * @throws ServerErrorHttpException
     */
    public function actionAssessments(string $marksheetId): string
    {
        try{
            if(empty($marksheetId)){
                throw new Exception('A course marksheet must be provided.');
            }

            $searchModel = new MissingMarksAssessmentsSearch();
            $assessmentsDataProvider = $searchModel->search($marksheetId);

            $marksheetDetails = SmisHelper::marksheetDetails($marksheetId);

            $courseCode = $marksheetDetails['courseCode'];
            $courseName = $marksheetDetails['courseName'];

            $panelHeading = 'ASSESSMENTS IN ' . $courseName .' ('. $courseCode .')';

            return $this->render('assessments', [
                'title' => 'Course assessments',
                'panelHeading' => $panelHeading,
                'assessmentsDataProvider' => $assessmentsDataProvider,
            ]);
        }catch(Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * @param string $assessmentId
     * @return string students with missing marks for an assessment
     * @throws ServerErrorHttpException
     */
    public function actionMissingMarks(string $assessmentId): string
    {
        try{
            if(empty($assessmentId)){
                throw new Exception('An assessment id must be provided.');
            }

            $cwModel = CourseWorkAssessment::findOne($assessmentId);

            $searchModel = new MissingMarksSearch();
            $missingMarksProvider = $searchModel->search([
                'assessmentId' => $assessmentId,
                'marksheetId' => $cwModel->MARKSHEET_ID
            ]);

            $reportDetails = SmisHelper::performanceReportDetails($cwModel->MARKSHEET_ID, $assessmentId);
            $panelHeading = 'Missing marks in ' . $reportDetails['courseCode'] . ' | ' . $reportDetails['assessmentName'];

            return $this->render('missingMarks',[
                'title' => 'Missing marks report',
                'searchModel' => $searchModel,
                'missingMarksProvider' => $missingMarksProvider,
                'panelHeading' => $panelHeading,
                'reportDetails' => $reportDetails
            ]);
        }catch(Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * @param string $level
     * @return string page to set filters
     * @throws ServerErrorHttpException
     */
    public function actionStudentConsolidatedMarksFilters(string $level): string
    {
        try{
            if($level !== 'lecturer' && $level !== 'hod' && $level !== 'dean'){
                throw new Exception('You may not have permissions to access these reports.');
            }

            $filter = new StudentConsolidatedMarksFilter();

            $filter->approvalLevel = $level;

            return $this->render('StudentConsolidatedMarksFilters', [
                'title' => 'Student consolidated marks filters',
                'filter' => $filter
            ]);
        }catch(Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            throw new ServerErrorHttpException($message, '500');
        }
    }

    /**
     * Generates a report of consolidated marks for all courses done by a student in an academic year
     * @return string page to render the report
     * @throws ServerErrorHttpException
     */
    public function actionConsolidatedMarksPerStudent(): string
    {
        try{
            $filter = new StudentConsolidatedMarksFilter();

            $session = Yii::$app->session;

            // Save the filters in the session for retrieval on page redirects
            if(!empty(Yii::$app->request->get()['StudentConsolidatedMarksFilter'])){
                $session['StudentConsolidatedMarksFilter'] = Yii::$app->request->get();
            }

            if(!$filter->load($session->get('StudentConsolidatedMarksFilter')) || !$filter->validate()){
                throw new Exception('Failed to load filters for course analysis report.');
            }

            $bindParams = [
                ':academicYear' => $filter->academicYear,
                ':studyProgram' => $filter->degreeCode,
                ':studyLevel' => $filter->levelOfStudy,
                ':studyGroup' => $filter->group
            ];

            $connection = Yii::$app->getDb();

            // Get all students registered for courses in the given timetable
            $studentsQuery = "SELECT DISTINCT MS.REGISTRATION_NUMBER, ST.OTHER_NAMES, ST.SURNAME
                    FROM MUTHONI.MARKSHEETS MS
                    INNER JOIN MUTHONI.MARKSHEET_DEF MD ON MS.MRKSHEET_ID = MD.MRKSHEET_ID
                    INNER JOIN MUTHONI.SEMESTERS SEM ON MD.SEMESTER_ID = SEM.SEMESTER_ID                    
                    INNER JOIN MUTHONI.UON_STUDENTS ST ON MS.REGISTRATION_NUMBER = ST.REGISTRATION_NUMBER
                    WHERE
                        SEM.ACADEMIC_YEAR = :academicYear AND
                        SEM.DEGREE_CODE = :studyProgram AND
                        SEM.LEVEL_OF_STUDY = :studyLevel AND
                        SEM.GROUP_CODE = :studyGroup
                    ORDER BY MS.REGISTRATION_NUMBER ASC";

            $students = $connection->createCommand($studentsQuery)->bindValues($bindParams)->queryAll();

            /**
             * Get the maximum number of courses registered for by students in the given timetable.
             * We use this number to decide how many table cells we'll have to display the courses in the report.
             */
            $maxStudentCourses = SmisHelper::getMaximumCoursesRegisteredFor($filter);

            $panelHeading = 'Consolidated marks per student';

            return $this->render('consolidatedMarksPerStudent', [
                'title' => 'Consolidated marks per student',
                'students' => $students,
                'maxStudentCourses' => $maxStudentCourses,
                'panelHeading' => $panelHeading,
                'filter' => $filter
            ]);
        }catch(Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \Mpdf\MpdfException
     * @throws \yii\db\Exception
     * @throws InvalidConfigException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     */
    public function actionStudentAnalysis()
    {
        $filter = new StudentConsolidatedMarksFilter();
        $filter->academicYear = '2021/2022';
        $filter->degreeCode = 'P15';
        $filter->levelOfStudy = 4;
        $filter->group = 10;

        $bindParams = [
            ':academicYear' => $filter->academicYear,
            ':studyProgram' => $filter->degreeCode,
            ':studyLevel' => $filter->levelOfStudy,
            ':studyGroup' => $filter->group
        ];

        $connection = Yii::$app->getDb();

        // Get all students registered for courses in the given timetable
        $studentsQuery = "SELECT DISTINCT MS.REGISTRATION_NUMBER
                    FROM MUTHONI.MARKSHEETS MS
                    INNER JOIN MUTHONI.MARKSHEET_DEF MD ON MS.MRKSHEET_ID = MD.MRKSHEET_ID
                    INNER JOIN MUTHONI.SEMESTERS SEM ON MD.SEMESTER_ID = SEM.SEMESTER_ID
                    WHERE
                        SEM.ACADEMIC_YEAR = :academicYear AND
                        SEM.DEGREE_CODE = :studyProgram AND
                        SEM.LEVEL_OF_STUDY = :studyLevel AND
                        SEM.GROUP_CODE = :studyGroup
                    ORDER BY MS.REGISTRATION_NUMBER ASC";

        $students = $connection->createCommand($studentsQuery)->bindValues($bindParams)->queryAll();

        /**
         * Get the maximum number of courses registered for by students in the given timetable.
         * We use this number to decide how many table cells we'll have to display the courses in the report.
         */
        $maxStudentCourses = SmisHelper::getMaximumCoursesRegisteredFor($filter);

        $panelHeading = 'Consolidated marks per student';

        $content = $this->renderPartial('studentAnalysis', [
            'title' => 'Consolidated marks per student',
            'students' => $students,
            'maxStudentCourses' => $maxStudentCourses,
            'panelHeading' => $panelHeading,
            'filter' => $filter
        ]);

        // setup kartik\mpdf\Pdf component
        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => Pdf::FORMAT_A4,
            // portrait orientation
            'orientation' => Pdf::ORIENT_LANDSCAPE,
            // stream to browser inline
            'destination' => Pdf::DEST_BROWSER,
            // your html content input
            'content' => $content,
            // format content from your own css file if needed or use the
            // enhanced bootstrap css built by Krajee for mPDF formatting
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
            // any css to be embedded if required
            'cssInline' => '.kv-heading-1{font-size:18px}',
            // set mPDF properties on the fly
            'options' => ['title' => 'Krajee Report Title'],
            // call mPDF methods on the fly
            'methods' => [
                'SetHeader'=>['Krajee Report Header'],
                'SetFooter'=>['{PAGENO}'],
            ]
        ]);

        // return the pdf output as per the destination setting
        return $pdf->render();
//        Yii::$app->response->format = Response::FORMAT_RAW;
//        $pdf = new Pdf([
//            'mode' => Pdf::MODE_CORE, // leaner size using standard fonts
//            'destination' => Pdf::DEST_BROWSER,
//            'content' => $content,
//            'options' => [
//                // any mpdf options you wish to set
//            ],
//            'methods' => [
//                'SetTitle' => 'Privacy Policy - Krajee.com',
//                'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
//                'SetHeader' => ['Krajee Privacy Policy||Generated On: ' . date("r")],
//                'SetFooter' => ['|Page {PAGENO}|'],
//                'SetAuthor' => 'Kartik Visweswaran',
//                'SetCreator' => 'Kartik Visweswaran',
//                'SetKeywords' => 'Krajee, Yii2, Export, PDF, MPDF, Output, Privacy, Policy, yii2-mpdf',
//            ]
//        ]);
        return $pdf->render();
    }
}