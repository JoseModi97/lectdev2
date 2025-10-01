<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 15-01-2021 12:18:33 
 * @modify date 15-01-2021 12:18:33 
 * @desc [description]
 */

/* @var string $deptCode */

use app\models\AllocationStatus;
use app\models\EmpVerifyView;
use kartik\select2\Select2;
use yii\bootstrap5\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$lecturers = EmpVerifyView::find()
    ->select(['PAYROLL_NO', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE'])
    ->where(['DEPT_CODE' => $deptCode, 'STATUS_DESC' => 'ACTIVE', 'JOB_CADRE' => 'ACADEMIC'])
    ->all();

$lecturers = ArrayHelper::map($lecturers, 'PAYROLL_NO', function($lecturer){
    return $lecturer->EMP_TITLE.' '.$lecturer->SURNAME.' '.$lecturer->OTHER_NAMES;
});

$status = AllocationStatus::find()->where(['NOT', ['STATUS_NAME' => 'PENDING']])->all();
$statusList = ArrayHelper::map($status, 'STATUS_NAME', function($sta){
    return $sta->STATUS_NAME;
});

Modal::begin([
    'title' => '<b>Allocate course lecturer(s)</b>',
    'id' => 'allocate-external-lecturers-modal',
    'size' => 'modal-xl',
    'options' => ['data-backdrop'=>"static", 'data-keyboard'=>"false"],
    'dialogOptions' => [
        'class' => 'modal-dialog-scrollable modal-dialog-centered',
        'style' => 'max-width: 1100px; max-height: 90vh;'
    ],
    'headerOptions' => [
        'style' => 'background-image: linear-gradient(#455492, #304186, #455492); color:#fff'
    ],
]);
?>

<div class="content-loader" style="border-radius: 5px;"></div>

<!-- course details -->
<style>
    .allocate-info p { margin-bottom: 6px; }
    .allocate-info .label { color:#0d6efd; font-weight:600; }
    @media (min-width: 768px){ .allocate-info .col-md-6 { padding-right:12px; padding-left:12px; } }
    .form-border { border:1px solid #008cba; border-radius:6px; }
    .form-border .card-body { padding:12px 14px; }
    .kv-editable-input, .select2-container{ width:100% !important; }
    .modal-xl .select2-container{ max-width:100%; }
    .gap-row{ row-gap:8px; }
    .truncate-wrap{ word-break:break-word; }
    .allocate-info .value{ display:inline-block; }
</style>
<div class="card form-border">
    <div class="card-body allocate-info">
        <div class="row gap-row">
            <div class="col-md-6 truncate-wrap"><p class="card-text"><span class="label">ACADEMIC YEAR: </span> <span class="lecturer-allocation-academic-year value"></span></p></div>
            <div class="col-md-6 truncate-wrap"><p class="card-text"><span class="label">DEGREE PROGRAMME: </span> <span class="lecturer-allocation-degree-name value"></span></p></div>
        </div>
        <div class="row gap-row">
            <div class="col-md-6 truncate-wrap"><p class="card-text"><span class="label">COURSE CODE: </span> <span class="lecturer-allocation-course-code value"></span></p></div>
            <div class="col-md-6 truncate-wrap"><p class="card-text"><span class="label">COURSE NAME: </span> <span class="lecturer-allocation-course-name value"></span></p></div>
        </div>
        <div class="row gap-row">
            <div class="col-md-6 truncate-wrap"><p class="card-text"><span class="label">LEVEL OF STUDY: </span> <span class="lecturer-allocation-level-of-study value"></span></p></div>
            <div class="col-md-6 truncate-wrap"><p class="card-text"><span class="label">SEMESTER: </span> <span class="lecturer-allocation-description-full value"></span></p></div>
        </div>
        <div class="row gap-row">
            <div class="col-md-6 truncate-wrap"><p class="card-text"><span class="label">GROUP: </span> <span class="lecturer-allocation-group value"></span></p></div>
            <div class="col-md-6 truncate-wrap"><p class="card-text"><span class="label">SEMESTER TYPE: </span> <span class="lecturer-allocation-semester-type value"></span></p></div>
        </div>
    </div>
 </div>
<!-- end course details -->

<!-- assign lecturers form -->
<div class="lec-assign-form-fields form-border">
    <?php 
        $form = ActiveForm::begin([
            'id' => 'allocate-external-lecturers-form',
            'action' => Url::to(['/allocation/allocate-request-lecturer']),
            'enableAjaxValidation' => false,
            'options' => ['enctype'=>'multipart/form-data']
        ]);
    ?>

    <div class="form-group select-status">
        <?php
            echo '<label>STATUS: </label>';
            echo Select2::widget([
                'id'=> 'external-lecturer-status',
                'name' => 'status',
                'data' => $statusList,
                'options' => [
                    'placeholder' => 'status',
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ]
            ]);
        ?>
    </div>

    <div class="form-group select-external-lecturers">
        <?php
            echo '<label>LECTURERS: </label>';
            echo Select2::widget([
                'id'=> 'external-lecturer-allocated',
                'name' => 'lecturers',
                'data' => $lecturers,
                'options' => [
                    'placeholder' => 'lecturers',
                    'multiple' => true
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ]
            ]);
        ?>
    </div>

    <div class="form-group remarks">
        <label for="external-lecturer-remarks">REMARKS: </label>
        <textarea class="form-control" id="external-lecturer-remarks" rows="3"></textarea>
    </div>

    <div class="form-group">
        <?= Html::submitButton('submit', ['id' => 'submit-external-lecturers', 'class'=>'btn'])?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<!-- end assign lecturers form -->

<?php Modal::end(); ?>
