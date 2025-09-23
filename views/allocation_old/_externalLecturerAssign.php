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
    'size' => 'modal-md',
    'options' => ['data-backdrop'=>"static", 'data-keyboard'=>"false"],
]);
?>

<div class="content-loader" style="border-radius: 5px;"></div>

<!-- course details -->
<div class="card form-border">
    <div class="card-body">
        <p class="card-text"><span class="text-primary">MARKSHEET: </span> <span class="lecturer-allocation-marksheet-id"> </span></p>
        <div class="row">
            <div class="col-md-8 col-lg-8">
                <p class="card-text"><span class="text-primary"> COURSE NAME: </span> <span class="lecturer-allocation-course-name"></span> </p>
            </div>
            <div class="col-md-4 col-lg-4">
                <p class="card-text text-center"><span class="text-primary"> COURSE CODE: </span> <span class="lecturer-allocation-course-code"></span></p>
            </div>
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