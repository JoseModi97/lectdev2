<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 28-06-2021 10:07:37 
 * @modify date 28-06-2021 10:07:37 
 * @desc [description]
 */

namespace app\controllers;

use app\components\SmisHelper;
use app\models\search\CourseAssignmentSearch;
use Exception;
use Yii;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class AllocatedCoursesController extends BaseController
{
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
                ]
            ]
        ];
    }

    /**
     * Get courses allocated to a lecturer
     * @return string|Response lecturer course allocations page
     * @throws ServerErrorHttpException
     */
    public function actionIndex()
    {
        try{
            $courseAssignmentSearch = new CourseAssignmentSearch();

            // Non-supplementary courses
            $nonSuppCoursesProvider = $courseAssignmentSearch->search(Yii::$app->request->queryParams, [
                'payrollNo' => $this->payrollNo,
                'academicYear' => $this->academicYear,
                'semesterType' => 'other'
            ]);

            // Supplementary courses
            $suppCoursesProvider = $courseAssignmentSearch->search(Yii::$app->request->queryParams, [
                'payrollNo' => $this->payrollNo,
                'academicYear' => $this->academicYear,
                'semesterType' => 'supplementary'
            ]);

            if(empty(Yii::$app->request->queryParams) && empty($nonSuppCoursesProvider->getModels())){
                $this->setFlash(
                    'danger',
                    'Allocated courses',
                    'You don\'t have any courses in the system for this academic year.
                        Kindly liase with your HOD to allocate the same to you'
                );
                return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
            }

            // All courses
            $allCoursesProvider = $courseAssignmentSearch->search(Yii::$app->request->queryParams,[
                'payrollNo' => $this->payrollNo,
                'academicYear' => null,
                'semesterType' => 'all'
            ]);

            return $this->render('index', [
                'title' => 'My course allocations',
                'courseAssignmentSearch' => $courseAssignmentSearch,
                'nonSuppCoursesProvider' => $nonSuppCoursesProvider,
                'suppCoursesProvider' => $suppCoursesProvider,
                'allCoursesProvider' => $allCoursesProvider,
                'academicYear' => $this->academicYear,
                'facCode' => $this->facCode
            ]);
        }
        catch(Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }
}