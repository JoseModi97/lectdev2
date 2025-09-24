<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 16-02-2021 11:40:12 
 * @modify date 16-02-2021 11:40:12 
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

$this->params['breadcrumbs'][] = [
    'label' => 'departments', 
    'url' => [
        '/departments/in-faculty', 
        'level' => $level, 
        'type' => $type
    ]
];

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="returned-script-courses">
    <h3><?= $facName;?></h3>
    <?php
        $gridColumns = [
            ['class' => 'kartik\grid\SerialColumn'],
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
            ],
            [
                'class' => 'kartik\grid\ActionColumn', 
                'template' => '{returned-scripts-report}',
                'contentOptions' => ['style'=>'white-space:nowrap;','class'=>'kartik-sheet-style kv-align-middle'],
                'buttons' => [
                    'returned-scripts-report' => function($url, $model) use($level, $type){
                        return Html::a('<i class="fas fa-file"></i> Returned scripts report', 
                            Url::to([
                                '/faculty-admin-reports/returned-scripts', 
                                'marksheetId' => $model['MRKSHEET_ID'],
                                'level' => $level,
                                'type' => $type,
                                'deptCode' => $model['course']['dept']['DEPT_CODE']
                            ]), 
                            [
                                'title' => 'Returned scripts report',
                                'class' => 'btn btn-xs btn-spacer' 
                            ]
                        );
                    }
                ]
            ]
        ];

        $gridId = 'returned-script-courses-grid';
        $title = 'COURSES IN THE DEPARTMENT OF ' . $deptName . ' | ACADEMIC YEAR ' . $academicYear;

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