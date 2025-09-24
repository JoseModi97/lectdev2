<?php
/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 18-11-2021
 * @desc Display created course works in faculties
 */

/**
 * @var yii\web\View $this
 * @var app\models\CourseWorkAssessment $model
 * @var yii\data\ActiveDataProvider $courseWorkProvider
 * @var string $title
 * @var string $academicYear
 */

use app\components\GridExport;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="course-work-definition-in-faculties">
    <?php
    echo $this->render('_academicYearFilter', ['reportType' => 'courseWorkDefinition']);

    $gridColumns = [
        [
            'class' => 'kartik\grid\SerialColumn',
            'width' => '5%'
        ],
        [
            'label' => 'CODE',
            'width' => '5%',
            'value' => function($model){
                return $model['facCode'];
            }
        ],
        [
            'label' => 'NAME',
            'width' => '75%',
            'value' => function($model){
                return $model['facName'];
            }
        ],
        [
            'label' => 'COURSE WORK DEFINED',
            'width' => '15%',
            'value' => function($model){
                return $model['courseworkCount'];
            }
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'template' => '{dept-cw}',
            'contentOptions' => ['style'=>'white-space:nowrap;','class'=>'kartik-sheet-style kv-align-middle'],
            'buttons' => [
                'dept-cw' => function ($url, $model) use ($academicYear) {
                    return Html::a('<i class="fas fa-file"></i> Course work defined in department',
                        Url::to([
                            '/system-admin-reports/course-work-definition-in-departments',
                            'facCode' => $model['facCode'],
                            'academicYear' => $academicYear
                        ]),
                        [
                            'title' => 'Course work definition in departments report',
                            'class' => 'btn btn-xs dept-cw'
                        ]
                    );
                }
            ],
            'hAlign' => 'center',
        ]
    ];

    $gridId = 'cw-definition-in-faculties';
    $title = 'Course work definitions for the academic year ' . $academicYear;
    $fileName = 'course_work_definition_in_faculties';
    try {
        echo GridView::widget([
            'id' => $gridId,
            'dataProvider' => $courseWorkProvider,
            'columns' => $gridColumns,
            'headerRowOptions' => ['class' => 'kartik-sheet-style'],
            'filterRowOptions' => ['class' => 'kartik-sheet-style'],
            'pjax' => true,
            'pjaxSettings' => [
                'options' => [
                    'id' => $gridId . '-pjax'
                ]
            ],
            'toolbar' => [
                '{export}',
                '{toggleData}'
            ],
            'panel' => [
                'type' => GridView::TYPE_PRIMARY,
                'heading' => '<h3 class="panel-title">' . $title . '</h3>',
            ],
            'toggleDataContainer' => ['class' => 'btn-group mr-2'],
            'toggleDataOptions' => ['minCount' => 20],
            'exportConfig' => [
                GridView::EXCEL => GridExport::exportExcel([
                    'filename' => $fileName,
                    'worksheet' => 'faculties'
                ]),
                GridView::PDF => GridExport::exportPdf([
                    'filename' => $fileName,
                    'title' => $title,
                    'subject' => 'faculties',
                    'keywords' => 'faculties',
                    'contentBefore' => '',
                    'contentAfter' => '',
                    'centerContent' => $title,
                ]),
            ],
            'itemLabelSingle' => 'faculty',
            'itemLabelPlural' => 'faculties',
        ]);
    } catch (Exception $ex) {
        $message = 'An error occurred while creating the table grid.';
        if(YII_ENV_DEV){
            $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
        }
        throw new ServerErrorHttpException($message, 500);
    }
    ?>
</div>


