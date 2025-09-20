<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var app\models\Marksheet $model
 * @var app\models\search\MissingMarksSearch $searchModel
 * @var yii\data\ActiveDataProvider $missingMarksProvider
 * @var string $title
 * @var string $panelHeading
 * @var string[] $reportDetails
 */

use app\models\UonStudent;
use kartik\grid\GridView;
use app\components\GridExport;
use yii\web\ServerErrorHttpException;

$this->title = $title;
$this->params['breadcrumbs'][] =[
    'label' => 'Assessments',
    'url' => ['/shared-reports/assessments', 'marksheetId' => $reportDetails['marksheetId']]
];
$this->params['breadcrumbs'][] = $reportDetails['courseName'] . ' (' . $reportDetails['courseCode'] . ')';
$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
    ['class' => 'kartik\grid\SerialColumn'],
    [
        'attribute' => 'REGISTRATION_NUMBER',
        'label' => 'REGISTRATION NUMBER',
        'width' => '15%',
    ],
    [
        'attribute' => 'student.SURNAME',
        'label' => 'SURNAME',
        'width' => '20%',
    ],
    [
        'attribute' => 'student.OTHER_NAMES',
        'label' => 'OTHER NAMES',
        'width' => '25%',
    ],
    [
        'label' => 'EMAIL',
        'width' => '25%',
        'value' => function($model){
            return UonStudent::validateEmail($model['student']['EMAIL']);
        }
    ],
    [
        'label' => 'TELEPHONE',
        'width' => '15%',
        'value' => function($model){
            return UonStudent::formatTelephone($model['student']['TELEPHONE']);
        }
    ]
];

$title = $reportDetails['courseCode'] . ' ' . $reportDetails['assessmentName'] . ' MISSING MARKS';

$fileName = $reportDetails['academicYear'] . '_' .$reportDetails['degreeCode'] . '_' . $reportDetails['level'] .
    '_sem_' . $reportDetails['semesterFullName'] . '_' . $reportDetails['group'];
$fileName .= '_' . $reportDetails['courseCode'] . '_' . $reportDetails['assessmentName'] . '_missing_marks_report';
$fileName = strtolower($fileName);
$fileName = str_replace(' ', '_', $fileName);

$contentBefore = '<p style="color:#333333; font-weight: bold;">ACADEMIC YEAR: ' . $reportDetails['academicYear'] . '</p>';
$contentBefore .= '<p style="color:#333333; font-weight: bold;">PROGRAMME: ' . $reportDetails['degreeName'] . ' (' .
    $reportDetails['degreeCode'] . ') </p>';
$contentBefore .= '<p style="color:#333333; font-weight: bold;">LEVEL OF STUDY: ' . strtoupper($reportDetails['level']) . '</p>';
$contentBefore .= '<p style="color:#333333; font-weight: bold;">SEMESTER: ' . $reportDetails['semesterFullName'] . '</p>';
$contentBefore .= '<p style="color:#333333; font-weight: bold;">GROUP: ' . strtoupper($reportDetails['group']) . '</p>';

$contentAfter = '';
try{
    echo GridView::widget([
        'id' => 'missingMarksGridView',
        'dataProvider' => $missingMarksProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumns,
        'headerRowOptions' => [
            'class' => 'kartik-sheet-style'
        ],
        'filterRowOptions' => [
            'class' => 'kartik-sheet-style'
        ],
        'pjax' => true,
        'toolbar' =>  [
            '{export}',
            '{toggleData}',
        ],
        'toggleDataContainer' => [
            'class' => 'btn-group mr-2'
        ],
        'export' => [
            'fontAwesome' => false,
            'label' => 'Download report'
        ],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<h3 class="panel-title">' . $panelHeading . '</h3>',
        ],
        'persistResize' => false,
        'toggleDataOptions' => [
            'minCount' => 50
        ],
        'exportConfig' => [
            GridView::PDF => GridExport::exportPdf([
                'filename' => $fileName,
                'title' => $title,
                'subject' => 'missing marks',
                'keywords' => 'missing marks',
                'contentBefore'=> $contentBefore,
                'contentAfter'=> $contentAfter,
                'centerContent' => $title,
            ])
        ],
        'itemLabelSingle' => 'student',
        'itemLabelPlural' => 'students',
    ]);
} catch (Exception $ex) {
    $message = 'An error occurred while trying to display the missing marks report.';
    if (YII_ENV_DEV) {
        $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}


