<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 13-10-2021 15:07:28 
 * @modify date 13-10-2021 15:07:28 
 * @desc Display created timetables for faculties
 */

 /**
 * @var $this yii\web\View 
 * @var $model app\models\MarksheetDef
 * @var $timetablesProvider yii\data\ActiveDataProvider
 * @var $title string
 * @var $academicYear string
 */

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;

use app\components\GridExport;

$this->title = $title;

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="created-timetables">
    <?php
    echo $this->render('_academicYearFilter', ['reportType' => 'createdTimetables']);

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
                'label' => 'TIMETABLES',
                'width' => '15%',
                'value' => function($model){
                    return $model['timetableCount'];
                }
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{dept-timetables}',
                'contentOptions' => ['style'=>'white-space:nowrap;','class'=>'kartik-sheet-style kv-align-middle'],
                'buttons' => [
                    'dept-timetables' => function ($url, $model) use ($academicYear) {
                        return Html::a('<i class="fas fa-file"></i> department timetables',
                            Url::to([
                                '/system-admin-reports/department-timetables', 
                                'facCode' => $model['facCode'],
                                'academicYear' => $academicYear
                            ]),
                            [
                                'title' => 'Department timetables report',
                                'class' => 'btn btn-xs dept-timetables'
                            ]
                        );
                    }
                ],
                'hAlign' => 'center',
            ]
        ];

        $gridId = 'created-timetables-faculties';
        $title = 'Timetables for the academic year ' . $academicYear;
        $fileName = 'faculties_timetables';
        echo GridView::widget([
            'id' => $gridId,
            'dataProvider' => $timetablesProvider,
            'columns' => $gridColumns,
            'headerRowOptions' => ['class' => 'kartik-sheet-style'],
            'filterRowOptions' => ['class' => 'kartik-sheet-style'],
            'pjax' => true,
            'pjaxSettings'=>[
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
                'heading'=>'<h3 class="panel-title">'.$title.'</h3>',
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
                    'subject' => 'faculties timetables',
                    'keywords' => 'faculties timetables',
                    'contentBefore'=> '',
                    'contentAfter'=> '',
                    'centerContent' => $title,
                ]),
            ],
            'itemLabelSingle' => 'faculty',
            'itemLabelPlural' => 'faculties',
        ]);
    ?>
</div>