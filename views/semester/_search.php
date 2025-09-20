<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use app\models\Semester;
use app\models\DegreeProgramme;
use yii\db\Expression;

/**
 * @var yii\web\View $this
 * @var app\models\search\SemesterSearch $model
 * @var yii\widgets\ActiveForm $form
 */

$semester = Semester::find()->where(['ACADEMIC_YEAR' => Yii::$app->request->get('SemesterSearch')['ACADEMIC_YEAR'] ?? ''])->all();

$searchDegreeCodes = array_unique(array_column($semester, 'DEGREE_CODE'));



$degreeCodes = ArrayHelper::map(
    DegreeProgramme::find()
        ->select([
            'DEGREE_CODE',
            'DEGREE_NAME',
            new Expression("(DEGREE_CODE || ' - ' || DEGREE_NAME) AS DEGREE"),
        ])
        ->distinct()
        ->where([
            'FACUL_FAC_CODE' => $facCode
        ])
        ->orderBy(['DEGREE_CODE' => SORT_ASC])
        ->asArray()
        ->all(),
    'DEGREE_CODE',
    'DEGREE'
);

$deg = DegreeProgramme::find()
    ->select([
        'DEGREE_CODE',
        'DEGREE_NAME',
        new Expression("(DEGREE_CODE || ' - ' || DEGREE_NAME) AS DEGREE"),
    ])
    ->distinct()
    ->where([
        'FACUL_FAC_CODE' => $facCode
    ])
    ->orderBy(['DEGREE_CODE' => SORT_ASC])
    ->asArray();



$academicYear =  [
    '2024/2025',
    '2023/2024',
    '2022/2023',
    '2021/2022',
    '2020/2021',
    '2019/2020'
];

// Fetch distinct academic years
$academicYears = ArrayHelper::map(
    Semester::find()
        ->select('ACADEMIC_YEAR')
        ->distinct()
        ->where([
            'ACADEMIC_YEAR' => $academicYear
        ])
        ->orderBy([
            'ACADEMIC_YEAR' => SORT_DESC
        ])
        ->asArray()
        ->all(),
    'ACADEMIC_YEAR',
    'ACADEMIC_YEAR'
);
$model->purpose = 'nonSuppCourses';
?>

<div class="semester-search container-fluid px-0">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="card-body row g-3">
        <div class="col-md-6">
            <?= $form->field($model, 'ACADEMIC_YEAR')->widget(Select2::class, [
                'data' => $academicYears,
                'options' => [
                    'placeholder' => 'Select Academic Year...',
                    'onchange' => <<<JS
                        $.post("/semester/degcode?ACADEMIC_YEAR=" + $(this).val(), function(data) {
                            console.log(data);
                            $("select#degreeCodeSelect").html(data).val(null).trigger("change");
                        });
                    JS,
                ],
                'pluginOptions' => ['allowClear' => true],
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'DEGREE_CODE')->widget(Select2::class, [
                'data' => $degreeCodes,
                'options' => [
                    'placeholder' => 'Select Degree Code...',
                    'id' => 'degreeCodeSelect',
                ],
                'pluginOptions' => ['allowClear' => false],
            ]) ?>
        </div>
        <div class="col-md-6" style="display:none;">
            <?= $form->field($model, 'purpose')->widget(Select2::class, [
                'data' => [
                    'nonSuppCourses' => 'Non Supplementary Courses',
                    'suppCourses' => 'Supplementary Courses',
                    'requestedCourses' => 'Requested Courses',
                    'serviceCourses' => 'Service Courses',
                ],
                'options' => [
                    'placeholder' => 'Select Purpose...',
                ],
                'pluginOptions' => ['allowClear' => false],
            ]) ?>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-start gap-2">
        <?= Html::submitButton('Search', [
            'class' => 'btn text-white px-4',
            'style' => "background-image: linear-gradient(#455492, #304186, #455492)",
        ]) ?>
        <?= Html::button('Reset', [
            'class' => 'btn btn-outline-secondary px-4',
            'onclick' => 'window.location.href = "' . \yii\helpers\Url::to(['index']) . '";'
        ]) ?>
    </div>


    <?php ActiveForm::end(); ?>
</div>