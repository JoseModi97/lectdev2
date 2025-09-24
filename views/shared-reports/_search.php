<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\CourseAnalysisFilter $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="course-analysis-filter-search">

    <?php $form = ActiveForm::begin([
        'id' => 'course-analysis-filters-form',
        'action' => ['course-analysis'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'approvalLevel')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'restrictedTo')->hiddenInput()->label(false) ?>

    <div class="row g-3">
        <div class="col-md-4">
            <?= $form->field($model, 'academicYear')->dropDownList([], [
                'prompt' => 'Select Academic Year',
                'id' => 'academic-year',
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'degreeCode')->dropDownList([], [
                'prompt' => 'Select Programme',
                'id' => 'programme',
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'levelOfStudy')->dropDownList([], [
                'prompt' => 'Select Level of Study',
                'id' => 'level-of-study',
            ]) ?>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <?= $form->field($model, 'group')->dropDownList([], [
                'prompt' => 'Select Group',
                'id' => 'group',
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'semester')->dropDownList([], [
                'prompt' => 'Select Semester',
                'id' => 'semester',
            ]) ?>
        </div>
    </div>

    <div class="form-group mt-2">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
