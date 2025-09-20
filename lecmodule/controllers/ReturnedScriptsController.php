<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 16-02-2021 12:26:37 
 * @modify date 16-02-2021 12:26:37 
 * @desc [description]
 */

namespace app\controllers;

use Yii;
use Exception;
use yii\db\Exception as dbException;
use yii\web\ServerErrorHttpException;
use yii\db\Expression;

use app\components\SmisHelper;

use app\models\MarksheetDef;
use app\models\ReturnedScript;
use app\models\Department;
use app\models\Faculty;

use app\models\search\MarksheetSearch;

class ReturnedScriptsController extends BaseController 
{     
    /**
     * Initialize object.
     * 
     * @return void
     */
    public function init()
    {
        parent::init();
        $allowedRoles = ['LEC_SMIS_FAC_ADMIN'];
        SmisHelper::allowAccess($allowedRoles);
    }

    
    /**
     * Configure object behaviours.
     * 
     * @return array The behavior configurations.
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
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
     * List students registered for a marksheet inorder to mark their 
     * scripts as returned or not.
     * 
     * @throws ServerErrorHttpException
     * 
     * @return string marks scripts view
     */
    public function actionMarkScriptsView()
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

            $searchModel = new MarksheetSearch();
            $mkProvider = $searchModel->search(Yii::$app->request->queryParams,[
                'marksheetId' => $marksheetId,
            ]);

            $department = Department::find()->select(['DEPT_NAME'])
                ->where(['DEPT_CODE' => $deptCode])->one();
            $deptName = $department->DEPT_NAME;

            $faculty = Faculty::find()->select(['FACULTY_NAME'])->where(['FAC_CODE' => $this->facCode])
                ->one();

            return $this->render('mark-scripts', [
                'title' => 'Exam register for marksheet '. $marksheetId,
                'searchModel' => $searchModel,
                'mkProvider' => $mkProvider,
                'marksheetId' => $marksheetId,
                'courseCode' => $courseCode,
                'courseName' => $courseName,
                'level' => $level, 
                'deptCode' => $deptCode,
                'deptName' => $deptName,
                'type' => $type,
                'facName' => $faculty->FACULTY_NAME,
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


    /**
     * Record scripts that have been returned or not.
     * 
     * @var array allScripts remarks of all records in the grid at the time the form is submitted
     * @var array returnedScripts selected records in the grid at the time the form is submitted. These are the returned scripts
     * 
     * @return string redirect back to mark returned scripts view
     */
    public function actionMarkScripts()
    {
        try{
            $transaction = Yii::$app->db->beginTransaction();
            $post = Yii::$app->request->post();
            $marksheetId = $post['MARKSHEET_ID'];
            $allScripts = $post['REMARKS'];
            // if all scripts are not returned, this is not set
            $returnedScripts = (isset($post['selection'])) ? $post['selection'] : NULL;
            $scriptReturned = (int)1;
            $scriptNotReturned = (int)0;

            foreach($allScripts as $regNumber => $remarks){
                $returnedScript = ReturnedScript::find()->where(['MARKSHEET_ID' => $marksheetId, 'REGISTRATION_NUMBER' =>$regNumber])->one();
                if(is_null($returnedScript))
                    $returnedScript = new ReturnedScript();
                $returnedScript->MARKSHEET_ID = $marksheetId;
                $returnedScript->REGISTRATION_NUMBER = $regNumber;
                $returnedScript->RETURNED_BY = $this->payrollNo;
                $returnedScript->RETURNED_DATE = new Expression('CURRENT_DATE');
                $returnedScript->REMARKS = ($remarks === '') ? NULL : $remarks;
                if(is_null($returnedScripts))
                    $returnedScript->STATUS = $scriptNotReturned;
                else
                    $returnedScript->STATUS = in_array($regNumber, $returnedScripts) ? $scriptReturned : $scriptNotReturned;
                if(!$returnedScript->save())
                    throw new Exception('Failed to save the retured script status.');
            }

            $transaction->commit();
            $this->setFlash('success', 'Returned Scripts', 'The returned scripts have been updated successfully.');
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        }
        catch(Exception | dbException $ex){
            $transaction->rollBack();
            if(YII_ENV_PROD) {
                if($ex instanceof dbException) $message = 'This request failed to process.';
                else $message = $ex->getMessage();
            }
            else $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            throw new ServerErrorHttpException($message, '500');
        }
    }
}
