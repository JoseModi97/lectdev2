<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 15-01-2021 12:18:33 
 * @modify date 15-01-2021 12:18:33 
 * @desc [description]
 */

/* @var string $deptCode */

use app\models\Department;
use app\models\EmpVerifyView;
use yii\helpers\Html;
use yii\bootstrap5\Modal;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

$facultyCodes = Yii::$app->params['newFaculties'];

$lecturers = EmpVerifyView::find()
    ->select(['PAYROLL_NO', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE'])
    ->where(['STATUS_DESC' => 'ACTIVE', 'JOB_CADRE' => 'ACADEMIC'])
    ->orderBy('SURNAME')
    ->all();

$lecturers = ArrayHelper::map($lecturers, 'PAYROLL_NO', function ($lecturer) {
    return $lecturer->EMP_TITLE . ' ' . $lecturer->SURNAME . ' ' . $lecturer->OTHER_NAMES;
});

$depts = Department::find()->select(['DEPT_CODE', 'DEPT_NAME'])->where(['DEPT_TYPE' => 'ACADEMIC'])
    ->andWhere(['NOT', ['DEPT_CODE' => $deptCode]])
    ->andWhere(['IN', 'FAC_CODE', $facultyCodes])
    ->all();

$depts = ArrayHelper::map($depts, 'DEPT_CODE', function ($dept) {
    return $dept->DEPT_NAME;
});

Modal::begin([
    'title' => '<b>Allocate or request course lecturer(s)</b>',
    'id' => 'allocate-course-lecturers-modal',
    'size' => 'modal-md',
    'options' => ['data-backdrop' => "static", 'data-keyboard' => "false"],
    'headerOptions' => ['style' => 'background-image: linear-gradient(#455492, #304186, #455492);'],
]);
?>

<div class="content-loader" style="border-radius: 5px;"></div>
<!-- course details -->
<div class="card" style="padding:10px; margin-bottom:10px; border: 1px solid #008cba;border-radius: 5px;">
    <div class="card-body">
        <p class="card-text"><span class="text-p">MARKSHEET: </span>
            <span class="lecturer-allocation-marksheet-id"> </span>
        </p>
        <div class="row">
            <div class="col-md-8 col-lg-8">
                <p class="card-text"><span class="text-primary"> COURSE NAME: </span>
                    <span class="lecturer-allocation-course-name"></span>
                </p>
            </div>
            <div class="col-md-4 col-lg-4">
                <p class="card-text text-center"><span class="text-primary"> COURSE CODE: </span>
                    <span class="lecturer-allocation-course-code"></span>
                </p>
            </div>
        </div>
    </div>
</div>
<!-- end course details -->

<!-- assign lecturers form -->
<div class="lec-assign-form-fields form-border">
    <?php
    $form = ActiveForm::begin([
        'id' => 'allocate-course-lecturers-form',
        // 'action' => Url::to(['/allocation/allocate-request-lecturer']),
        'action' => '',
        'enableAjaxValidation' => false,
        'options' => ['enctype' => 'multipart/form-data']
    ]);
    ?>
    <!-- <input type="text" id="request-id" value="">
    <input type="text" id="marksheet-id" value="">
    <input type="text" id="course-type" value=""> -->
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" checked id="lecturer-internal" name="lecturer-internal"
            value="internal">
        <label class="form-check-label" for="lecturer-internal">Allocate lecturer from my department</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" id="lecturer-external" name="lecturer-external" value="external">
        <label for="lecturer-external">Request for lecturer from another department</label>
    </div>
    <br>

    <div class="form-group select-lecturers">
        <?php
        echo '<label>LECTURERS: </label>';
        echo Select2::widget([
            'id' => 'lecturer-assigned',
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

    <div class="form-group select-departments">
        <?php
        echo '<label>DEPARTMENTS: </label>';
        echo Select2::widget([
            'id' => 'service-dept',
            'name' => 'department',
            'data' => $depts,
            'options' => [
                'placeholder' => 'department',
            ],
            'pluginOptions' => [
                'allowClear' => true
            ]
        ]);
        ?>
    </div>

    <div class="form-group">
        <?=
        Html::button('submit', [
            'id' => 'submit-internal-lecturers-or-requests',
            'class' => 'btn text-white my-2',
            'style' => "background-image: linear-gradient(#455492, #304186, #455492)"
        ]);
        ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<!-- end assign lecturers form -->
<?php Modal::end(); ?>