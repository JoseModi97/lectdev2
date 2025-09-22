<?php

use app\models\DegreeProgramme;
use app\models\Semester;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\Semester $model */
/** @var yii\widgets\ActiveForm $form */

$academicYears = ArrayHelper::map(
    Semester::find()->select('ACADEMIC_YEAR')->distinct()->asArray()->all(),
    'ACADEMIC_YEAR',
    'ACADEMIC_YEAR'
);

// Fetch distinct degree codes from Semester model
$degreeCodes = ArrayHelper::map(
    DegreeProgramme::find()->select(['DEGREE_CODE', 'DEGREE_NAME'])->distinct()->asArray()->all(),
    'DEGREE_CODE',
    'DEGREE_NAME'
);
?>

<div class="semester-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'SEMESTER_ID')->textInput(['maxlength' => true]) ?>



    <?php
    echo $form->field($model, 'ACADEMIC_YEAR')->widget(Select2::class, [
        'data' => $degreeCodes,
        'options' => ['placeholder' => 'Select Academic Year...'],
        'pluginOptions' => [
            'allowClear' => false,
        ],
    ])->label('Academic Year');
    ?>


    <?= $form->field($model, 'DEGREE_CODE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'LEVEL_OF_STUDY')->textInput() ?>

    <?= $form->field($model, 'SEMESTER_CODE')->textInput() ?>

    <?= $form->field($model, 'INTAKE_CODE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'START_DATE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'END_DATE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'FIRST_SEMESTER')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'SEMESTER_NAME')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'CLOSING_DATE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ADMIN_USER')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'GROUP_CODE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'REGISTRATION_DEADLINE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'DESCRIPTION_CODE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'SESSION_TYPE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'DISPLAY_DATE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'REGISTRATION_DATE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'SEMESTER_TYPE')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>