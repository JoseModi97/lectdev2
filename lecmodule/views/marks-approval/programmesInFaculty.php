<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc Display programmes in a faculty
 */

/**
 * @var yii\web\View $this
 * @var app\models\DegreeProgramme $model
 * @var yii\data\ActiveDataProvider $programmesProvider
 * @var app\models\search\ProgrammesInAFacultySearch $searchModel
 * @var string $title
 * @var string $deptCode
 * @var string $facCode
 * @var string $level
 * @var string $panelHeading
 */

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;

// Build grid columns
$gridId = 'programmes-in-a-faculty';
$degreeCodeColumn = [
    'attribute' => 'DEGREE_CODE',
    'label' => 'CODE'
];
$degreeNameColumn = [
    'attribute' => 'DEGREE_NAME',
    'label' => 'NAME'
];
$actionColumn = [
    'class' => 'kartik\grid\ActionColumn',
    'template' => '{filter-courses}',
    'contentOptions' => ['style'=>'white-space:nowrap;','class'=>'kartik-sheet-style kv-align-middle'],
    'buttons' => [
        'filter-courses' => function($url, $model) use($level) {
            return Html::a('<i class="fa fa-filter" aria-hidden="true"></i> courses',
                Url::to(['/marks-approval/filters', 'approvalLevel' => $level, 'degreeCode' => $model['DEGREE_CODE']]),
                [
                    'title' => 'Filter courses',
                    'class' => 'btn btn-xs'
                ]
            );
        }
    ]
];

// Render programmes table grid
try {
    echo GridView::widget([
        'id' => $gridId,
        'dataProvider' => $programmesProvider,
        'filterModel' => $searchModel,
        'headerRowOptions' => [
            'class' => 'kartik-sheet-style'
        ],
        'filterRowOptions' => [
            'class' => 'kartik-sheet-style'
        ],
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
        'toggleDataContainer' => [
            'class' => 'btn-group mr-2'
        ],
        'toggleDataOptions' => [
            'minCount' => 50
        ],
        'itemLabelSingle' => 'course',
        'itemLabelPlural' => 'courses',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            $degreeCodeColumn,
            $degreeNameColumn,
            $actionColumn
        ]
    ]);
} catch (Exception $ex) {
    $message = 'An error occurred while trying to display the programmes in the faculty';
    if (YII_ENV_DEV) {
        $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}


