<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc Generate reports for the faculty admin
 */

namespace app\controllers;

use app\components\SmisHelper;
use app\models\Department;
use app\models\Faculty;
use app\models\MarksheetDef;
use app\models\search\ReturnedScriptsSearch;
use Exception;
use Yii;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

class FacultyAdminReportsController extends BaseController
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
                    ],
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
        SmisHelper::allowAccess(['LEC_SMIS_FAC_ADMIN']);
    }

    /**
     * Set the academic year to be used to generate various reports
     * @throws ServerErrorHttpException
     */
    public function actionSetAcademicYear()
    {
        try{
            $get = Yii::$app->request->get();
            $academicYear = $get['academic-year'];
            $reportType = $get['report-type'];

            if(empty($academicYear)) {
                throw new Exception('Academic year must be provided.');
            }

            if($reportType === 'createdTimetables'){
                $this->redirect(['/faculty-admin-reports/department-timetables', 'academicYear' => $academicYear]);
            }elseif($reportType === 'lecturerCourseAllocations') {
                $this->redirect(['/faculty-admin-reports/course-allocations-in-departments',
                    'academicYear' => $academicYear]);
            }elseif ($reportType === 'courseWorkDefinition'){
                $this->redirect(['/faculty-admin-reports/course-work-definition-in-departments',
                    'academicYear' => $academicYear]);
            }
        }catch (Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * List students with returned scripts for a marksheet
     * @throws ServerErrorHttpException
     * @return string returned scripts report view
     */
    public function actionReturnedScripts(): string
    {
        try{
            $request = Yii::$app->request;
            $marksheetId = $request->get('marksheetId');
            $level = $request->get('level');
            $deptCode = $request->get('deptCode');
            $type = $request->get('type');

            $department = Department::find()->select(['DEPT_NAME', 'FAC_CODE'])
                ->where(['DEPT_CODE' => $deptCode])->one();
            $deptName = $department->DEPT_NAME;

            $faculty = Faculty::find()->select(['FACULTY_NAME'])->where(['FAC_CODE' => $this->facCode])
                ->one();

            $mkModel = MarksheetDef::findOne($marksheetId);
            $courseCode = $mkModel->course->COURSE_CODE;    
            $courseName = $mkModel->course->COURSE_NAME;

            $searchModel = new ReturnedScriptsSearch();
            $returnedScriptsProvider = $searchModel->search(Yii::$app->request->queryParams, [
                'marksheetId' => $marksheetId
            ]);

            return $this->render('returnedScripts', [
                'title' => 'Returned scripts for marksheet ' . $marksheetId,
                'searchModel' => $searchModel,
                'returnedScriptsProvider' => $returnedScriptsProvider,
                'marksheetId' => $marksheetId,
                'courseCode' => $courseCode,
                'courseName' => $courseName,
                'facName' => $faculty->FACULTY_NAME,
                'academicYear' => $this->getCurrentAcademicYear(),
                'level' => $level, 
                'deptCode' => $deptCode,
                'deptName' => $deptName,
                'type' => $type
            ]);
        } catch(Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * Get created timetables in each department in a faculty
     * @param string|null $academicYear
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionDepartmentTimetables(?string $academicYear = null): string
    {
        try{
            if(empty($academicYear)){
                $academicYear = $this->academicYear;
            }

            $timetablesProvider = SmisHelper::departmentTimetables($this->facCode, $academicYear);

            return $this->render('//shared-reports/departmentTimetables', [
                'title' => 'Department timetables',
                'timetablesProvider' => $timetablesProvider,
                'academicYear' => $academicYear,
                'facCode' => $this->facCode,
                'level' => 'facAdmin'
            ]);
        }catch(Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * Get created timetables in a programme
     * @param string $deptCode
     * @param string $academicYear
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionProgrammesTimetables(string $deptCode, string $academicYear): string
    {
        try{
            $programmesDetails = SmisHelper::programmesTimetables($deptCode, $academicYear);

            return $this->render('//shared-reports/programmeTimetables', [
                'title' => 'Programme timetables',
                'createdMarksheetsProvider' => $programmesDetails['createdMarksheetsProvider'],
                'createdMarksheetsSearch' => $programmesDetails['createdMarksheetsSearch'],
                'academicYear' => $academicYear,
                'facName' => $programmesDetails['faculty']->FACULTY_NAME,
                'facCode' => $programmesDetails['department']->FAC_CODE,
                'deptName' => $programmesDetails['department']->DEPT_NAME,
                'level' => 'facAdmin'
            ]);
        }catch(Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * @param string|null $academicYear
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionCourseAllocationsInDepartments(?string $academicYear = null): string
    {
        try{
            if(empty($academicYear)){
                $academicYear = $this->academicYear;
            }

            $faculty = Faculty::find()->select(['FACULTY_NAME'])
                ->where(['FAC_CODE' => $this->facCode])->one();

            $departmentAllocationsProvider = SmisHelper::courseAllocationsInDepartments($this->facCode, $academicYear);

            return $this->render('//shared-reports/courseAllocationsInDepartments', [
                'title' => 'Lecturer course allocations in departments',
                'departmentAllocationsProvider' => $departmentAllocationsProvider,
                'academicYear' => $academicYear,
                'facCode' => $this->facCode,
                'facName' => $faculty->FACULTY_NAME,
                'level' => 'facAdmin'
            ]);
        }catch(Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * @param string $deptCode
     * @param string $academicYear
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionNumberOfCourseAllocationsPerLecturer(string $deptCode, string $academicYear): string
    {
        try{
            $allocationsDetails = SmisHelper::numberOfCourseAllocationsPerLecturer($deptCode, $academicYear);

            return $this->render('//shared-reports/NoOfcoursesPerLecturer', [
                'title' => 'Number of courses allocated per lecturer',
                'coursesProvider' => $allocationsDetails['coursesProvider'],
                'courseSearch' => $allocationsDetails['courseSearch'],
                'academicYear' => $academicYear,
                'deptCode' => $deptCode,
                'deptName' => $allocationsDetails['department']->DEPT_NAME,
                'facCode' => $allocationsDetails['faculty']->FAC_CODE,
                'facName' => $allocationsDetails['faculty']->FACULTY_NAME,
                'level' => 'facAdmin'
            ]);
        }catch (Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * @param string $payroll
     * @param string $academicYear
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionCourseAllocationsPerLecturer(string $payroll, string $academicYear): string
    {
        try{
            $allocationsDetails = SmisHelper::courseAllocationsPerLecturer($payroll, $academicYear);

            return $this->render('//shared-reports/coursesPerLecturer', [
                'title' => 'Courses allocated per lecturer',
                'academicYear' => $academicYear,
                'coursesProvider' => $allocationsDetails['coursesProvider'],
                'courseSearch' => $allocationsDetails['courseSearch'],
                'deptCode' => $allocationsDetails['staff']['DEPT_CODE'],
                'deptName' => $allocationsDetails['staff']['DEPT_NAME'],
                'lecturer' => $allocationsDetails['staff']['EMP_TITLE'] . ' ' . $allocationsDetails['staff']['SURNAME']
                    . ' ' . $allocationsDetails['staff']['OTHER_NAMES'],
                'level' => 'facAdmin'
            ]);
        }catch (Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * Get cw created per department
     * @param string|null $academicYear
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionCourseWorkDefinitionInDepartments(?string $academicYear = null): string
    {
        try{
            if(empty($academicYear)){
                $academicYear = $this->academicYear;
            }

            $faculty = Faculty::find()->select(['FACULTY_NAME'])->where(['FAC_CODE' => $this->facCode])->one();

            $courseworkProvider = SmisHelper::courseWorkDefinitionInDepartments($this->facCode, $academicYear);

            return $this->render('//shared-reports/courseworkInDepartment', [
                'title' => 'Course work definition in departments',
                'facultyName' => $faculty->FACULTY_NAME,
                'courseWorkProvider' => $courseworkProvider,
                'academicYear' => $academicYear,
                'level' => 'facAdmin'
            ]);
        }catch(Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * Get cw created per programme course
     * @param string $deptCode
     * @param string $academicYear
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionProgrammeCourseWorkDefinition(string $deptCode, string $academicYear): string
    {
        try{
            $cwDetails = SmisHelper::programmeCourseWorkDefinition($deptCode, $academicYear);

            return $this->render('//shared-reports/courseworkInProgrammes', [
                'title' => 'Course work definition in programmes',
                'programmeCourseWorkProvider' => $cwDetails['programmeCourseWorkProvider'],
                'programmeCourseWorkSearch' => $cwDetails['programmeCourseWorkSearch'],
                'academicYear' => $academicYear,
                'facultyName' => $cwDetails['faculty']->FACULTY_NAME,
                'facultyCode' => $cwDetails['department']->FAC_CODE,
                'departmentName' => $cwDetails['department']->DEPT_NAME,
                'departmentCode' => $deptCode,
                'level' => 'facAdmin'
            ]);
        }catch (Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * Get cw created per course
     * @param string $marksheetId
     * @param string $deptCode
     * @param string $academicYear
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionCourseWorkDefinitions(string $marksheetId, string $deptCode, string $academicYear): string
    {
        try{
            $cwDetails = SmisHelper::courseWorkDefinitions($marksheetId, $deptCode, $academicYear);

            return $this->render('//shared-reports/marksheetCwDefinitions', [
                'title' => 'Marksheet course work definition',
                'assessmentsSearch' => $cwDetails['assessmentsSearch'],
                'assessmentsProvider' => $cwDetails['assessmentsProvider'],
                'academicYear' => $academicYear,
                'departmentCode' => $deptCode,
                'departmentName' => $cwDetails['department']->DEPT_NAME,
                'courseCode' => $cwDetails['course']->COURSE_CODE,
                'courseName' => $cwDetails['course']->COURSE_NAME,
                'level' => 'facAdmin'
            ]);
        }catch (Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }
}

