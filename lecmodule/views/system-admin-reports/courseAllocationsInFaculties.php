<?php
/**
 * @var $this yii\web\View
 * @var $model $facultyAllocations
 * @var $facultyAllocationsProvider yii\data\ActiveDataProvider
 * @var $title string
 * @var $academicYear string
 */

use app\components\GridExport;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="course-allocations-in-faculties-index">
    <?php
    echo $this->render('_academicYearFilter', ['reportType' => 'lecturerCourseAllocations']);

    $gridColumns = [
        [
            'class' => 'kartik\grid\SerialColumn',
            'width' => '5%'
        ],
        [
            'label' => 'CODE',
            'width' => '10',
            'value' => function($model){
                return $model['facCode'];
            }
        ],
        [
            'label' => 'NAME',
            'width' => '65',
            'value' => function($model){
                return $model['facName'];
            }
        ],
        [
            'label' => 'LECTURERS',
            'width' => '10',
            'value' => function($model){
                return $model['lecturers'];
            }
        ],
        [
            'label' => 'LECTURERS WITH COURSES',
            'width' => '10',
            'value'=> function($model){
                return $model['lecturersWithCourses'];
            }
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'template' => '{course-allocations-in-department}',
            'contentOptions' => [
                'style'=>'white-space:nowrap;',
                'class'=>'kartik-sheet-style kv-align-middle'
            ],
            'buttons' => [
                'course-allocations-in-department' => function($url, $model) use($academicYear){
                    return Html::a('<i class="fas fa-file"></i> Course allocations in departments',
                        Url::to([
                            '/system-admin-reports/course-allocations-in-departments',
                            'facCode' => $model['facCode'],
                            'academicYear' => $academicYear
                        ]),
                        [
                            'title' => 'Course allocations in departments report',
                            'class' => 'btn btn-xs'
                        ]
                    );
                }
            ],
            'hAlign' => 'center'
        ]
    ];

    $gridId = 'course-allocations-in-faculties';
    $title = 'Course allocations in the faculties for the academic year ' . $academicYear;
    $fileName = 'course_allocations_in_faculties';
    try {
        echo GridView::widget([
            'id' => $gridId,
            'dataProvider' => $facultyAllocationsProvider,
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
                '{toggleData}',
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
                    'worksheet' => 'faculties'
                ]),
                GridView::PDF => GridExport::exportPdf([
                    'filename' => $fileName,
                    'title' => $title,
                    'subject' => 'faculties courses',
                    'keywords' => 'faculties courses',
                    'contentBefore'=> '',
                    'contentAfter'=> '',
                    'centerContent' => $title,
                ]),
            ],
            'itemLabelSingle' => 'faculty',
            'itemLabelPlural' => 'faculties',
        ]);
    } catch (Exception $e) {
    }
    ?>
</div>
