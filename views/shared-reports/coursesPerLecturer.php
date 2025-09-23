<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var $this yii\web\View
 * @var $model app\models\CourseAssignment
 * @var $coursesProvider yii\data\ActiveDataProvider
 * @var $courseSearch app\models\search\AllocatedCoursesPerLecturerSearch
 * @var $title string
 * @var $academicYear string
 * @var $deptName string
 * @var $deptCode string
 * @var $lecturer string
 * @var string $level
 */

use app\components\GridExport;
use kartik\grid\GridView;
use yii\web\ServerErrorHttpException;

$this->title = $title;

$noOfCoursesPerLecturerUrl = '';
if($level === 'dean'){
    $noOfCoursesPerLecturerUrl = '/dean-reports/number-of-course-allocations-per-lecturer';
}elseif ($level === 'facAdmin'){
    $noOfCoursesPerLecturerUrl = '/faculty-admin-reports/number-of-course-allocations-per-lecturer';
}elseif($level === 'sysAdmin'){
    $noOfCoursesPerLecturerUrl = '/system-admin-reports/number-of-course-allocations-per-lecturer';
}
$this->params['breadcrumbs'][] = [
    'label' => 'COURSE ALLOCATIONS IN THE DEPARTMENT OF ' . $deptName,
    'url' => [
        $noOfCoursesPerLecturerUrl,
        'deptCode' => $deptCode,
        'academicYear' => $academicYear
    ]
];

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="courses-per-lecturer">
    <?php
    $gridColumns = [
        [
            'class' => 'kartik\grid\SerialColumn',
            'width' => '5%'
        ],
        [
            'attribute' => 'marksheetDef.semester.degreeProgramme.faculty.FACULTY_NAME',
            'label' => 'FACULTY NAME',
            'value' => function($model){
                return $model['marksheetDef']['semester']['degreeProgramme']['faculty']['FACULTY_NAME'];
            },
            'group' => true,
            'width' => '20%'
        ],
        [
            'attribute' => 'marksheetDef.semester.degreeProgramme.DEGREE_NAME',
            'label' => 'DEGREE NAME',
            'value' => function($model){
                return $model['marksheetDef']['semester']['degreeProgramme']['DEGREE_NAME'];
            },
            'group' => true,
            'width' => '20%'
        ],
        [
            'attribute' => 'marksheetDef.course.COURSE_CODE',
            'label' => 'COURSE',
            'value' => function($model){
                return $model['marksheetDef']['course']['COURSE_CODE'] .' - '.$model['marksheetDef']['course']['COURSE_NAME'];
            },
            'width' => '55%'
        ],
        [
            'attribute' => 'marksheetDef.course.dept.DEPT_CODE',
            'label' => 'DEPARTMENT CODE',
            'value' => function($model){
                return $model['marksheetDef']['course']['dept']['DEPT_CODE'];
            },
        ],
        [
            'attribute' => 'marksheetDef.course.dept.DEPT_NAME',
            'label' => 'DEPARTMENT NAME',
            'value' => function($model){
                return $model['marksheetDef']['course']['dept']['DEPT_NAME'];
            },
        ]
    ];

    $gridId = 'courses-per-lecturer';
    $title = 'Courses allocated for ' . $lecturer . ' for the academic year ' . $academicYear;
    $fileName = 'allocated_courses';
    try {
        echo GridView::widget([
            'id' => $gridId,
            'dataProvider' => $coursesProvider,
            'filterModel' => $courseSearch,
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
                    'worksheet' => 'courses'
                ]),
                GridView::PDF => GridExport::exportPdf([
                    'filename' => $fileName,
                    'title' => $title,
                    'subject' => 'courses timetables',
                    'keywords' => 'courses timetables',
                    'contentBefore' => '',
                    'contentAfter' => '',
                    'centerContent' => $title
                ]),
            ],
            'itemLabelSingle' => 'course',
            'itemLabelPlural' => 'courses',
        ]);
    } catch (Exception $ex) {
        $message = 'There were errors while trying to display courses allocated per lecturer.';
        if(YII_ENV_DEV){
            $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
        }
        throw new ServerErrorHttpException($message, 500);
    }
    ?>
</div>

