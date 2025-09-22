<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var app\models\MarksheetDef $model
 * @var yii\data\ActiveDataProvider $timetablesProvider
 * @var string $title
 * @var string $academicYear
 * @var string $level
 */

use app\components\GridExport;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

$this->title = $title;
$academicYearFilterUrl = '';
$programmesTimetablesUrl = '';
if($level === 'dean'){
    $academicYearFilterUrl = '/dean-reports/set-academic-year';
    $programmesTimetablesUrl = '/dean-reports/programmes-timetables';
}elseif ($level === 'facAdmin'){
    $academicYearFilterUrl = '/faculty-admin-reports/set-academic-year';
    $programmesTimetablesUrl = '/faculty-admin-reports/programmes-timetables';
}

if($level === 'sysAdmin') {
    $academicYearFilterUrl = '/system-admin-reports/set-academic-year';
    $programmesTimetablesUrl = '/system-admin-reports/programmes-timetables';
    $this->params['breadcrumbs'][] = [
        'label' => 'FACULTY TIMETABLES',
        'url' => [
            '/system-admin-reports/faculty-timetables'
        ]
    ];
}

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="created-timetables">
    <?php
    echo $this->render('_academicYearFilter', ['reportType' => 'createdTimetables', 'url' => $academicYearFilterUrl]);

    $gridColumns = [
        [
            'class' => 'kartik\grid\SerialColumn',
            'width' => '5%'
        ],
        [
            'label' => 'CODE',
            'width' => '5%',
            'value' => function($model){
                return $model['deptCode'];
            }
        ],
        [
            'label' => 'NAME',
            'width' => '75%',
            'value' => function($model){
                return $model['deptName'];
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
            'template' => '{programmes-timetables}',
            'contentOptions' => ['style'=>'white-space:nowrap;','class'=>'kartik-sheet-style kv-align-middle'],
            'buttons' => [
                'programmes-timetables' => function ($url, $model) use ($academicYear, $programmesTimetablesUrl) {
                    return Html::a('<i class="fas fa-file"></i> Programme timetables',
                        Url::to([$programmesTimetablesUrl, 'deptCode' => $model['deptCode'], 'academicYear' => $academicYear]),
                        ['title' => 'Programmes timetables report', 'class' => 'btn btn-xs dept-timetables']
                    );
                }
            ],
            'hAlign' => 'center',
        ]
    ];

    $gridId = 'created-timetables-departments';
    $title = 'Timetables for the academic year ' . $academicYear;
    $fileName = 'departments_timetables';
    try {
        echo GridView::widget([
            'id' => $gridId,
            'dataProvider' => $timetablesProvider,
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
            'toggleDataOptions' => ['minCount' => 50],
            'exportConfig' => [
                GridView::EXCEL => GridExport::exportExcel([
                    'filename' => $fileName,
                    'worksheet' => 'departments'
                ]),
                GridView::PDF => GridExport::exportPdf([
                    'filename' => $fileName,
                    'title' => $title,
                    'subject' => 'departments timetables',
                    'keywords' => 'departments timetables',
                    'contentBefore' => '',
                    'contentAfter' => '',
                    'centerContent' => $title
                ]),
            ],
            'itemLabelSingle' => 'department',
            'itemLabelPlural' => 'departments',
        ]);
    } catch (Exception $ex) {
        $message = 'There were errors while trying to display the departments timetables.';
        if(YII_ENV_DEV){
            $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
        }
        throw new ServerErrorHttpException($message, 500);
    }
    ?>
</div>
