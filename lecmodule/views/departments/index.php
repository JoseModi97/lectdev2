<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 26-01-2021 15:27:31 
 * @modify date 26-01-2021 15:27:31 
 * @desc [description]
 */

/* @var $this yii\web\View */
/* @var $model app\models\Department */
/* @var $searchModel app\models\search\DepartmentsSearch */
/* @var $departmentsProvider yii\data\ActiveDataProvider */
/* @var $title string */
/* @var $facCode string */
/* @var $level string */
/* @var $type string */ 
/* @var $facName string */ 

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="faculty-departments">
    <h3><?= $facName;?></h3>
    <?php
        $gridId = 'faculty-departments-grid';
        $title = 'DEPARTMENTS IN THE '.$facName;

        $gridColumns = [
            ['class' => 'kartik\grid\SerialColumn'],
            [
                'attribute' => 'DEPT_CODE',
                'label' => 'DEPARTMENT CODE'
            ],
            [
                'attribute' => 'DEPT_NAME',
                'label' => 'DEPARTMENT NAME'
            ],
            [
                'class' => 'kartik\grid\ActionColumn', 
                'template' => '{view-department-courses}',
                'contentOptions' => ['style'=>'white-space:nowrap;','class'=>'kartik-sheet-style kv-align-middle'],
                'buttons' => [
                 'view-department-courses' => function($url, $model) use ($level, $type) {
                        return Html::a('<i class="fas fa-eye"></i> view courses', 
                            Url::to([
                                '/courses/in-department', 
                                'level' => $level, 
                                'deptCode' => $model['DEPT_CODE'], 
                                'type' => $type
                            ]), 
                            [
                                'title' => 'View courses in the department',
                                'class' => 'btn btn-xs btn-spacer' 
                            ]
                        );
                    }
                ]
            ]
        ];

        echo GridView::widget([
            'id' => $gridId,
            'dataProvider' => $departmentsProvider,
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
            'itemLabelSingle' => 'department',
            'itemLabelPlural' => 'departments',
        ]);
    ?>
</div>

