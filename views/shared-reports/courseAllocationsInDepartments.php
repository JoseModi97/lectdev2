<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var departmentAllocations $model
 * @var yii\data\ActiveDataProvider $departmentAllocationsProvider
 * @var string $title
 * @var string $academicYear
 * @var string $facName
 * @var string $level
 */

use app\components\GridExport;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

$this->title = $title;

$academicYearFilterUrl = '';
$noOfCoursesPerLecturerUrl = '';
if($level === 'dean'){
    $academicYearFilterUrl = '/dean-reports/set-academic-year';
    $noOfCoursesPerLecturerUrl = '/dean-reports/number-of-course-allocations-per-lecturer';
}elseif ($level === 'facAdmin'){
    $academicYearFilterUrl = '/faculty-admin-reports/set-academic-year';
    $noOfCoursesPerLecturerUrl = '/faculty-admin-reports/number-of-course-allocations-per-lecturer';
}

if($level === 'sysAdmin'){
    $academicYearFilterUrl = '/system-admin-reports/set-academic-year';
    $noOfCoursesPerLecturerUrl = '/system-admin-reports/number-of-course-allocations-per-lecturer';

    $this->params['breadcrumbs'][] = [
        'label' => 'COURSE ALLOCATIONS IN FACULTIES',
        'url' => [
            '/system-admin-reports/course-allocations-in-faculties',
            'academicYear' => $academicYear
        ]
    ];
}

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="course-allocations-in-departments-index">
    <?php
    echo $this->render('_academicYearFilter', ['reportType' => 'lecturerCourseAllocations', 'url' => $academicYearFilterUrl]);

    $gridColumns = [
        [
            'class' => 'kartik\grid\SerialColumn',
            'width' => '5%'
        ],
        [
            'label' => 'CODE',
            'width' => '10%',
            'value' => function($model){
                return $model['deptCode'];
            }
        ],
        [
            'label' => 'NAME',
            'width' => '55%',
            'value' => function($model){
                return $model['deptName'];
            }
        ],
        [
            'label' => 'LECTURERS',
            'width' => '10%',
            'value' => function($model){
                return $model['lecturers'];
            }
        ],
        [
            'label' => 'LECTURERS WITH COURSES',
            'width' => '20%',
            'value'=> function($model){
                return $model['lecturersWithCourses'];
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
                'lecturer-courses' => function($url, $model) use($academicYear, $noOfCoursesPerLecturerUrl){
                    return Html::a('<i class="fas fa-file"></i> Number of courses per lecturer',
                        Url::to([
                            $noOfCoursesPerLecturerUrl,
                            'deptCode' => $model['deptCode'],
                            'academicYear' => $academicYear
                        ]),
                        [
                            'title' => 'Number of courses per lecturer report',
                            'class' => 'btn btn-xs'
                        ]
                    );
                }
            ],
            'hAlign' => 'center'
        ]
    ];

    $gridId = 'course-allocations-in-departments';
    $title = 'Course allocations in the ' . strtolower($facName) . ' for the academic year ' . $academicYear;
    $fileName = 'course_allocations_in_departments';
    try{
        echo GridView::widget([
            'id' => $gridId,
            'dataProvider' => $departmentAllocationsProvider,
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
            'toggleDataOptions' => ['minCount' => 50],
            'exportConfig' => [
                GridView::EXCEL => GridExport::exportExcel([
                    'filename' => $fileName,
                    'worksheet' => 'departments'
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
            'itemLabelSingle' => 'department',
            'itemLabelPlural' => 'departments',
        ]);
    } catch (Exception $ex) {
        $message = 'There were errors while trying to display course allocations in the faculty departments.';
        if(YII_ENV_DEV){
            $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
        }
        throw new ServerErrorHttpException($message, 500);
    }
    ?>
</div>

