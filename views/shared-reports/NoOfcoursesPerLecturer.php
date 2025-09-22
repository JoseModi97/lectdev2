<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var $this yii\web\View
 * @var $model app\models\CourseAssignment
 * @var $coursesProvider yii\data\ActiveDataProvider
 * @var $courseSearch app\models\search\NoOfAllocatedCoursesPerLecturerSearch
 * @var $title string
 * @var $academicYear string
 * @var $deptName string
 * @var $deptCode string
 * @var $facCode string
 * @var $facName string
 * @var string $level
 */

use app\components\GridExport;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

$this->title = $title;

$courseAllocationsInDeptsUrl = '';
$courseAllocationsPerLecturerUrl = '';
if($level === 'dean'){
    $courseAllocationsInDeptsUrl = '/dean-reports/course-allocations-in-departments';
    $courseAllocationsPerLecturerUrl = '/dean-reports/course-allocations-per-lecturer';
}elseif ($level === 'facAdmin'){
    $courseAllocationsInDeptsUrl = '/faculty-admin-reports/course-allocations-in-departments';
    $courseAllocationsPerLecturerUrl = '/faculty-admin-reports/course-allocations-per-lecturer';
}elseif ($level === 'sysAdmin'){
    $courseAllocationsInDeptsUrl = '/system-admin-reports/course-allocations-in-departments';
    $courseAllocationsPerLecturerUrl = '/system-admin-reports/course-allocations-per-lecturer';
}
$this->params['breadcrumbs'][] = [
    'label' => 'COURSE ALLOCATIONS IN ' . $facName,
    'url' => [
        $courseAllocationsInDeptsUrl,
        'facCode' => $facCode,
        'academicYear' => $academicYear
    ]
];

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="number-courses-per-lecturer">
    <?php
    $gridColumns = [
        [
            'class' => 'kartik\grid\SerialColumn',
            'width' => '5%'
        ],
        [
            'attribute' => 'PAYROLL_NO',
            'label' => 'PAYROLL NO.',
            'width' => '10%'
        ],
        [
            'label' => 'NAME',
            'width'=> '55%',
            'value' => function($model){
                return $model->staff->EMP_TITLE . ' ' . $model->staff->SURNAME . ' ' . $model->staff->OTHER_NAMES;
            }
        ],
        [
            'label' => 'NO. OF COURSES',
            'width' => '30%',
            'value' => function($model){
                return $model->coursesNumber;
            }
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'template' => '{lecturer-courses}',
            'contentOptions' => [
                'style'=>'white-space:nowrap;',
                'class'=>'kartik-sheet-style kv-align-middle'
            ],
            'buttons' => [
                'lecturer-courses' => function($url, $model) use($academicYear, $courseAllocationsPerLecturerUrl){
                    return Html::a('<i class="fas fa-file"></i> Allocated courses',
                        Url::to([
                            $courseAllocationsPerLecturerUrl,
                            'payroll' => $model->PAYROLL_NO,
                            'academicYear' => $academicYear
                        ]),
                        [
                            'title' => 'Allocated courses report',
                            'class' => 'btn btn-xs'
                        ]
                    );
                }
            ],
            'hAlign' => 'center'
        ]
    ];

    $gridId = 'course-allocations-in-department';
    $title = 'Course allocations in the department of ' . strtolower($deptName) . ' for the academic year ' . $academicYear;
    $fileName = 'course_allocations_in_department';
    try {
        echo GridView::widget([
            'id' => $gridId,
            'dataProvider' => $coursesProvider,
            'filterModel' => $courseSearch,
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
                'heading'=>'<h3 class="panel-title">' . $title . '</h3>',
            ],
            'toggleDataContainer' => ['class' => 'btn-group mr-2'],
            'toggleDataOptions' => ['minCount' => 20],
            'exportConfig' => [
                GridView::EXCEL => GridExport::exportExcel([
                    'filename' => $fileName,
                    'worksheet' => 'lecturer_courses'
                ]),
                GridView::PDF => GridExport::exportPdf([
                    'filename' => $fileName,
                    'title' => $title,
                    'subject' => 'departments courses',
                    'keywords' => 'departments courses',
                    'contentBefore'=> '',
                    'contentAfter'=> '',
                    'centerContent' => $title,
                ]),
            ],
            'itemLabelSingle' => 'lecturer',
            'itemLabelPlural' => 'lecturers',
        ]);
    } catch (Exception $ex) {
        $message = 'There were errors while trying to display number of courses per lecturer.';
        if(YII_ENV_DEV){
            $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
        }
        throw new ServerErrorHttpException($message, 500);
    }
    ?>
</div>
