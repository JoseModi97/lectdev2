<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 08-03-2021 11:59:48 
 * @modify date 08-03-2021 11:59:48 
 * @desc [description]
 */

/**
 * @var $this yii\web\View
 * @var $model app\models\ReturnedScript
 * @var $searchModel app\models\search\ReturnedScriptsSearch
 * @var $returnedScriptsProvider yii\data\ActiveDataProvider
 * @var $title string
 * @var $courseName string
 * @var $courseCode string
 * @var $facName string
 * @var $academicYear string
 * @var $level string 
 * @var $deptCode string
 * @var $deptName string
 * @var $type string
 */

use app\components\GridExport;
use app\models\EmpVerifyView;
use kartik\grid\GridView;

$this->title = $title;

$this->params['breadcrumbs'][] = [
    'label' => 'courses in the department of ' . $deptName, 
    'url' => [
        '/courses/in-department', 
        'level' => $level, 
        'deptCode' => $deptCode,
        'type' => $type
    ]
];

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="returned-scripts-report">
    <h3><?= $facName;?></h3>
    <?php
        $gridId = 'returned-scripts-grid';
        $title = 'RETURNED SCRIPTS FOR '.$courseName.' | '.$courseCode .' | ACADEMIC YEAR ' . $academicYear;
        $fileName = $courseCode.'_returned_scripts';
        $contentBefore = '';
        $contentAfter = '';
        $centerContent = $courseCode.' RETURNED SCRIPTS | ACADEMIC YEAR '.$academicYear;
        $gridColumns = [
            ['class' => 'kartik\grid\SerialColumn'],
            [
                'attribute' => 'REGISTRATION_NUMBER',
                'label' => 'REGISTRATION NUMBER',
                'width' => '10%',
            ],
            [
                'attribute' => 'REMARKS',
                'label' => 'REMARKS',
                'width' => '30%',
                'value' => function($model){
                    if(is_null($model->REMARKS)) return '';
                    else return $model->REMARKS;
                }
            ],
            [
                'attribute' => 'RECEIVED_BY',
                'label' => 'RECEIVED BY' ,
                'width' => '15%',
                'value' => function($model){
                    if(is_null($model->RECEIVED_BY)) return '';
                    else {
                        $lecturer = EmpVerifyView::find()
                            ->select(['PAYROLL_NO', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE'])
                            ->where(['PAYROLL_NO' => $model->RECEIVED_BY])
                            ->one();
                        return $lecturer->EMP_TITLE.' '.$lecturer->SURNAME.' '.$lecturer->OTHER_NAMES;
                    }
                }
            ],
            [
                'attribute' => 'RETURNED_BY',
                'label' => 'RETURNED BY' ,
                'hAlign' => 'left',
                'width' => '15%',
                'value' => function($model){
                    return $model->RETURNED_BY;
                    $lecturer = EmpVerifyView::find()
                        ->select(['PAYROLL_NO', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE'])
                        ->where(['PAYROLL_NO' => $model->RETURNED_BY])
                        ->one();
                    return $lecturer->EMP_TITLE.' '.$lecturer->SURNAME.' '.$lecturer->OTHER_NAMES;
                }
            ],
            [
                'attribute' => 'RETURNED_DATE',
                'label' => 'RETURNED DATE',
                'hAlign' => 'left',
                'width' => '20%',
                'format' => 'raw',
                'contentOptions'=>['class'=>'kartik-sheet-style kv-align-middle'],
                'filterType' => GridView::FILTER_DATE,
                'filterWidgetOptions' => [
                    'options'=>['id'=>'exam-marks-date-entered'],
                    'pluginOptions' => ['autoclose'=>true,'allowClear' => true,'format' => 'dd-M-yyyy',],
                ],
                'filterInputOptions' => ['placeholder' => 'Date Entered'],
                'value' => function($model){
                    return $model->RETURNED_DATE;
                    $fullDate = Yii::$app->formatter->asDate($model->RETURNED_DATE, 'full');
                    $relativeTime = Yii::$app->formatter->format($model->RETURNED_DATE, 'relativeTime');
                    $relativeTime = "<b class='text-primary'>$relativeTime</b>";
                    return $fullDate;
                }
            ]
         ]; 

         echo GridView::widget([
            'id' => $gridId,
            'dataProvider' => $returnedScriptsProvider,
            'filterModel' => $searchModel,
            'columns' => $gridColumns,
            'headerRowOptions' => ['class' => 'kartik-sheet-style'],
            'filterRowOptions' => ['class' => 'kartik-sheet-style'],
            'pjax' => true, 
            'toolbar' => [
                '{export}',
                '{toggleData}'
            ],
            'toggleDataContainer' => ['class' => 'btn-group mr-2'],
            'export' => [
                'fontAwesome' => false,
            ],
            'panel' => [
                'type' => GridView::TYPE_PRIMARY,
                'heading'=>'<h3 class="panel-title">'.$title.'</h3>',
            ],
            'persistResize' => false,
            'toggleDataOptions' => ['minCount' => 20],
            'exportConfig' => [
                GridView::PDF => GridExport::exportPdf([
                    'filename' => $fileName, 
                    'title' => $title,
                    'subject' => 'returned scripts',
                    'keywords' => 'returned scripts',
                    'contentBefore'=> $contentBefore,
                    'contentAfter'=> $contentAfter,
                    'centerContent' => $centerContent
                ])
            ],
            'itemLabelSingle' => 'returned script',
            'itemLabelPlural' => 'returned scripts',
        ]);
    ?>
</div>