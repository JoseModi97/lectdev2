<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 */

namespace app\controllers;

use app\components\SmisHelper;
use app\models\AssessmentType;
use app\models\Course;
use app\models\CourseWorkAssessment;
use app\models\EmpVerifyView;
use app\models\MarksheetDef;
use app\models\search\MarksheetSearch;
use app\models\Semester;
use Exception;
use Yii;
use yii\db\Expression;
use yii\web\Controller;
use yii\web\ServerErrorHttpException;

class BaseController extends Controller
{
    /**
     * @var string logged-in user payroll number
     */
    protected $payrollNo;

    /**
     * @var string logged-in user department code
     */
    protected $deptCode;

    /**
     * @var string logged-in user department name
     */
    protected $deptName;

    /**
     * @var string logged-in user faculty code
     */
    protected $facCode;

    /**
     * @var string logged-in user faculty name
     */
    protected $facName;

    /**
     * @var string current academic year
     */
    protected $academicYear;

    /**
     * Initialize the controllers
     *
     * @throws ServerErrorHttpException
     */
    public function init()
    {
        try{
            parent::init();

            /**
             * We use the username, password and secretKey stored in the session to make a db connection.
             * If either is missing logout user.
             */
            $session = Yii::$app->session;
            if(empty($session->get('username')) || empty($session->get('password'))
                || empty($session->get('secretKey'))){
                Yii::$app->user->switchIdentity(null);
            }

            if(Yii::$app->user->isGuest){
                return $this->redirect('site/login');
            }else{
                $staff = EmpVerifyView::find()
                    ->select([
                        'DEPT_CODE',
                        'DEPT_NAME',
                        'FAC_CODE',
                        'FACULTY_NAME'
                    ])
                    ->where(['PAYROLL_NO' => Yii::$app->user->identity->PAYROLL_NO])
                    ->asArray()
                    ->one();

                $this->payrollNo = Yii::$app->user->identity->PAYROLL_NO;
                $this->deptCode = $staff['DEPT_CODE'];
                $this->deptName = $staff['DEPT_NAME'];
                $this->facCode = $staff['FAC_CODE'];
                $this->facName = $staff['FACULTY_NAME'];
                $this->academicYear = $this->getCurrentAcademicYear();
            }
        }catch(Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV) {
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * Set flash message
     *
     * @param string $type
     * @param string $title
     * @param string $msg
     *
     * @return void
     */
    protected function setFlash(string $type, string $title, string $msg):void
    {
        Yii::$app->getSession()->setFlash('new', [
            'type' => $type,
            'title' => $title,
            'message' => $msg
        ]);
    }

    /**
     * @param string $type
     * @param string $title
     * @param string $msg
     *
     * @return void
     */
    protected function addFlash(string $type, string $title, string $msg):void
    {
        Yii::$app->getSession()->addFlash('added', [
            'type' => $type,
            'title' => $title,
            'message' => $msg
        ]);
    }

    /**
     * Get the current academic year
     * @return string
     * @throws Exception
     */
    protected function getCurrentAcademicYear(): string
    {
        if(YII_ENV_DEV){
            return '2020/2021';
        }else{
            return '2021/2022';
        }

//        $todayDate = SmisHelper::getDateTime('now', false);
//
//        $sql = "SELECT PERIOD_CODE FROM MUTHONI.ACADEMIC_PERIOD
//                WHERE TO_DATE(:todayDate, 'DD-MON-YYYY HH24:MI:SS') >= ACADEMIC_PERIOD.PERIOD_START_DATE
//                AND TO_DATE(:todayDate, 'DD-MON-YYYY HH24:MI:SS') <= ACADEMIC_PERIOD.PERIOD_END_DATE";
//
//        $connection = Yii::$app->getDb();
//        try{
//            $academicPeriod = $connection->createCommand($sql)->bindValue(':todayDate', $todayDate)->queryOne();
//        }catch (Exception $ex){
//            throw new Exception('Request to get the current academic year failed.');
//        }
//
//        return $academicPeriod['PERIOD_CODE'];
    }

    /**
     * Get students registered for this marksheet
     * Get course code and name of this marksheet
     * Get current date
     * @param string $marksheetId
     * @return array marksheet details
     * @throws Exception
     */
    protected function marksheetStudents(string $marksheetId): array
    {
        $mkModel = MarksheetDef::find()->select(['COURSE_ID','SEMESTER_ID'])
            ->where(['MRKSHEET_ID' => $marksheetId])->asArray()->one();

        $courseModel = Course::find()->select(['COURSE_CODE', 'COURSE_NAME'])
            ->where(['COURSE_ID' => $mkModel['COURSE_ID']])->asArray()->one();
        $courseCode = $courseModel['COURSE_CODE'];
        $courseName = $courseModel['COURSE_NAME'];

        $semester = Semester::find()->select(['SEMESTER_TYPE'])
            ->where(['SEMESTER_ID' => $mkModel['SEMESTER_ID']])->asArray()->one();

        $today = SmisHelper::getDateTime('now', false);

        $searchModel = new MarksheetSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,[
            'marksheetId' => $marksheetId,
            'semesterType' => $semester['SEMESTER_TYPE']
        ]);

        return [
            'courseCode' => $courseCode,
            'courseName' => $courseName,
            'today' => $today,
            'searchModel' =>  $searchModel,
            'mkProvider' => $dataProvider
        ];
    }

    /**
     * Format the date for saving in the db
     * @param $date
     * @param string $format
     * @return false|string
     */
    protected function getSaveDate($date, string $format = "d-M-Y")
    {
        $time = strtotime($date);
        return date($format, $time);
    }

    /**
     * Check if marksheet belongs to a supplementary semester.
     * @param string $marksheetId
     * @return bool
     */
    protected function isSupplementary(string $marksheetId):bool
    {
        $mkModel = MarksheetDef::find()->select(['COURSE_ID','SEMESTER_ID'])
            ->where(['MRKSHEET_ID' => $marksheetId])->asArray()->one();

        $semester = Semester::find()->select(['SEMESTER_TYPE'])
            ->where(['SEMESTER_ID' => $mkModel['SEMESTER_ID']])->asArray()->one();

        if($semester['SEMESTER_TYPE'] === 'SUPPLEMENTARY'){
            return true;
        }else{
            return false;
        }
    }

    /**
     * For courses with single exam components or courses that don't require coursework marks,
     * we create the assessment of type EXAM under which exam marks will be saved.
     * @param string $marksheetId
     * @param bool $requiresCoursework
     * @param bool $isSupplementary
     * @return array details of the newly created assessment.
     * @throws Exception
     */
    protected function createExamEntry(string $marksheetId, bool $requiresCoursework, bool $isSupplementary): array
    {
        $examType = AssessmentType::find()->where(['ASSESSMENT_NAME' => 'EXAM'])->one();

        if(is_null($examType)){
            $examType = new AssessmentType();
            $examType->ASSESSMENT_NAME = 'EXAM';
            $examType->ASSESSMENT_DESCRIPTION = 'EXAM';
            $examType->LOCKED = 0;
            $examType->save();
        }

        $examAssessment = CourseWorkAssessment::find()->alias('CW')
            ->joinWith(['assessmentType AT'])
            ->where(['CW.MARKSHEET_ID' => $marksheetId,'AT.ASSESSMENT_NAME' => 'EXAM'])
            ->one();

        if(is_null($examAssessment)){
            $ratios = SmisHelper::getRatios($marksheetId);
            $examAssessment = new CourseWorkAssessment();
            $examAssessment->MARKSHEET_ID = $marksheetId;
            $examAssessment->ASSESSMENT_TYPE_ID = $examType->getPrimaryKey();

            if($requiresCoursework && !$isSupplementary){
                $examAssessment->WEIGHT = $ratios['examRatio'];
                $examAssessment->DIVIDER = $ratios['examRatio'];
            }else{
                $examAssessment->WEIGHT = 100;
                $examAssessment->DIVIDER = 100;
            }

            $examAssessment->RESULT_DUE_DATE = new Expression('CURRENT_DATE');
            $examAssessment->save();
        }

        return [
            'assessmentId' => $examAssessment->ASSESSMENT_ID,
            'assessmentName' => 'EXAM',
            'maximumMarks' => $examAssessment->DIVIDER
        ];
    }
}