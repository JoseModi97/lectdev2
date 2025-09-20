<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc Generate reports for the lecturer
 */

namespace app\controllers;

use app\components\SmisHelper;
use app\exceptions\ForbiddenException;
use app\models\Course;
use app\models\CourseAnalysisFilter;
use app\models\CourseWorkAssessment;
use app\models\DegreeProgramme;
use app\models\Group;
use app\models\LevelOfStudy;
use app\models\Marksheet;
use app\models\MarksheetDef;
use app\models\search\ConsolidatedMarksheetSearch;
use app\models\search\CourseAnalysisSearch;
use app\models\search\LecturerReportCourseAssignmentSearch;
use app\models\search\MarksPreviewSearch;
use app\models\search\MissingMarksSearch;
use app\models\Semester;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class LecturerReportsController extends BaseController
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
        SmisHelper::allowAccess(['LEC_SMIS_LECTURER']);
    }

    /**
     * Return all courses assigned to the logged in lecturer for the current academic year.
     * @param string $type
     * @return string lecturer course allocations page
     * @throws ServerErrorHttpException
     */
    public function actionIndex(string $type): string
    {
        try{
            if($type !== 'consolidated-marksheet' && $type !== 'class-perfomance-analysis' && $type !== 'missing-marks'){
                throw new Exception('The correct report type must be provided.');
            }

            $academicYear = $this->getCurrentAcademicYear();
            $searchModel = new LecturerReportCourseAssignmentSearch();
            $currentCoursesProvider = $searchModel->search(Yii::$app->request->queryParams,[
                'payrollNo' => $this->payrollNo,
                'academicYear' => $academicYear
            ]);         
            return $this->render('index', [
                'title' => 'My course allocations',
                'searchModel' => $searchModel,
                'currentCoursesProvider' => $currentCoursesProvider,
                'academicYear' => $academicYear,
                'reportType' => $type
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
     * Return the consolidated marks for each student registered for a marksheet.
     * @param string $marksheetId The marksheet for which we retrieve the student marks
     * @return string  consolidated marksheet page
     * @throws ServerErrorHttpException
     */
    public function actionConsolidatedMarksheet(string $marksheetId): string
    {
        try{
            $mkModel = MarksheetDef::findOne($marksheetId);
            $courseCode = $mkModel->course->COURSE_CODE;    
            $courseName = $mkModel->course->COURSE_NAME;

            $course = Course::find()->select(['DEPT_CODE'])->where(['COURSE_CODE' => $courseCode])->one();
            if($course->DEPT_CODE !== $this->deptCode){
                throw new ForbiddenException('You\'re not allowed to view reports from other departments.');
            }

            $searchModel = new ConsolidatedMarksheetSearch();
            $mkProvider = $searchModel->search(Yii::$app->request->queryParams,[
                'marksheetId' => $marksheetId,
            ]);
            
            return $this->render('consolidatedMarksheet', [
                'title' => 'Consolidated marksheet report',
                'searchModel' => $searchModel,
                'mkProvider' => $mkProvider,
                'marksheetId' => $marksheetId,
                'courseCode' => $courseCode,
                'courseName' => $courseName
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
     * @param string $marksheetId The marksheet for which we want to get analysis report
     * @return string the analysis report page
     * @throws ServerErrorHttpException
     */
    public function actionClassPerfomanceAnalysis(string $marksheetId): string
    {
        try{    
            $mkModel = MarksheetDef::findOne($marksheetId);
            $courseCode = $mkModel->course->COURSE_CODE;    
            $courseName = $mkModel->course->COURSE_NAME;

            $course = Course::find()->select(['DEPT_CODE'])->where(['COURSE_CODE' => $courseCode])->one();
            if($course->DEPT_CODE !== $this->deptCode)
                throw new ForbiddenException('You\'re not allowed to view reports from other departments.');

            $user = Yii::$app->user->identity->EMP_TITLE.' '.
                    Yii::$app->user->identity->SURNAME.' '.
                    Yii::$app->user->identity->OTHER_NAMES;
                    
            return $this->render('classPerfomanceAnalysis', [
                'title' => 'Class perfomance analysis report',
                'marksheetId' => $marksheetId,
                'courseCode' => $courseCode,
                'courseName' => $courseName,
                'academicYear' => $this->getCurrentAcademicYear(),
                'user' => $user,
                'date' => date('d-M-Y')
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
     * @param string $marksheetId The marksheet for which we want to get analysis report
     * @throws InvalidConfigException
     */
    public function actionClassPerfomanceStats(string $marksheetId)
    {
        try{
            $totalStudents = Marksheet::find()->where(['MRKSHEET_ID' => $marksheetId])->count();
            $totalFinalMarks = Marksheet::find()->where(['MRKSHEET_ID'=>$marksheetId])->sum('FINAL_MARKS');
            $totalExamMarks = Marksheet::find()->where(['MRKSHEET_ID'=>$marksheetId])->sum('EXAM_MARKS');
            $totalCourseMarks = Marksheet::find()->where(['MRKSHEET_ID'=>$marksheetId])->sum('COURSE_MARKS');
            $avgFinalMarks = $totalFinalMarks / $totalStudents;
            $avgExamMarks = $totalExamMarks / $totalStudents;
            $avgCourseMarks = $totalCourseMarks / $totalStudents;

            $gradesDistribution = Marksheet::find()
                ->select(['GRADE', 'COUNT(*) AS cnt'])
                ->where(['MRKSHEET_ID' => $marksheetId])
                ->groupBy(['GRADE'])
                ->orderBy(['GRADE' => SORT_ASC])
                ->createCommand()
                ->queryAll();
            
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
     * @param string $assessmentId The assessment for which we want to get the students with missing marks
     * @return string missing marks page
     * @throws ServerErrorHttpException
     */
    public function actionMissingMarks(string $assessmentId): string
    {
        try{
            $cwModel = CourseWorkAssessment::findOne($assessmentId);
            $searchModel = new MissingMarksSearch();
            $missingMarksProvider = $searchModel->search(Yii::$app->request->queryParams,[
                'assessmentId' => $assessmentId,
                'marksheetId' => $cwModel->MARKSHEET_ID 
            ]);

            return $this->render('missingMarks',[
                'title' => 'Missing marks report',
                'courseCode' => $cwModel->marksheetDef->course->COURSE_CODE,
                'courseName' => $cwModel->marksheetDef->course->COURSE_NAME,
                'missingMarksProvider' => $missingMarksProvider,
                'searchModel' => $searchModel,
                'assessmentName' => $cwModel->assessmentType->ASSESSMENT_NAME
            ]);
        } catch(Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            throw new ServerErrorHttpException($message, '500'); 
        }
    }
}