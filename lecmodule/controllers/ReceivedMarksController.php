<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @date: 7/30/2024
 * @time: 2:44 PM
 */

namespace app\controllers;

use app\components\SmisHelper;
use app\models\filters\ReceivedMissingMarksFilter;
use app\models\Marksheet;
use app\models\search\ReceivedMissingMarksSearch;
use app\models\TempMarksheet;
use Exception;
use Yii;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

class ReceivedMarksController extends BaseController
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

    /**
     * @return string
     */
    public function actionFilters(): string
    {
        $filter = new ReceivedMissingMarksFilter();

        return $this->render('receivedMarksFilters', [
            'title' => 'Received/Missing marks report filters',
            'filter' => $filter,
        ]);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function actionCourses(): string
    {
        // Save course filters in the session for retrieval on page redirects
        $session = Yii::$app->session;
        if (!empty(Yii::$app->request->get()['ReceivedMarksFilter'])) {
            $session['ReceivedMarksFilter'] = Yii::$app->request->get();
        }

        $filters = $session->get('ReceivedMarksFilter')['ReceivedMarksFilter'];

        $filter = new ReceivedMissingMarksFilter();
        $filter->academicYear = $filters['academicYear'];
        $filter->degreeCode = $filters['degreeCode'];
        $filter->levelOfStudy = $filters['levelOfStudy'];
        $filter->group = $filters['group'];
        $filter->semester = $filters['semester'];

        $searchModel = new ReceivedMissingMarksSearch();
        $dataProvider = $searchModel->search([
            'queryParams' => Yii::$app->request->queryParams,
            'filter' => $filter
        ]);

        $models = [];
        foreach ($dataProvider->getModels() as $model) {
            $totalStudents = (int)Marksheet::find()->select(['REGISTRATION_NUMBER'])
                ->where(['MRKSHEET_ID' => $model['MRKSHEET_ID']])->count();

            $received = 0;
            $missing = 0;
            if ($totalStudents > 0) {
                $received = (int)TempMarksheet::find()->select(['REGISTRATION_NUMBER'])
                    ->where([
                        'MRKSHEET_ID' => $model['MRKSHEET_ID'],
                        'MARKS_COMPLETE' => 1
                    ])->count();

                $missing = $totalStudents - $received;
            }

            $model['totalStudents'] = $totalStudents;
            $model['received'] = $received;
            $model['missing'] = $missing;
            $models[] = $model;
        }

        $dataProvider->setModels($models);

        return $this->render('index', [
            'title' => 'Received/Missing Marks',
            'panelHeading' => 'Received/Missing Marks',
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'filter' => $filter
        ]);
    }
}