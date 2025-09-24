<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc Generate reports for the system administrator
 */

namespace app\controllers;

use app\components\SmisHelper;
use app\models\CourseAssignment;
use app\models\CourseWorkAssessment;
use app\models\Department;
use app\models\EmpVerifyView;
use app\models\Faculty;
use Exception;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

class SystemAdminReportsController extends BaseController
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
        //SmisHelper::allowAccess(['LEC_SMIS_SYS_ADMIN']);
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
                $this->redirect(['/system-admin-reports/faculty-timetables', 'academicYear' => $academicYear]);
            }elseif($reportType === 'lecturerCourseAllocations') {
                $this->redirect(['/system-admin-reports/course-allocations-in-faculties',
                    'academicYear' => $academicYear]);
            }elseif ($reportType === 'courseWorkDefinition'){
                $this->redirect(['/system-admin-reports/course-work-definition-in-faculties',
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
     * Get created timetables in a faculty
     * @param string|null $academicYear
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionFacultyTimetables(?string $academicYear = null): string
    {
        try{
            if(empty($academicYear)){
                $academicYear = $this->academicYear;
            }

            $facultyCodes = Yii::$app->params['newFaculties'];

            $faculties = Faculty::find()->select(['FAC_CODE', 'FACULTY_NAME'])
                ->where(['IN', 'FAC_CODE', $facultyCodes])->asArray()->all();

            $timetables = [];
            foreach($faculties as $faculty){
                $facCode = $faculty['FAC_CODE'];

                $departments = Department::find()->select(['DEPT_CODE'])
                    ->where(['FAC_CODE' => $facCode])->asArray()->all();
                if(empty($departments)){
                    $timetableCount = 0;
                }else{
                    $deptCodes = [];
                    foreach($departments as $department){
                        $deptCodes[] = $department['DEPT_CODE'];
                    }
                    $timetableCount = $this->totalTimetablesInFaculty($facCode, $deptCodes, $academicYear);
                }
              
                $timetable = [
                    'facCode' => $facCode,
                    'facName' => $faculty['FACULTY_NAME'],
                    'timetableCount' => $timetableCount
                ];
                $timetables[] = $timetable;
            }

            $timetablesProvider = new ArrayDataProvider([
                'allModels' => $timetables,
                'sort' => false,
                'pagination' => [
                    'pageSize' => 20,
                ],
            ]);

            return $this->render('facultyTimetables', [
                'title' => 'Faculty timetables',
                'timetablesProvider' => $timetablesProvider,
                'academicYear' => $academicYear
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
     * Get created timetables in a faculty
     * @param string $facCode
     * @param string $academicYear
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionDepartmentTimetables(string $facCode, string $academicYear): string
    {
        try{
            $timetablesProvider = SmisHelper::departmentTimetables($facCode, $academicYear);
            return $this->render('//shared-reports/departmentTimetables', [
                'title' => 'Department timetables',
                'timetablesProvider' => $timetablesProvider,
                'academicYear' => $academicYear,
                'facCode' => $facCode,
                'level' => 'sysAdmin'
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
                'level' => 'sysAdmin'
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
    public function actionCourseAllocationsInFaculties(?string $academicYear = null): string
    {
        try{
            if(empty($academicYear)){
                $academicYear = $this->academicYear;
            }

            // Get the number of lecturers in each faculty
            $facultyCodes = Yii::$app->params['newFaculties'];
            $faculties = EmpVerifyView::find()
                ->select(['FAC_CODE', 'FACULTY_NAME', 'COUNT(*) AS staffNumber'])
                ->where(['STATUS_DESC' => 'ACTIVE', 'JOB_CADRE' => 'ACADEMIC'])
                ->andWhere(['IN', 'FAC_CODE', $facultyCodes])
                ->groupBy(['FAC_CODE', 'FACULTY_NAME'])
                ->orderBy(['FAC_CODE' => SORT_ASC])
                ->asArray()->all();

            /**
             * Get all lectures in EACH faculty,
             * who have been allocated at least 1 course for the academic year given
             */
            $facultyAllocations = [];
            foreach ($faculties as $faculty){
                $facCode = $faculty['FAC_CODE'];

                $lecturersAllocated = CourseAssignment::find()->alias('CW')->select(['CW.PAYROLL_NO'])
                    ->where(['LIKE', 'CW.MRKSHEET_ID', $academicYear . '%', false])
                    ->joinWith(['staff ST' => function(ActiveQuery $q){
                        $q->select([
                            'ST.PAYROLL_NO',
                            'ST.FAC_CODE'
                        ]);
                    }], true,'INNER JOIN')
                    ->andWhere(['ST.FAC_CODE' => $facCode])
                    ->distinct()->asArray()->all();

                $lecturersWithCourses = EmpVerifyView::find()
                    ->where(['FAC_CODE' => $facCode])
                    ->andWhere(['IN', 'PAYROLL_NO', $lecturersAllocated])
                    ->count();
                $facultyAllocation = [
                    'facCode' => $facCode,
                    'facName' => $faculty['FACULTY_NAME'],
                    'lecturers' => $faculty['staffNumber'],
                    'lecturersWithCourses' => $lecturersWithCourses
                ];
                $facultyAllocations[] = $facultyAllocation;
            }

            $facultyAllocationsProvider = new ArrayDataProvider([
                'allModels' => $facultyAllocations,
                'sort' => false,
                'pagination' => [
                    'pageSize' => 20,
                ],
            ]);

            return $this->render('courseAllocationsInFaculties', [
                'title' => 'Lecturer course allocations in faculties',
                'facultyAllocationsProvider' => $facultyAllocationsProvider,
                'academicYear' => $academicYear
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
     * @param string $facCode
     * @param string $academicYear
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionCourseAllocationsInDepartments(string $facCode, string $academicYear): string
    {
        try{
            $faculty = Faculty::find()->select(['FACULTY_NAME'])->where(['FAC_CODE' => $facCode])->one();
            $departmentAllocationsProvider = SmisHelper::courseAllocationsInDepartments($facCode, $academicYear);
            return $this->render('//shared-reports/courseAllocationsInDepartments', [
                'title' => 'Lecturer course allocations in departments',
                'departmentAllocationsProvider' => $departmentAllocationsProvider,
                'academicYear' => $academicYear,
                'facCode' => $facCode,
                'facName' => $faculty->FACULTY_NAME,
                'level' => 'sysAdmin'
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
                'level' => 'sysAdmin'
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
                'level' => 'sysAdmin'
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
     * Get cw created per faculty
     * @param string|null $academicYear
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionCourseWorkDefinitionInFaculties(?string $academicYear = null): string
    {
        try {
            if(empty($academicYear)){
                $academicYear = $this->academicYear;
            }

            $facultyCodes = Yii::$app->params['newFaculties'];
            $faculties = Faculty::find()->select(['FAC_CODE', 'FACULTY_NAME'])
                ->where(['IN', 'FAC_CODE', $facultyCodes])->asArray()->all();

            // Get total no.of course works created in EACH faculty
            $courseWorks = [];
            foreach ($faculties as $faculty){
                $facCode = $faculty['FAC_CODE'];

                $courseworkCount = $this->totalCourseworkDefined([
                    'academicYear' => $academicYear,
                    'facCode' => $facCode,
                    'deptCode' => null
                ]);

                $courseWork = [
                    'facCode' => $facCode,
                    'facName' => $faculty['FACULTY_NAME'],
                    'courseworkCount' => $courseworkCount
                ];
                $courseWorks[] = $courseWork;
            }

            $courseworkProvider = new ArrayDataProvider([
                'allModels' => $courseWorks,
                'sort' => false,
                'pagination' => [
                    'pageSize' => 20,
                ],
            ]);

            return $this->render('courseworkInFaculty', [
                'title' => 'Course work definition in faculties',
                'courseWorkProvider' => $courseworkProvider,
                'academicYear' => $academicYear
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
     * @param string $facCode
     * @param string $academicYear
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionCourseWorkDefinitionInDepartments(string $facCode, string $academicYear): string
    {
        try{
            $faculty = Faculty::find()->select(['FACULTY_NAME'])->where(['FAC_CODE' => $facCode])->one();
            $courseworkProvider = SmisHelper::courseWorkDefinitionInDepartments($facCode, $academicYear);
            return $this->render('//shared-reports/courseworkInDepartment', [
                'title' => 'Course work definition in departments',
                'facultyName' => $faculty->FACULTY_NAME,
                'courseWorkProvider' => $courseworkProvider,
                'academicYear' => $academicYear,
                'level' => 'sysAdmin'
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
     * Get cw created per programme per course
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
                'level' => 'sysAdmin'
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
                'level' => 'sysAdmin'
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
     * @param string $facCode
     * @param array $deptCodes
     * @param string $academicYear
     * @return int
     * @throws Exception
     */
    private function totalTimetablesInFaculty(string $facCode, array $deptCodes, string $academicYear): int
    {
        $params = [
            ':academicYear' => $academicYear,
            ':facCode' => $facCode
        ];
        
        $deptList = '(';
        for($i = 0; $i < count($deptCodes); $i++){
            $params[':dept' . $i] = $deptCodes[$i];
            $deptList .= ':dept' . $i . ',';
        }
        $deptList = rtrim($deptList, ',');
        $deptList .= ')';
        
        $sql = "SELECT 
            COUNT(DISTINCT(substr(MUTHONI.MARKSHEET_DEF.MRKSHEET_ID, 1, instr(MUTHONI.MARKSHEET_DEF.MRKSHEET_ID, '_', 1, 5))))            
            AS NUMBER_OF_TIMETABLES
            FROM MUTHONI.MARKSHEET_DEF 
            INNER JOIN MUTHONI.SEMESTERS ON MUTHONI.MARKSHEET_DEF.SEMESTER_ID = MUTHONI.SEMESTERS.SEMESTER_ID
            INNER JOIN MUTHONI.DEGREE_PROGRAMMES ON MUTHONI.SEMESTERS.DEGREE_CODE = MUTHONI.DEGREE_PROGRAMMES.DEGREE_CODE
            INNER JOIN MUTHONI.COURSES ON MUTHONI.MARKSHEET_DEF.COURSE_ID = MUTHONI.COURSES.COURSE_ID
            INNER JOIN MUTHONI.DEPARTMENTS ON MUTHONI.COURSES.DEPT_CODE = MUTHONI.DEPARTMENTS.DEPT_CODE
            WHERE MUTHONI.MARKSHEET_DEF.MRKSHEET_ID LIKE CONCAT(:academicYear, '%')
            AND MUTHONI.DEPARTMENTS.FAC_CODE = :facCode
            AND MUTHONI.COURSES.DEPT_CODE IN " . $deptList;

        $connection = Yii::$app->getDb();
        try{
            return $connection->createCommand($sql)->bindValues($params)->queryScalar();
        }catch (Exception $ex){
            throw new Exception('Request to get the number of timetables failed.');
        }
    }

    /**
     * Get course works defined
     * @param array $params
     * @return bool|int|string|null
     */
    private function totalCourseworkDefined(array $params)
    {
        $courseWorkCount = CourseWorkAssessment::find()->alias('CW')
            ->select(['CW.MARKSHEET_ID'])
            ->joinWith(['assessmentType AT'], true, 'INNER JOIN')
            ->joinWith(['marksheetDef MD' => function (ActiveQuery $q) {
                $q->select([
                    'MD.MRKSHEET_ID',
                    'MD.COURSE_ID',
                    'MD.SEMESTER_ID'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['marksheetDef.semester SM' => function (ActiveQuery $q) {
                $q->select([
                    'SM.SEMESTER_ID',
                    'SM.ACADEMIC_YEAR'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['marksheetDef.course CS' => function (ActiveQuery $q) {
                $q->select([
                    'CS.COURSE_ID',
                    'CS.DEPT_CODE'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['marksheetDef.course.dept DEPT' => function (ActiveQuery $q) {
                $q->select([
                    'DEPT.DEPT_CODE',
                    'DEPT.FAC_CODE'
                ]);
            }], true, 'INNER JOIN')
            ->where(['SM.ACADEMIC_YEAR' => $params['academicYear']])
            ->andWhere(['NOT', ['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT']])
            ->andWhere(['NOT', ['AT.ASSESSMENT_NAME' => 'EXAM']]);

        if(!is_null($params['deptCode']) && is_null($params['facCode'])){
            $courseWorkCount->andWhere(['DEPT.DEPT_CODE' => $params['deptCode']]);
        }

        if(is_null($params['deptCode']) && !is_null($params['facCode'])){
            $courseWorkCount->andWhere(['DEPT.FAC_CODE' => $params['facCode']]);
        }

        return $courseWorkCount->count();
    }
}
