<?php

namespace app\controllers;

use app\models\Semester;
use app\models\search\SemesterSearch;
use app\models\DegreeProgramme; // Added
use app\models\search\CourseAssignmentSearch;
use Exception;
use yii\helpers\ArrayHelper; // Added
use Yii; // Added
use yii\db\Expression;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\ServerErrorHttpException;

/**
 * SemesterController implements the CRUD actions for Semester model.
 */
class SemesterController extends BaseController
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    // /**
    //  * Lists all Semester models.
    //  *
    //  * @return string
    //  */
    // public function actionIndex()
    // {
    //     $searchModel = new SemesterSearch();
    //     $params = $this->request->queryParams;

    //     // Check if the search form has been submitted with any values
    //     $searchSubmitted = isset($params['SemesterSearch']) && (
    //         !empty($params['SemesterSearch']['ACADEMIC_YEAR']) &&
    //         !empty($params['SemesterSearch']['DEGREE_CODE'])
    //     );

    //     if ($searchSubmitted) {
    //         $dataProvider = $searchModel->search($params);
    //     } else {
    //         // No search performed, or search form cleared
    //         $query = Semester::find()->where('0=1');
    //         $dataProvider = new \yii\data\ActiveDataProvider([
    //             'query' => $query,
    //         ]);
    //     }

    //     $distinctDegreeCodes = DegreeProgramme::find()->select('DEGREE_CODE')->distinct()->column();

    //     return $this->render('index', [
    //         'searchModel' => $searchModel,
    //         'dataProvider' => $dataProvider,
    //         'facCode' => $this->facCode,
    //         'deptCode' => $this->deptCode,
    //         'distinctDegreeCodes' => $distinctDegreeCodes, // Added
    //         'searchPerformed' => $searchSubmitted,
    //     ]);
    // }

    /**
     * Get courses allocated to a lecturer
     * @return string|Response lecturer course allocations page
     * @throws ServerErrorHttpException
     */
    public function actionIndex()
    {
        $searchModel = new SemesterSearch();
        $params = $this->request->queryParams;
        $searchPerformed = false;

        // Check if the search form has been submitted with any values
        $searchSubmitted = isset($params['SemesterSearch']) && (
            !empty($params['SemesterSearch']['ACADEMIC_YEAR']) &&
            !empty($params['SemesterSearch']['DEGREE_CODE'])
        );

        if ($searchSubmitted) {
            $dataProvider = $searchModel->search($params);
            $searchPerformed = true;
        } else {
            // No search performed, or search form cleared
            $query = Semester::find()->where('0=1');
            $dataProvider = new \yii\data\ActiveDataProvider([
                'query' => $query,
            ]);
        }

        $distinctDegreeCodes = DegreeProgramme::find()->select('DEGREE_CODE')->distinct()->column();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'facCode' => $this->facCode,
            'deptCode' => $this->deptCode,
            'distinctDegreeCodes' => $distinctDegreeCodes, // Added
            'searchPerformed' => $searchPerformed,
        ]);
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
    /**
     * Displays a single Semester model.
     * @param string $SEMESTER_ID Semester ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($SEMESTER_ID)
    {
        return $this->render('view', [
            'model' => $this->findModel($SEMESTER_ID),
        ]);
    }

    /**
     * Creates a new Semester model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Semester();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'SEMESTER_ID' => $model->SEMESTER_ID]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Semester model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $SEMESTER_ID Semester ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($SEMESTER_ID)
    {
        $model = $this->findModel($SEMESTER_ID);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'SEMESTER_ID' => $model->SEMESTER_ID]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Semester model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $SEMESTER_ID Semester ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($SEMESTER_ID)
    {
        $this->findModel($SEMESTER_ID)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Semester model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $SEMESTER_ID Semester ID
     * @return Semester the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($SEMESTER_ID)
    {
        if (($model = Semester::findOne(['SEMESTER_ID' => $SEMESTER_ID])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionDegcode($ACADEMIC_YEAR)
    {
        $degCode = Semester::find()
            ->select([
                'MUTHONI.DEGREE_PROGRAMMES.DEGREE_CODE',
                'MUTHONI.DEGREE_PROGRAMMES.DEGREE_NAME',
            ])
            ->distinct()
            ->joinWith(['degreeProgramme'])
            ->where(['MUTHONI.SEMESTERS.ACADEMIC_YEAR' => $ACADEMIC_YEAR])->all();


        foreach ($degCode as $deg) {
            echo "<option value='" . $deg->DEGREE_CODE . "'>" . $deg->DEGREE_CODE . ' - ' . $deg->degreeProgramme->DEGREE_NAME . "</option>";
        }
    }
}
