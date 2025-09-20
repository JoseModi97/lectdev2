<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 16-02-2021 14:19:23 
 * @modify date 16-02-2021 14:19:23 
 * @desc [description]
 */

/**
 * @var $this yii\web\View
 * @var $model app\models\Marksheet
 * @var $searchModel app\models\search\MarksheetSearch
 * @var $mkProvider yii\data\ActiveDataProvider
 * @var $marksheetId string
 * @var $courseCode string
 * @var $courseName string
 * @var $level string
 * @var $deptCode string
 * @var $depName string
 * @var $type string 
 * @var $facName string
 */

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

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

<div class="mark-scripts">
    <h3><?= $facName;?></h3>
    <?php
        $title = $courseName.' ('.$courseCode.') EXAM ATTENDANCE REGISTER';

        $form = ActiveForm::begin([
            'id' => 'returned-scripts-form',
            'action' => Url::to(['/returned-scripts/mark-scripts']),
            'enableAjaxValidation' => true,
            'options' => [
                'data' => ['pjax' => 1], 
                'enctype' => 'multipart/form-data'
            ]
        ]);

        echo '<input name="MARKSHEET_ID" type="hidden" readonly value="'.$marksheetId.'"/>';

        $gridColumns = [
            ['class' => 'kartik\grid\SerialColumn'],  
            [
                'class' => 'kartik\grid\CheckboxColumn',
                'width' => '10%',
                'headerOptions' => ['class' => 'kartik-sheet-style'],
                'checkboxOptions' => function ($model, $key, $index, $column) use ($marksheetId){
                    $regNumber = $model['REGISTRATION_NUMBER'];
                    $returnedStatus = (int)1;
                    $notReturnedStatus = (int)0;
                    $returnedScript = \app\models\ReturnedScript::find()
                        ->select(['STATUS'])
                        ->where(['MARKSHEET_ID' => $marksheetId, 'REGISTRATION_NUMBER' => $regNumber])
                        ->one();
                    if(is_null($returnedScript) || $returnedScript->STATUS === $notReturnedStatus)
                        $checked = false;
                    elseif($returnedScript->STATUS === $returnedStatus)
                        $checked = true;
                    return [
                        'value' => $regNumber,
                        'class' => 'mark-scripts-checkbox',
                        'checked' => $checked 
                    ];
                }
            ],
            [
                'attribute' => 'REGISTRATION_NUMBER',
                'label' => 'REGISTRATION NUMBER',
                'width' => '10%',
            ],
            [
                'attribute' => 'student.SURNAME',
                'label' => 'SURNAME',
                'width' => '15%',
            ],
            [
                'attribute' => 'student.OTHER_NAMES',
                'label' => 'OTHER NAMES',
                'width' => '20%',
            ],
            [
                'label' => 'SCRIPT STATUS',
                'format' => 'raw',
                'vAlign' => 'middle',
                'width' => '15%',
                'value' => function($model) use ($marksheetId){
                    $regNumber = $model['REGISTRATION_NUMBER'];
                    $returnedStatus = (int)1;
                    $notReturnedStatus = (int)0;
                    $returnedScript = \app\models\ReturnedScript::find()
                        ->select(['STATUS'])
                        ->where(['MARKSHEET_ID' => $marksheetId, 'REGISTRATION_NUMBER' => $regNumber])
                        ->one();
                    if(is_null($returnedScript) || $returnedScript->STATUS === $notReturnedStatus)
                        $status = '<div class="p-3 mb-2 bg-danger" style="padding: 5px; margin-bottom:2px;">NOT RETURNED</div>';
                    elseif($returnedScript->STATUS === $returnedStatus)
                        $status = '<div class="p-3 mb-2 bg-success" style="padding: 5px; margin-bottom:2px;">RETURNED</div>';
                    return $status;
                } 
            ],
            [
                'label' => 'REMARKS',
                'hAlign' => 'left',
                'width' => '25%',
                'format' => 'raw',
                'value' => function($model) use ($marksheetId){
                    $regNumber = $model['REGISTRATION_NUMBER'];
                    $returnedScript = \app\models\ReturnedScript::find()
                        ->select(['REMARKS'])
                        ->where(['MARKSHEET_ID' => $marksheetId, 'REGISTRATION_NUMBER' => $regNumber])
                        ->one();
                    if(is_null($returnedScript) || is_null($returnedScript->REMARKS))
                        $remarks = '';
                    else
                        $remarks = $returnedScript->REMARKS;
                    $input = '<input type="text" name="REMARKS['.$regNumber.']" class="form-control scripts-remarks" value="'.$remarks.'">';
                    return $input;
                }
            ]
        ];

        echo GridView::widget([
            'id' => 'markscriptsGridView',
            'dataProvider' => $mkProvider,
            'filterModel' => $searchModel,
            'columns' => $gridColumns,
            'headerRowOptions' => ['class' => 'kartik-sheet-style'],
            'filterRowOptions' => ['class' => 'kartik-sheet-style'],
            'pjax' => true, 
            'toolbar' =>  [
                [
                    'content' => 
                        Html::submitButton('Save changes', [
                            'title' => 'Save changes',
                            'id' => 'submit-returned-scripts', 
                            'class' => 'btn btn-spacer',
                        ]),
                    'options' => ['class' => 'btn-group mr-2']
                ],
                '{toggleData}'
            ],
            'toggleDataContainer' => ['class' => 'btn-group mr-2'],
            'panel' => [
                'type' => GridView::TYPE_PRIMARY,
                'heading'=>'<h3 class="panel-title">'.$title,
            ],
            'persistResize' => false,
            'toggleDataOptions' => ['minCount' => 35],
            'itemLabelSingle' => 'student',
            'itemLabelPlural' => 'students',
        ]);

        ActiveForm::end();
    ?>
</div>

