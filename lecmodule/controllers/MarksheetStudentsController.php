<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 05-07-2021 10:53:38 
 * @modify date 05-07-2021 10:53:38 
 * @desc [description]
 */

namespace app\controllers;

use Yii;
use Exception;
use yii\db\Exception as dbException;
use yii\web\ServerErrorHttpException;

use app\components\SmisHelper;

use app\models\CourseAssignment;
use app\models\Department;

/**
 * [Description MarksheetStudentsController]
 */
class MarksheetStudentsController extends BaseController
{
    /**
     * Component setup
     * 
     * @return void 
     */
    public function init()
    {
        parent::init();
        $allowedRoles = ['LEC_SMIS_LECTURER'];
        SmisHelper::allowAccess($allowedRoles);
    }

    
    /**
     * @return array component behaviors
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
                    ]
                ]
            ]
        ];
    }


    /**
     * Get students registered in a marksheet 
     * 
     * @param string $assignmentId allocated course
     * @param string $type class list | exam list
     * 
     *  @throws Exception
     *  @throws ServerErrorHttpException
     * 
     * @return mixed class list view | exam list view | Error view
     */
    public function actionIndex(string $assignmentId, string $type)
    {
        try{
            if($assignmentId === ''){
                throw new Exception('The marksheet must be specified.');
            }

            if($type !== 'classList' && $type !== 'examList'){
                throw new Exception('The correct list type must be specified.');
            }

            $assignmentModel = CourseAssignment::find()->select(['MRKSHEET_ID'])
                ->where(['LEC_ASSIGNMENT_ID' => $assignmentId])->asArray()->one();

            $marksheetId = $assignmentModel['MRKSHEET_ID'];
            $marksheetStudents = $this->marksheetStudents($marksheetId);

            if($type === 'classList'){
                $title = 'Class list';
                $view = 'classList';
            }else{
                $title = 'Exam list';
                $view = 'examList';
            }

            $dept = Department::find()->select(['DEPT_NAME'])->where(['DEPT_CODE' => $this->deptCode])->one(); 

            return $this->render($view, [
                'title' => $title,
                'marksheetId' => $marksheetId,
                'deptCode' =>  $this->deptCode,
                'deptName' => $dept->DEPT_NAME,
                'date' => $marksheetStudents['today'],
                'searchModel' => $marksheetStudents['searchModel'],
                'studentMarksheetProvider' => $marksheetStudents['mkProvider'],
                'courseCode' => $marksheetStudents['courseCode'],
                'courseName' => $marksheetStudents['courseName'],
                'academicYear' => $this->getCurrentAcademicYear()
            ]);
        }catch(Exception | dbException $ex){
            if(YII_ENV_PROD) {
                if($ex instanceof dbException) $message = 'This request failed to process.';
                else $message = $ex->getMessage();
            }
            else $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            throw new ServerErrorHttpException($message, '500');
        }
    }
}

