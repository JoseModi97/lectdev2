<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 26-01-2021 18:05:05 
 * @modify date 26-01-2021 18:05:05 
 * @desc [description]
 */

namespace app\controllers;

use Yii;
use yii\web\Response;
use Exception;
use yii\db\Exception as dbException;
use yii\web\ServerErrorHttpException;
use yii\filters\VerbFilter;

use app\components\SmisHelper;

use app\models\Department;
use app\models\Faculty;
use app\models\search\DepartmentsSearch;

class DepartmentsController extends BaseController {

    /**
     * Initialize object.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $allowedRoles = [
            'LEC_SMIS_FAC_ADMIN',
            'LEC_SMIS_DEAN'
        ];
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
     * Get all departments in a faculty.
     * 
     * @throws ServerErrorHttpException
     * 
     * @return string departments in a faculty view.
     */
     public function actionInFaculty()
     {
        try{
            $request = Yii::$app->request;
            $level = $request->get('level');
            $type = $request->get('type');

            if($level !== 'dean' && $level !== 'facAdmin'){
                throw new Exception('You must give the correct approval level.'); 
            }

            if($level === 'facAdmin'){
                if($type !== 'operations' && $type !== 'reports'){
                    throw 
                        new Exception('You must give the correct operation type for the faculty admin');
                }
            }
                   
            $faculty = Faculty::find()->select(['FACULTY_NAME'])->where(['FAC_CODE' => $this->facCode])->one();
            $facName = $faculty->FACULTY_NAME;

            $searchModel = new DepartmentsSearch();
            $departmentsProvider = $searchModel->search(Yii::$app->request->queryParams, [
                'facCode' => $this->facCode
            ]);

            return $this->render('index', [
                'title' => 'Departments in the ' . strtolower($facName),
                'searchModel' => $searchModel,
                'departmentsProvider' => $departmentsProvider,
                'facCode' => $this->facCode,
                'facName' => $facName,
                'level' => $level,
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