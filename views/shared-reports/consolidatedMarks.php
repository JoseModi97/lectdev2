<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var app\models\TempMarksheet $model
 * @var app\models\search\MarksPreviewSearch $marksPreviewSearch
 * @var yii\data\ActiveDataProvider $marksPreviewProvider
 * @var string $title
 * @var string $panelHeading
 * @var string[] $reportDetails
 */

use app\components\GridExport;
use kartik\grid\GridView;
use yii\web\ServerErrorHttpException;

$this->title = $title;
$this->params['breadcrumbs'][] = [
    'label' => 'Grade analysis',
    'url' => ['/shared-reports/course-analysis']
];
$this->params['breadcrumbs'][] = $this->title;

$regNumColumn = [
    'attribute' => 'REGISTRATION_NUMBER',
    'label' => 'REGISTRATION NUMBER',
    'hAlign' => 'left',
    'width' => '15%'
];
$surnameColumn = [
    'attribute' => 'student.SURNAME',
    'label' => 'SURNAME',
    'hAlign' => 'left',
    'width' => '15%',
    'value' => function($model){
        return $model['student']['SURNAME'];
    }
];
$otherNamesColumn =[
    'attribute' => 'student.OTHER_NAMES',
    'label' => 'OTHER NAMES',
    'hAlign' => 'left',
    'width' => '25%',
    'value' => function($model){
        return $model['student']['OTHER_NAMES'];
    }
];
$examTypeColumn = [
    'attribute' => 'EXAM_TYPE',
    'label' => 'EXAM TYPE',
    'hAlign' => 'left'
];
$cwMarksColumn = [
    'attribute' => 'COURSE_MARKS',
    'label' => 'CW MARKS',
    'hAlign' => 'left',
    'value' => function($model){
        if(is_null($model['COURSE_MARKS'])){
            return '--';
        }
        return $model['COURSE_MARKS'];
    }
];
$examMarksColumn = [
    'attribute' => 'EXAM_MARKS',
    'label' => 'EXAM MARKS',
    'hAlign' => 'left',
    'value' => function($model){
        if(is_null($model['EXAM_MARKS'])){
            return '--';
        }
        return $model['EXAM_MARKS'];
    }
];
$totalMarksColumn = [
    'attribute' => 'FINAL_MARKS',
    'label' => 'TOTAL MARKS',
    'hAlign' => 'left',
    'value' => function($model){
        if(is_null($model['FINAL_MARKS'])){
            return '--';
        }
        return $model['FINAL_MARKS'];
    }
];
$gradeColumn = [
    'attribute' => 'GRADE',
    'label' => 'GRADE',
    'hAlign' => 'left',
    'value' => function($model){
        if(is_null($model['GRADE'])){
            return '--';
        }
        return $model['GRADE'];
    }
];

$gridId = 'consolidated-marks-report';

$fileName = $reportDetails['academicYear'] . '_' .$reportDetails['degreeCode'] . '_' . $reportDetails['level'] .
    '_sem_' . $reportDetails['semesterFullName'] . '_' . $reportDetails['group'];
$fileName .= '_' . $reportDetails['courseCode'] . '_consolidated_marks_report';
$fileName = strtolower($fileName);
$fileName = str_replace(' ', '_', $fileName);

$centerContent = $reportDetails['courseCode'] . ' consolidated marks';

$contentBefore = '<p style="color:#333333; font-weight: bold;">ACADEMIC YEAR: ' . $reportDetails['academicYear'] . '</p>';
$contentBefore .= '<p style="color:#333333; font-weight: bold;">PROGRAMME: ' . $reportDetails['degreeName'] . ' (' . $reportDetails['degreeCode'] . ') </p>';
$contentBefore .= '<p style="color:#333333; font-weight: bold;">LEVEL OF STUDY: ' . strtoupper($reportDetails['level']) . '</p>';
$contentBefore .= '<p style="color:#333333; font-weight: bold;">SEMESTER: ' . $reportDetails['semesterFullName'] . '</p>';
$contentBefore .= '<p style="color:#333333; font-weight: bold;">GROUP: ' . strtoupper($reportDetails['group']) . '</p>';

$contentAfter = '';

try {
    echo GridView::widget([
        'id' => $gridId,
        'dataProvider' => $marksPreviewProvider,
        'filterModel' => $marksPreviewSearch,
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
            '{export}',
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
        'export' => [
            'fontAwesome' => false,
            'label' => 'Download report'
        ],
        'exportConfig' => [
            GridView::PDF => GridExport::exportPdf([
                'filename' => $fileName,
                'title' => $title,
                'subject' => 'marks',
                'keywords' => 'marks preview',
                'centerContent' => $centerContent,
                'contentBefore' => $contentBefore,
                'contentAfter' => $contentAfter,
            ])
        ],
        'itemLabelSingle' => 'student',
        'itemLabelPlural' => 'students',
        'columns' => [
            [
                'class' => 'kartik\grid\SerialColumn',
                'width' => '5%'
            ],
            $regNumColumn,
            $surnameColumn,
            $otherNamesColumn,
            $examTypeColumn,
            $cwMarksColumn,
            $examMarksColumn,
            $totalMarksColumn,
            $gradeColumn
        ]
    ]);
} catch (Exception $ex) {
    $message = 'An error occurred while trying to display the consolidated marks report.';
    if (YII_ENV_DEV) {
        $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}


