<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @date: 7/26/2024
 * @time: 3:30 PM
 */

/* @var yii\web\View $this */
/* @var app\models\MarksheetDef $model */
/* @var app\models\search\ReceivedMissingMarksSearch $searchModel */
/* @var yii\data\ActiveDataProvider $dataProvider */
/* @var string $title */
/**
 * @var app\models\filters\ReceivedMissingMarksFilter $filter
 */

/* @var string $panelHeading */

use app\models\EmpVerifyView;
use kartik\grid\GridView;
use yii\web\ServerErrorHttpException;

$this->title = $title;
$this->params['breadcrumbs'][] = [
    'label' => 'Received/missing marks report filters',
    'url' => ['/received-marks/filters']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
echo $this->render('../shared-reports/activeFilters', ['filter' => $filter]);
?>

<div class="allocated-courses-index">
    <div class="row">
        <div class="col-md-12 col-lg-12">
            <?php
            $gridId = 'received-missing-marks-report';
            try {
                echo GridView::widget([
                    'id' => $gridId,
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'headerRowOptions' => ['class' => 'kartik-sheet-style'],
                    'filterRowOptions' => ['class' => 'kartik-sheet-style'],
                    'pjax' => true,
                    'pjaxSettings' => [
                        'options' => [
                            'id' => $gridId . '-pjax'
                        ]
                    ],
                    'toolbar' => [
                        '{toggleData}'
                    ],
                    'panel' => [
                        'type' => GridView::TYPE_PRIMARY,
                        'heading' => '<h3 class="panel-title">' . $panelHeading . '</h3>',
                    ],
                    'persistResize' => false,
                    'toggleDataContainer' => ['class' => 'btn-group mr-2'],
                    'toggleDataOptions' => ['minCount' => 20],
                    'itemLabelSingle' => 'course',
                    'itemLabelPlural' => 'courses',
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'label' => 'DEGREE',
                            'value' => function ($model) {
                                return $model['semester']['degreeProgramme']['DEGREE_CODE'] . ' - ' .
                                    $model['semester']['degreeProgramme']['DEGREE_NAME'];
                            },
                            'group' => true,
                            'groupedRow' => true,
                            'groupOddCssClass' => 'kv-grouped-row',
                            'groupEvenCssClass' => 'kv-grouped-row',
                        ],
                        [
                            'attribute' => 'course.COURSE_CODE',
                            'label' => 'CODE',
                        ],
                        [
                            'attribute' => 'course.COURSE_NAME',
                            'label' => 'COURSE NAME',
                        ],
                        [
                            'attribute' => 'group.GROUP_NAME',
                            'label' => 'GROUP'
                        ],
                        [
                            'attribute' => 'semester.LEVEL_OF_STUDY',
                            'label' => 'LEVEL'
                        ],
                        [
                            'attribute' => 'PAYROLL_NO',
                            'label' => 'LECTURER',
                            'value' => function ($model) {
                                $lecturer = EmpVerifyView::find()
                                    ->select(['PAYROLL_NO', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE'])
                                    ->where(['PAYROLL_NO' => $model['PAYROLL_NO']])
                                    ->asArray()
                                    ->one();
                                if (empty($lecturer)) {
                                    return '--';
                                }
                                return $lecturer['EMP_TITLE'] . ' ' . $lecturer['OTHER_NAMES'];
                            }
                        ],
                        [
                            'attribute' => 'EXAM_DATE',
                            'label' => 'EXAM DATE',
                            'value' => function ($model) {
                                if (empty($model['EXAM_DATE'])) {
                                    return '--';
                                }
                                return $model['EXAM_DATE'];
                            }
                        ],
                        [
                            'attribute' => 'totalStudents',
                            'label' => 'TOTAL STUDENTS'
                        ],
                        [
                            'attribute' => 'received',
                            'label' => 'RECEIVED'
                        ],
                        [
                            'attribute' => 'missing',
                            'label' => 'MISSING'
                        ]
                    ]
                ]);
            } catch (Exception $ex) {
                $message = 'Failed to create grid for allocated assessments.';
                if (YII_ENV_DEV) {
                    $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
                }
                throw new ServerErrorHttpException($message, 500);
            }
            ?>
        </div>
    </div>
</div>
