<?php

/**
 * @author Jack Jmm
 * @email jackmutiso37@gmail.com
 * @create date 12-9-2025 20:50:22 
 * @desc 
 */

namespace app\controllers;

use Yii;
use Exception;
use yii\web\Response;
use app\components\SmisHelper;
use yii\filters\AccessControl;
use app\models\CourseAssignment;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;
use app\models\search\CourseAssignmentSearch;

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

            //get user programmes
            $programmes = $this->getUserProgrammes($this->payrollNo, $nonSuppCoursesProvider);


            return $this->render('index', [
                'title' => 'My course allocations',
                'courseAssignmentSearch' => $courseAssignmentSearch,
                'nonSuppCoursesProvider' => $nonSuppCoursesProvider,
                // 'suppCoursesProvider' => $suppCoursesProvider,
                // 'allCoursesProvider' => $allCoursesProvider,
                'academicYear' => $this->academicYear,
                'facCode' => $this->facCode,
                'programmes' => $programmes
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

    /**
     * Get user programmes
     */
    public function getUserProgrammes($payrollNo, $dataProvider = null)
    {
        $programmes = [];

        if ($dataProvider) {
            $models = $dataProvider->getModels();
            $uniqueProgrammes = [];

            foreach ($models as $model) {
                $degreeCode = $model->marksheetDef->semester->degreeProgramme->DEGREE_CODE;
                $degreeName = $model->marksheetDef->semester->degreeProgramme->DEGREE_NAME;

                if (!isset($uniqueProgrammes[$degreeCode])) {
                    $uniqueProgrammes[$degreeCode] = [
                        'DEGREE_CODE' => $degreeCode,
                        'DEGREE_NAME' => $degreeName
                    ];
                }
            }

            $programmes = array_values($uniqueProgrammes);
        } else {
            // $programmes = CourseAssignment::find()
            //     ->select(['DISTINCT DEG.DEGREE_CODE', 'DEG.DEGREE_NAME'])
            //     ->alias('CA')
            //     ->innerJoin(['MD' => 'marksheet_def'], 'CA.MRKSHEET_ID = MD.MRKSHEET_ID')
            //     ->innerJoin(['SM' => 'semester'], 'MD.SEMESTER_ID = SM.SEMESTER_ID')
            //     ->innerJoin(['DEG' => 'degree_programme'], 'SM.DEGREE_CODE = DEG.DEGREE_CODE')
            //     ->where(['CA.PAYROLL_NO' => $payrollNo])
            //     ->orderBy(['DEG.DEGREE_NAME' => SORT_ASC])
            //     ->asArray()
            //     ->all();

            $programmes = [];
        }
        return $programmes;
    }
}