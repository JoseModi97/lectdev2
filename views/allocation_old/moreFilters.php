<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var app\models\CourseAllocationFilter $filter
 */
?>

<div class="semester-search container-fluid px-0">
    <?php $form = ActiveForm::begin([
        'action' => Url::to(['/allocation/give']),
        'method' => 'get',
    ]); ?>

    <?= $form->field($filter, 'degreeCode')->hiddenInput()->label(false) ?>
    <?= $form->field($filter, 'group')->hiddenInput()->label(false) ?>
    <?= $form->field($filter, 'levelOfStudy')->hiddenInput()->label(false) ?>
    <?= $form->field($filter, 'semester')->hiddenInput()->label(false) ?>
    <?= $form->field($filter, 'purpose')->hiddenInput()->label(false) ?>

    <div class="card shadow-sm rounded-0">
        <div class="card-header" style="background-image: linear-gradient(#455492, #304186, #455492);">
            <h5 class="m-0 float-start text-white">More Filters</h5>
        </div>
        <div class="card-body row g-3">
            <div class="col-md-4">
                <?= $form->field($filter, 'academicYear')->widget(Select2::class, [
                    'data' => Yii::$app->params['academicYears'] ?? [],
                    'options' => ['placeholder' => 'Select Academic Year...'],
                    'pluginOptions' => ['allowClear' => true],
                ]) ?>
            </div>
            <div class="col-md-4">
                <?php $form->field($filter, 'courseCode') ?>
            </div>
            <div class="col-md-4">
                <?php $form->field($filter, 'courseName') ?>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-start gap-2">
            <?= Html::submitButton('Search', [
                'class' => 'btn text-white px-4',
                'style' => "background-image: linear-gradient(#455492, #304186, #455492);",
            ]) ?>
            <?= Html::a('Reset', ['/allocation/give', 'CourseAllocationFilter' => [
                'academicYear' => $filter->academicYear,
                'degreeCode' => $filter->degreeCode,
                'group' => $filter->group,
                'levelOfStudy' => $filter->levelOfStudy,
                'semester' => $filter->semester,
                'purpose' => $filter->purpose,
                'courseCode' => '',
                'courseName' => '',
            ]], ['class' => 'btn btn-outline-secondary px-4']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>