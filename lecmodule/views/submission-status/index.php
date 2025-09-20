<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @date: 7/26/2024
 * @time: 3:30 PM
 */

/* @var yii\web\View $this */
/* @var app\models\CourseWorkAssessment $model */
/* @var app\models\search\MarksSubmissionSearch $searchModel */
/* @var yii\data\ActiveDataProvider $dataProvider */
/* @var string $title */

/* @var string $panelHeading */

use kartik\grid\GridView;
use yii\web\ServerErrorHttpException;

$this->title = $title;

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="allocated-courses-index">
    <div class="row">
        <div class="col-md-12 col-lg-12">
            <?php
            $gridId = 'submission-report';
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
                    'itemLabelSingle' => 'assessment',
                    'itemLabelPlural' => 'assessments',
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'label' => 'DEGREE',
                            'value' => function($model){
                                $academicYear = $model['marksheetDef']['semester']['ACADEMIC_YEAR'];
                                $degreeName = $model['marksheetDef']['semester']['degreeProgramme']['DEGREE_NAME'];
                                return $degreeName. ' ('.$academicYear.')';
                            },
                            'group' => true,
                            'groupedRow' => true,
                            'groupOddCssClass' => 'kv-grouped-row',
                            'groupEvenCssClass' => 'kv-grouped-row',
                        ],
                        [
                            'attribute' => 'marksheetDef.course.COURSE_CODE',
                            'label' => 'CODE',
                            'group' => true,
                        ],
                        [
                            'attribute' => 'marksheetDef.course.COURSE_NAME',
                            'label' => 'COURSE NAME',
                            'group' => true,
                        ],
                        [
                            'attribute' => 'assessmentType.ASSESSMENT_NAME',
                            'label' => 'ASSESSMENT NAME'
                        ],
                        [
                            'attribute' => 'marksheetDef.group.GROUP_NAME',
                            'label' => 'GROUP'
                        ],
                        [
                            'label' => 'LEVEL',
                            'value' => function($model){
                                return $model['marksheetDef']['semester']['LEVEL_OF_STUDY'];
                            }
                        ],
                        [
                            'label' => 'SEMESTER',
                            'width' => '20%',
                            'value' => function($model){
                                $description = $model['marksheetDef']['semester']['semesterDescription']['SEMESTER_DESC'];
                                $code = $model['marksheetDef']['semester']['SEMESTER_CODE'];
                                return $code . ' (' . $description .')';
                            }
                        ],
                        [
                            'attribute' => 'lecPending',
                            'label' => 'PENDING AT LEC'
                        ],
                        [
                            'attribute' => 'lecApproved',
                            'label' => 'SUBMITTED BY LEC'
                        ],
                        [
                            'attribute' => 'hodPending',
                            'label' => 'PENDING AT HOD'
                        ],
                        [
                            'attribute' => 'hodApproved',
                            'label' => 'APPROVED BY HOD'
                        ],
                        [
                            'attribute' => 'deanPending',
                            'label' => 'PENDING AT DEAN'
                        ],
                        [
                            'attribute' => 'deanApproved',
                            'label' => 'APPROVED BY DEAN'
                        ],
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
