<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 02-09-2021 15:01:35 
 * @modify date 02-09-2021 15:01:35 
 * @desc [description]
 */

namespace app\controllers;

use Yii;
use Exception;
use yii\web\HttpException;
use yii\db\Exception as dbException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use yii\filters\AccessControl;

use app\components\SmisHelper;

use app\models\Department;
use app\models\Faculty;
use app\models\search\DepartmentCoursesSearch; 

class CoursesController extends BaseController 
{
    /**
     * Initialize object.
     * 
     * @return void
     */
    public function init()
    {
        parent::init();
        $allowedRoles = [
            'LEC_SMIS_HOD', 
            'LEC_SMIS_DEAN', 
            'LEC_SMIS_FAC_ADMIN'
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
                'class' => AccessControl::className(),
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
     * List courses in a department
     * 
     * @throws ServerErrorHttpException
     * 
     * @return string courses in a department view 
     */
    public function actionInDepartment()
    {
        try{
            $request = Yii::$app->request;
            $level = $request->get('level');
            $deptCode = $request->get('deptCode');
            $type = $request->get('type');

            if($level !== 'hod' && $level !== 'dean' && $level !== 'facAdmin'){
                throw new Exception('You must give the correct approval level.'); 
            }

            $roles= Yii::$app->session->get('roles');

            $department = Department::find()->select(['DEPT_NAME', 'FAC_CODE'])
                ->where(['DEPT_CODE' => $deptCode])->one();
            $deptName = $department->DEPT_NAME;

            $faculty = Faculty::find()->select(['FACULTY_NAME'])->where(['FAC_CODE' => $this->facCode])
                ->one();

            if($level === 'hod' && in_array('LEC_SMIS_HOD', $roles)){
                if($this->deptCode !== $deptCode){
                    throw new Exception('The HOD is not allowed to view courses in another department.');
                }
            }elseif($level === 'dean' && in_array('LEC_SMIS_DEAN', $roles)){
                if($this->facCode !== $department->FAC_CODE){
                    throw new Exception('The Dean is not allowed to view courses in another faculty.');
                }
            }elseif($level === 'facAdmin' && in_array('LEC_SMIS_FAC_ADMIN', $roles)){
                if($this->facCode !== $department->FAC_CODE){
                    throw new Exception('The faculty administrator is not allowed to view courses in another faculty.');
                }
            }else{
                throw new Exception('You are not allowed to view these courses.');
            }
          
            $academicYear = $this->getCurrentAcademicYear();

            $searchModel = new DepartmentCoursesSearch();
            $departmentCoursesProvider = $searchModel->search(Yii::$app->request->queryParams, [
                'deptCode' => $deptCode,
                'academicYear' => $academicYear
            ]);

            $returnData = [
                'title' => 'Courses in the department of ' . strtolower($deptName),
                'searchModel' => $searchModel,
                'departmentCoursesProvider' => $departmentCoursesProvider,
                'deptCode' => $deptCode,
                'deptName' => $deptName,
                'academicYear' => $academicYear,
                'level' => $level,
                'type' => $type,
                'facName' => $faculty->FACULTY_NAME,
            ];

            if($level === 'hod' && $type === 'reports'){
                return $this->render('//hod-reports/index',$returnData);
            }

            if($level === 'facAdmin'){
                if($type === 'operations'){
                    return $this->render('//returned-scripts/index',$returnData);
                }elseif($type === 'reports'){
                    return $this->render('//faculty-admin-reports/index',$returnData);
                }else{
                    throw new Exception('You must give the correct operation type for the faculty admin.');
                }
            }

            return $this->render('departmentCourses', $returnData);

        }catch (Exception | dbException $ex){
            if(YII_ENV_PROD) {
                if($ex instanceof dbException) $message = 'This request failed to process.';
                else $message = $ex->getMessage();
            }
            else $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            throw new ServerErrorHttpException($message, '500');
        }
    }
}