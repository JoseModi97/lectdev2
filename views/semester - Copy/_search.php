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
            'DEGREE_CODE' => $searchDegreeCodes,
            'FACUL_FAC_CODE' => $facCode
        ])
        ->asArray()
        ->all(),
    'DEGREE_CODE',
    'DEGREE'
);

// dd($degreeCodes);
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

<div class="semester-search">
    <div class="panel panel-success">
        <div class="panel-heading"><i class="fa fa-filter" aria-hidden="true"></i> <b>Filter courses</b></div>
        <div class="panel-body">
            <?php $form = ActiveForm::begin([
                'action' => ['index'],
                'method' => 'get',
                'options' => ['class' => 'form-horizontal'],
            ]); ?>

            <div class="form-group">
                <label class="control-label col-sm-2">Academic Year</label>
                <div class="col-sm-10">
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
                    ])->label(false) ?>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-sm-2">Degree Code</label>
                <div class="col-sm-10">
                    <?= $form->field($model, 'DEGREE_CODE')->widget(Select2::class, [
                        'data' => $degreeCodes,
                        'options' => [
                            'placeholder' => 'Select Degree Code...',
                            'id' => 'degreeCodeSelect',
                        ],
                        'pluginOptions' => ['allowClear' => false],
                    ])->label(false) ?>
                </div>
            </div>

            <div class="form-group" style="display:none;">
                <label class="control-label col-sm-2">Purpose</label>
                <div class="col-sm-10">
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
                    ])->label(false) ?>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('Reset', ['index'], ['class' => 'btn btn-default']) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
