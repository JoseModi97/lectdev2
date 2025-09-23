<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\CourseAnalysisFilter $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="course-analysis-filter-search">

    <?php $form = ActiveForm::begin([
        'action' => ['course-analysis'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'approvalLevel')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'restrictedTo')->hiddenInput()->label(false) ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'academicYear')->dropDownList([], ['prompt' => 'Select Academic Year']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'degreeCode')->dropDownList([], ['prompt' => 'Select Programme']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'levelOfStudy')->dropDownList([], ['prompt' => 'Select Level of Study']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'group')->dropDownList([], ['prompt' => 'Select Group']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'semester')->dropDownList([], ['prompt' => 'Select Semester']) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>