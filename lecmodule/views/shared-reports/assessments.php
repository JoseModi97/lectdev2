<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var app\models\CourseWorkAssessment $model
 * @var app\models\search\MissingMarksAssessmentsSearch $searchModel
 * @var yii\data\ActiveDataProvider $assessmentsDataProvider
 * @var string $title
 * @var string $panelHeading
 */

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

$this->title = $title;
$this->params['breadcrumbs'][] = [
    'label' => 'Grade analysis',
    'url' => ['/shared-reports/course-analysis']
];
$this->params['breadcrumbs'][] = $this->title;

// Build grid columns
$gridId = 'courses-assessments';

$assessmentNameColumn = [
    'attribute' => 'assessmentType.ASSESSMENT_NAME',
    'label' => 'ASSESSMENT NAME',
    'value' => function($model){
        $assessmentName = $model['assessmentType']['ASSESSMENT_NAME'];
        if(strpos($assessmentName, 'EXAM_COMPONENT') !== false){
            return str_replace('EXAM_COMPONENT','', $assessmentName);
        }else{
            return $assessmentName;
        }
    }
];
$resultDueDateColumn = [
    'label' => 'RESULT DUE DATE',
    'value' => function($model){
        $duedate = $model['RESULT_DUE_DATE'];
        return is_null( $duedate) ? 'NOT SET' : $duedate;
    }
];
$actionColumn = [
    'class' => 'kartik\grid\ActionColumn',
    'header' => 'ACTIONS',
    'template' => '{missing-marks}',
    'contentOptions' => ['style'=>'white-space:nowrap;','class'=>'kartik-sheet-style kv-align-middle'],
    'buttons' => [
        'missing-marks' => function($url, $model){
            return Html::a('<i class="fas fa-eye"></i> Missing marks',
                Url::to(['/shared-reports/missing-marks', 'assessmentId' => $model['ASSESSMENT_ID']]),
                [
                    'title' => 'View missing marks report',
                    'class' => 'btn btn-xs btn-spacer'
                ]
            );
        }
    ]
];

// Render programmes table grid
try {
    echo GridView::widget([
        'id' => $gridId,
        'dataProvider' => $assessmentsDataProvider,
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
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<h3 class="panel-title">' . $panelHeading . '</h3>',
        ],
        'persistResize' => false,
        'itemLabelSingle' => 'assessment',
        'itemLabelPlural' => 'assessments',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            $assessmentNameColumn,
            $resultDueDateColumn,
            $actionColumn
        ]
    ]);
} catch (Exception $ex) {
    $message = 'An error occurred while trying to display course assessments.';
    if (YII_ENV_DEV) {
        $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}
