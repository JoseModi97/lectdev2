<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 27-01-2021 12:26:31 
 * @modify date 27-01-2021 12:26:31 
 * @desc [description]
 */

/**
 * @var $this yii\web\View 
 * @var $model app\models\MarksheetDef
 * @var $searchModel app\models\search\DepartmentCoursesSearch 
 * @var $departmentCoursesProvider yii\data\ActiveDataProvider
 * @var $title string
 * @var $deptCode string 
 * @var $deptName string 
 * @var $academicYear string 
 * @var $level string
 * @var $type string
 * @var $facName string
 */

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;

$this->title = $title;

if($level !== 'hod'){
    $this->params['breadcrumbs'][] = [
        'label' => 'departments', 
        'url' => [
            '/departments/in-faculty', 
            'level' => $level, 
            'type' => $type
        ]
    ];
}

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="department-courses">
    <h3><?= $facName;?></h3>
    <?php
        $gridId = 'department-courses-grid';
        $title = 'COURSES IN THE DEPARTMENT OF ' . $deptName . ' | ACADEMIC YEAR ' . $academicYear;
        $gridColumns = [
            ['class' => 'kartik\grid\SerialColumn'],
            [
                'class' => 'kartik\grid\ExpandRowColumn',
                'width' => '50px',
                'value' => function ($model, $key, $index, $column) {
                    return GridView::ROW_COLLAPSED;
                },
                'detail' => function ($model, $key, $index, $column) use ($level) {
                    return Yii::$app->controller->renderPartial('_course_assessments', [
                        'marksheetId' => $model['MRKSHEET_ID'],
                        'courseCode' => $model['course']['COURSE_CODE'],
                        'courseName' => $model['course']['COURSE_NAME'],
                        'degreeCode' => $model['semester']['degreeProgramme']['DEGREE_CODE'],
                        'level' => $level
                    ]);
                },
                'headerOptions' => ['class' => 'kartik-sheet-style ficha'],
                'contentOptions'=>['class'=>'kartik-sheet-style ficha'],
                'expandOneOnly' => true,    
            ],
            [
                'attribute' => 'course.COURSE_CODE',
                'label' => 'COURSE CODE',
            ],
            [
                'attribute' => 'course.COURSE_NAME',
                'label' => 'COURSE NAME'
            ],
            [
                'attribute' => 'semester.degreeProgramme.DEGREE_NAME',
                'label' => 'DEGREE NAME',
                'width' => '310px',
                'group' => true,  
                'groupedRow' => true,                    
                'groupOddCssClass' => 'kv-grouped-row',  
                'groupEvenCssClass' => 'kv-grouped-row', 
            ]
        ];

        echo GridView::widget([
            'id' => $gridId,
            'dataProvider' => $departmentCoursesProvider,
            'filterModel' => $searchModel,
            'columns' => $gridColumns,
            'headerRowOptions' => ['class' => 'kartik-sheet-style'],
            'filterRowOptions' => ['class' => 'kartik-sheet-style'],
            'pjax' => true,
            'pjaxSettings'=>[
                'options' => [
                    'id' => $gridId.'-pjax'
                ]
            ],
            'toolbar' => [
                '{toggleData}'
            ],
            'panel' => [
                'type' => GridView::TYPE_PRIMARY,
                'heading'=>'<h3 class="panel-title">'.$title.'</h3>',
            ],
            'toggleDataContainer' => ['class' => 'btn-group mr-2'],
            'toggleDataOptions' => ['minCount' => 20],
            'itemLabelSingle' => 'course',
            'itemLabelPlural' => 'courses',
        ]);
    ?>
</div>


