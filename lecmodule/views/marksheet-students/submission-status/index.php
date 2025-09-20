<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @date: 7/26/2024
 * @time: 3:30 PM
 */

/* @var yii\web\View $this */
/* @var app\models\CourseAssignment $model */
/* @var app\models\search\CourseAssignmentSearch $courseAssignmentSearch */
/* @var yii\data\ActiveDataProvider $nonSuppCoursesProvider */
/* @var yii\data\ActiveDataProvider $suppCoursesProvider */
/* @var yii\data\ActiveDataProvider $allCoursesProvider */
/* @var string $title */
/* @var string $academicYear */
/* @var string $facCode */

use kartik\grid\GridView;
use yii\web\ServerErrorHttpException;
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
                        'heading' => '<h3 class="panel-title"> <?= $panelHeading?> </h3>',
                    ],
                    'persistResize' => false,
                    'toggleDataContainer' => ['class' => 'btn-group mr-2'],
                    'toggleDataOptions' => ['minCount' => 20],
                    'itemLabelSingle' => 'course',
                    'itemLabelPlural' => 'courses',
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'MARKSHEET_ID',
                            'label' => 'MARKSHEET'
                        ],
                        [
                            'attribute' => 'assessmentType.ASSESSMENT_NAME',
                            'label' => 'NAME'
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
                        ]
                    ]
                ]);
            } catch (Exception $ex) {
                $message = 'Failed to create grid for allocated courses.';
                if(YII_ENV_DEV){
                    $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
                }
                throw new ServerErrorHttpException($message, 500);
            }
            ?>
        </div>
    </div>
</div>
