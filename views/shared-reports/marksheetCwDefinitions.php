<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var app\models\CourseWorkAssessment $model
 * @var yii\data\ActiveDataProvider $assessmentsProvider
 * @var app\models\search\CourseworkAssessmentSearch $assessmentsSearch
 * @var string $title
 * @var string $academicYear
 * @var string $facultyName
 * @var string $facultyCode
 * @var string $departmentName
 * @var string $departmentCode
 * @var string $courseCode
 * @var string $courseName
 * @var string $level
 */

use app\components\GridExport;
use kartik\grid\GridView;
use yii\web\ServerErrorHttpException;

$cwDefinitionsInProgrammesUrl = '';
if($level === 'dean'){
    $cwDefinitionsInProgrammesUrl = '/dean-reports/programme-course-work-definition';
}elseif ($level === 'facAdmin'){
    $cwDefinitionsInProgrammesUrl = '/faculty-admin-reports/programme-course-work-definition';
}elseif($level === 'sysAdmin'){
    $cwDefinitionsInProgrammesUrl = '/system-admin-reports/programme-course-work-definition';
}
$this->params['breadcrumbs'][] = [
    'label' => 'COURSE WORK DEFINITIONS IN PROGRAMMES',
    'url' => [
        $cwDefinitionsInProgrammesUrl,
        'deptCode' => $departmentCode,
        'academicYear' => $academicYear
    ]
];

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="marksheet-course-work-definition">
    <h6> DEPARTMENT OF <?= $departmentName; ?></h6>

    <?php
    $gridColumns = [
        ['class' => 'kartik\grid\SerialColumn'],
        [
            'attribute' => 'assessmentType.ASSESSMENT_NAME',
            'label' => 'NAME',
            'value' => function($model){
                return $model->assessmentType->ASSESSMENT_NAME;
            }
        ],
        [
            'attribute' => 'WEIGHT',
            'label' => 'WEIGHT'
        ],
        [
            'attribute' => 'DIVIDER',
            'label' => 'MARKED OUT OF'
        ],
        [
            'attribute' => 'RESULT_DUE_DATE',
            'label' => 'RESULT DUE DATE',
        ]
    ];

    $gridId = 'cw-definition-in-programmes';
    $title = 'Course work definitions for ' . $courseName . ' ('. $courseCode . ') | Academic year ' . $academicYear;
    $fileName = 'course_work_definition_for_' . $courseCode;
    try {
        echo GridView::widget([
            'id' => $gridId,
            'dataProvider' => $assessmentsProvider,
            'filterModel' => $assessmentsSearch,
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
                    'worksheet' => 'course work'
                ]),
                GridView::PDF => GridExport::exportPdf([
                    'filename' => $fileName,
                    'title' => $title,
                    'subject' => 'course work',
                    'keywords' => 'course work',
                    'contentBefore' => '',
                    'contentAfter' => '',
                    'centerContent' => $title
                ]),
            ],
            'itemLabelSingle' => 'course work',
            'itemLabelPlural' => 'course work',
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
