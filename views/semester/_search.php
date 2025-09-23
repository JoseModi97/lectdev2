<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use app\models\Semester;
use app\models\DegreeProgramme;
use app\models\LevelOfStudy;
use app\models\SemesterDescription;


use yii\db\Expression;
use yii\db\Query;

/**
 * @var yii\web\View $this
 * @var app\models\search\SemesterSearch $model
 * @var yii\widgets\ActiveForm $form
 */











$semester = Semester::find()
    ->where([
        'ACADEMIC_YEAR' => (Yii::$app->request->get('SemesterSearch')['ACADEMIC_YEAR'] ?? '')
    ])
    ->all();



$rows = (new Query())
    ->select([
        'MUTHONI.SEMESTERS.SEMESTER_CODE',
    ])
    ->distinct()
    ->from('MUTHONI.SEMESTERS')
    ->all();

// dd($model);

$semesterList = ArrayHelper::map($rows, 'SEMESTER_CODE', 'SEMESTER_CODE');

// dd($semesterList);

$searchDegreeCodes = array_unique(array_column($semester, 'DEGREE_CODE'));

$filter = Yii::$app->request->get('filtersFor') ?? Yii::$app->request->get('SemesterSearch')['purpose'] ?? '';

$model->purpose = $filter;
$model->DEGREE_CODE = Yii::$app->request->get('SemesterSearch')['DEGREE_CODE'] ?? '';
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

$levels = ArrayHelper::map(LevelOfStudy::find()->all(), 'LEVEL_OF_STUDY', 'NAME');

$semesterCodes = ArrayHelper::map(
    Semester::find()
        ->select(['MUTHONI.SEMESTERS.DESCRIPTION_CODE', 'MUTHONI.SEMESTERS.SEMESTER_CODE', 'MUTHONI.SEMESTER_DESCRIPTIONS.SEMESTER_DESC'])
        ->leftJoin('MUTHONI.SEMESTER_DESCRIPTIONS', 'MUTHONI.SEMESTER_DESCRIPTIONS.DESCRIPTION_CODE = MUTHONI.SEMESTERS.DESCRIPTION_CODE')
        ->distinct()
        ->asArray()
        ->all(),
    'DESCRIPTION_CODE',
    function ($model) {
        return $model['SEMESTER_CODE'] . ' - ' . $model['SEMESTER_DESC'];
    }
);




$sem = ArrayHelper::map(Semester::find()->select(['SEMESTER_CODE'])->distinct()->all(), 'SEMESTER_CODE', 'SEMESTER_CODE')

?>

<div class="semester-search container-fluid px-0">
    <?php $form = ActiveForm::begin([
        'action' => ['index', 'filtersFor' => $filter],
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
        <div class="col-md-6">
            <?= $form->field($model, 'LEVEL_OF_STUDY')->widget(Select2::class, [
                'data' => $levels,
                'options' => ['placeholder' => 'Select Level of Study...'],
                'pluginOptions' => ['allowClear' => true],
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'SEMESTER_CODE') ?>
        </div>

        <div class="col-md-6">
            <?php $form->field($model, 'courseName') ?>
        </div>

        <div class="col-md-6" style="display: none;">
            <?= $form->field($model, 'purpose')->widget(Select2::class, [
                'data' => [
                    'nonSuppCourses' => 'Non Supplementary Courses',
                    'suppCourses' => 'Supplementary Courses',
                    'requestedCourses' => 'Requested Courses',
                    'serviceCourses' => 'Service Courses',
                ],
                'options' => [
                    'placeholder' => 'Select se...',
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
            'onclick' => 'window.location.href = "' . \yii\helpers\Url::to(['index?filtersFor=' . $filter]) . '";'
        ]) ?>
    </div>


    <?php ActiveForm::end(); ?>
</div>