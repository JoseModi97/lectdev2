<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var app\models\CourseWorkAssessment $model
 * @var yii\data\ActiveDataProvider $courseWorkProvider
 * @var string $title
 * @var string $academicYear
 * @var string $facultyName
 * @var string $level
 */

use app\components\GridExport;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

$this->title = $title;
$academicYearFilterUrl = '';
$programmeCwDefinitionUrl = '';
if($level === 'dean'){
    $academicYearFilterUrl = '/dean-reports/set-academic-year';
    $programmeCwDefinitionUrl = '/dean-reports/programme-course-work-definition';
}elseif ($level === 'facAdmin'){
    $academicYearFilterUrl = '/faculty-admin-reports/set-academic-year';
    $programmeCwDefinitionUrl = '/faculty-admin-reports/programme-course-work-definition';
}

if($level === 'sysAdmin'){
    $academicYearFilterUrl = '/system-admin-reports/set-academic-year';
    $programmeCwDefinitionUrl = '/system-admin-reports/programme-course-work-definition';

    $this->params['breadcrumbs'][] = [
        'label' => 'COURSE WORK DEFINITIONS IN FACULTIES',
        'url' => [
            '/system-admin-reports/course-work-definition-in-faculties',
            'facCode' => ['facCode'],
            'academicYear' => $academicYear
        ]
    ];
}

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="course-work-definition-in-departments">
    <h6> <?= $facultyName; ?></h6>
    <?php
    echo $this->render('_academicYearFilter', ['reportType' => 'courseWorkDefinition', 'url' => $academicYearFilterUrl]);

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
            'label' => 'COURSE WORK DEFINED',
            'width' => '15%',
            'value' => function($model){
                return $model['courseworkCount'];
            }
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'template' => '{programmes-course-works}',
            'contentOptions' => ['style'=>'white-space:nowrap;','class'=>'kartik-sheet-style kv-align-middle'],
            'buttons' => [
                'programmes-course-works' => function ($url, $model) use ($academicYear, $programmeCwDefinitionUrl) {
                    return Html::a('<i class="fas fa-file"></i> Programme course work definition',
                        Url::to([$programmeCwDefinitionUrl, 'deptCode' => $model['deptCode'],
                            'academicYear' => $academicYear]),
                        [
                            'title' => 'Programmes course work definition report',
                            'class' => 'btn btn-xs programme-course-work'
                        ]
                    );
                }
            ],
            'hAlign' => 'center',
        ]
    ];

    $gridId = 'cw-definition-in-departments';
    $title = 'Course work definitions for the academic year ' . $academicYear;
    $fileName = 'course_work_definition_in_departments';
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
                    'worksheet' => 'departments'
                ]),
                GridView::PDF => GridExport::exportPdf([
                    'filename' => $fileName,
                    'title' => $title,
                    'subject' => 'departments',
                    'keywords' => 'departments',
                    'contentBefore' => '',
                    'contentAfter' => '',
                    'centerContent' => $title,
                ]),
            ],
            'itemLabelSingle' => 'department',
            'itemLabelPlural' => 'departments',
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
