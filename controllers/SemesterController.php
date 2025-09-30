<?php

namespace app\controllers;

use app\models\Semester;
use app\models\search\SemesterSearch;
use app\models\DegreeProgramme; // Added
use app\models\search\CourseAssignmentSearch;
use app\models\SemesterDescription;
use Exception;
use yii\helpers\ArrayHelper; // Added
use Yii; // Added
use yii\db\Expression;
use yii\db\Query;
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
        // Load params so validation errors can render in the form
        $searchModel->load($params);

        if (!empty(Yii::$app->request->get('SemesterSearch')['SEMESTER_CODE_DESC'])) {
            $desc = Yii::$app->request->get('SemesterSearch')['SEMESTER_CODE_DESC'];
            $parts = explode(' - ', $desc);

            $sd = SemesterDescription::findOne(['SEMESTER_DESC' => $parts[1]]);
            $searchModel->DESCRIPTION_CODE = $sd['DESCRIPTION_CODE'];
            $searchModel->SEMESTER_CODE = $parts[0];
        }
        // Gate heavy search until base filters from _search are provided
        $ss = $params['SemesterSearch'] ?? [];
        $baseFiltersProvided = !empty($ss['ACADEMIC_YEAR']) && !empty($ss['DEGREE_CODE']) && !empty($ss['SEMESTER_CODE']);

        if ($baseFiltersProvided) {
            $dataProvider = $searchModel->search($params);
            $searchPerformed = true;
        } else {
            $dataProvider = new \yii\data\ActiveDataProvider([
                'query' => Semester::find()->where('0=1'),
            ]);
            $searchPerformed = false;
        }
        // If user attempted a search but required base filters are missing,
        // validate to populate "cannot be blank" errors on the form
        if (isset($params['SemesterSearch']) && !$baseFiltersProvided) {
            $searchModel->validate();
        }
        $distinctDegreeCodes = DegreeProgramme::find()->select('DEGREE_CODE')->distinct()->column();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'facCode' => $this->facCode,
            'deptCode' => $this->deptCode,
            'distinctDegreeCodes' => $distinctDegreeCodes,
            'searchPerformed' => $searchPerformed,
        ]);
    }
    public function actionAcademicYear()
    {
        $searchModel = new SemesterSearch();
        $params = $this->request->queryParams;


        $dataProvider = $searchModel->year($params);

        return $this->render('academic-year', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
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

    public function actionSemcode($ACADEMIC_YEAR, $DEGREE_CODE)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // $semCode = Semester::find()
        //     ->select([
        //         'MUTHONI.SEMESTERS.SEMESTER_CODE',
        //         'MUTHONI.SEMESTER_DESCRIPTIONS.SEMESTER_DESC'
        //     ])
        //     ->innerJoinWith(['semesterDescription'])
        //     ->where([
        //         'MUTHONI.SEMESTERS.ACADEMIC_YEAR' => $ACADEMIC_YEAR,
        //         'MUTHONI.SEMESTERS.DEGREE_CODE' => $DEGREE_CODE,
        //     ])
        //     ->all();
        $semCode = (new \yii\db\Query())
            ->select([
                'MUTHONI.SEMESTERS.SEMESTER_CODE',
                'MUTHONI.SEMESTER_DESCRIPTIONS.SEMESTER_DESC',
                'MUTHONI.SEMESTER_DESCRIPTIONS.SEMESTER_DESC',
                'MUTHONI.SEMESTERS.SEMESTER_TYPE',
            ])
            ->distinct()
            ->from('MUTHONI.SEMESTERS')
            ->innerJoin(
                'MUTHONI.SEMESTER_DESCRIPTIONS',
                'MUTHONI.SEMESTER_DESCRIPTIONS.DESCRIPTION_CODE = MUTHONI.SEMESTERS.DESCRIPTION_CODE'
            )
            ->where([
                'MUTHONI.SEMESTERS.ACADEMIC_YEAR' => $ACADEMIC_YEAR,
                'MUTHONI.SEMESTERS.DEGREE_CODE' => $DEGREE_CODE,
            ])->orderBy([
                'MUTHONI.SEMESTERS.SEMESTER_CODE' => SORT_ASC,
                'MUTHONI.SEMESTER_DESCRIPTIONS.SEMESTER_DESC' => SORT_ASC,
            ])
            ->all();

        $levels = (new \yii\db\Query())
            ->select([
                'MUTHONI.LEVEL_OF_STUDY.LEVEL_OF_STUDY',
                'MUTHONI.LEVEL_OF_STUDY.NAME',
            ])
            ->distinct()
            ->from('MUTHONI.SEMESTERS')
            ->innerJoin(
                'MUTHONI.LEVEL_OF_STUDY',
                'MUTHONI.LEVEL_OF_STUDY.LEVEL_OF_STUDY = MUTHONI.SEMESTERS.LEVEL_OF_STUDY'
            )
            ->where([
                'MUTHONI.SEMESTERS.ACADEMIC_YEAR' => $ACADEMIC_YEAR,
                'MUTHONI.SEMESTERS.DEGREE_CODE' => $DEGREE_CODE,
            ])->orderBy([
                'MUTHONI.LEVEL_OF_STUDY.LEVEL_OF_STUDY' => SORT_ASC,
                'MUTHONI.LEVEL_OF_STUDY.NAME' => SORT_ASC,
            ])
            ->all();



        $semesterOptions = [];
        foreach ($semCode as $sem) {
            $semesterOptions[] = [
                'id'   => $sem['SEMESTER_CODE'],
                'text' => $sem['SEMESTER_CODE'] . ' - ' . $sem['SEMESTER_DESC'] . ' - ' . $sem['SEMESTER_TYPE'],
            ];
        }

        $levelOptions = [];
        foreach ($levels as $lvl) {
            $levelOptions[] = [
                'id'   => $lvl['LEVEL_OF_STUDY'],
                'text' => $lvl['NAME'],
            ];
        }


        return [
            'semesters' => $semesterOptions,
            'levels'    => $levelOptions,
        ];
    }
}
