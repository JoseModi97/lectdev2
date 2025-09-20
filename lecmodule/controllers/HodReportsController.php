<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc Generate reports for the HOD
 */

namespace app\controllers;

use app\components\SmisHelper;
use app\models\CourseAnalysisFilter;
use app\models\Department;
use app\models\Faculty;
use app\models\MarksheetDef;
use app\models\search\CourseAnalysisSearch;
use app\models\search\SubmittedMarksSearch;
use Exception;
use Yii;
use yii\db\Exception as dbException;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

class HodReportsController extends BaseController
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
        SmisHelper::allowAccess(['LEC_SMIS_HOD']);
    }

    
    /**
     * List the submitted marks for a marksheet
     * 
     * @throws ServerErrorHttpException 
     * 
     * @return string submitted marks view 
     */
    public function actionSubmittedMarks()
    {
        try{
            $request = Yii::$app->request;
            $marksheetId = $request->get('marksheetId');
            $level = $request->get('level');
            $deptCode = $request->get('deptCode');
            $type = $request->get('type');

            $mkModel = MarksheetDef::findOne($marksheetId);
            $courseCode = $mkModel->course->COURSE_CODE;    
            $courseName = $mkModel->course->COURSE_NAME;

            $searchModel = new SubmittedMarksSearch();
            $submittedMarkskProvider = $searchModel->search(Yii::$app->request->queryParams,[
                'deptCode' => $this->deptCode,
                'facCode' => $this->facCode,
                'marksheetId' => $marksheetId,
                'level' => 'hod',
            ]);

            $department = Department::find()->select(['DEPT_NAME'])
                ->where(['DEPT_CODE' => $deptCode])->one();
            $deptName = $department->DEPT_NAME;

            $faculty = Faculty::find()->select(['FACULTY_NAME'])->where(['FAC_CODE' => $this->facCode])
                ->one();

            return $this->render('submittedMarks', [
                'title' => 'Submitted marks for marksheet ' . $marksheetId,
                'searchModel' => $searchModel,
                'submittedMarkskProvider' => $submittedMarkskProvider,
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
        }
        catch(Exception | dbException $ex){
            if(YII_ENV_PROD) {
                if($ex instanceof dbException) $message = 'This request failed to process.';
                else $message = $ex->getMessage();
            }
            else $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            throw new ServerErrorHttpException($message, '500');
        }
    }
}