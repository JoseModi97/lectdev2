<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @date: 7/26/2024
 * @time: 10:36 AM
 */

namespace app\controllers;

use app\components\SmisHelper;
use app\models\search\MarksSubmissionSearch;
use app\models\StudentCoursework;
use Yii;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

class SubmissionStatusController extends BaseController
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
                    ]
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
        SmisHelper::allowAccess(['LEC_SMIS_DEAN', 'LEC_SMIS_HOD', 'LEC_SMIS_LECTURER']);
    }

    public function actionIndex(): string
    {
        $searchModel = new MarksSubmissionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, [
            'payrollNo' => $this->payrollNo,
            'academicYear' => $this->academicYear,
        ]);

        $models = [];
        foreach ($dataProvider->getModels() as $model) {
            $assessmentId = $model['ASSESSMENT_ID'];
            $model['lecPending'] = StudentCoursework::find()->where(['ASSESSMENT_ID' => $assessmentId, 'LECTURER_APPROVAL_STATUS' => 'PENDING'])->count();
            $model['lecApproved'] = StudentCoursework::find()->where(['ASSESSMENT_ID' => $assessmentId, 'LECTURER_APPROVAL_STATUS' => 'APPROVED'])->count();
            $model['hodPending'] = StudentCoursework::find()->where(['ASSESSMENT_ID' => $assessmentId, 'HOD_APPROVAL_STATUS' => 'PENDING'])->count();
            $model['hodApproved'] = StudentCoursework::find()->where(['ASSESSMENT_ID' => $assessmentId, 'HOD_APPROVAL_STATUS' => 'APPROVED'])->count();
            $model['deanPending'] = StudentCoursework::find()->where(['ASSESSMENT_ID' => $assessmentId, 'DEAN_APPROVAL_STATUS' => 'PENDING'])->count();
            $model['deanApproved'] = StudentCoursework::find()->where(['ASSESSMENT_ID' => $assessmentId, 'DEAN_APPROVAL_STATUS' => 'APPROVED'])->count();
//            $model['hodPending'] = '--';
//            $model['hodApproved'] = '--';
//            $model['deanPending'] = '--';
//            $model['deanApproved'] = '--';
            $models[] = $model;
        }

        $dataProvider->setModels($models);

        return $this->render('index', [
            'title' => 'Marks submission status',
            'panelHeading' => 'Submission and approval status of assessment marks',
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel
        ]);
    }
}