<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use app\models\Semester;
use app\models\DegreeProgramme;
use app\models\LevelOfStudy;


use yii\db\Expression;
use yii\db\Query;

$currentAcademicYear = (date('Y') - 1) . '/' . date('Y');
/**
 * @var yii\web\View $this
 * @var app\models\search\SemesterSearch $model
 * @var yii\widgets\ActiveForm $form
 */
$levelsQuery = (new \yii\db\Query())
    ->select([
        'MUTHONI.LEVEL_OF_STUDY.LEVEL_OF_STUDY',
        'MUTHONI.LEVEL_OF_STUDY.NAME',
    ])
    ->distinct()
    ->from('MUTHONI.SEMESTERS')
    ->innerJoin(
        'MUTHONI.LEVEL_OF_STUDY',
        'MUTHONI.LEVEL_OF_STUDY.LEVEL_OF_STUDY = MUTHONI.SEMESTERS.LEVEL_OF_STUDY'
    )
    ->andFilterWhere([
        'MUTHONI.SEMESTERS.ACADEMIC_YEAR' => Yii::$app->request->get('SemesterSearch')['ACADEMIC_YEAR'] ?? null,
        'MUTHONI.SEMESTERS.DEGREE_CODE' => Yii::$app->request->get('SemesterSearch')['DEGREE_CODE'] ?? null,
    ])
    ->orderBy([
        'MUTHONI.LEVEL_OF_STUDY.LEVEL_OF_STUDY' => SORT_ASC,
        'MUTHONI.LEVEL_OF_STUDY.NAME' => SORT_ASC,
    ])
    ->all();

$yearLists = [];
if (!empty(Yii::$app->request->get('SemesterSearch')['ACADEMIC_YEAR']) && !empty(Yii::$app->request->get('SemesterSearch')['DEGREE_CODE'])) {
    $yearLists = ArrayHelper::map($levelsQuery, 'LEVEL_OF_STUDY', 'NAME');
}

$selectedLevel = Yii::$app->request->get('SemesterSearch')['LEVEL_OF_STUDY'] ?? '';
if (!empty($selectedLevel) && !array_key_exists($selectedLevel, $yearLists)) {
    $levelModel = LevelOfStudy::findOne(['LEVEL_OF_STUDY' => $selectedLevel]);
    if ($levelModel) {
        $yearLists[$selectedLevel] = $levelModel->NAME;
    }
}

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

$this->registerCss(
    "
    .help-block{
    color: red;
    }
    /* Loading state for Select2 when dependent data is being fetched */
    .select2-container.select2-loading{
        position: relative;
        opacity: 0.6;
    }
    .select2-container.select2-loading:after{
        content: '';
        position: absolute;
        right: 8px;
        top: 50%;
        width: 16px;
        height: 16px;
        margin-top: -8px;
        border: 2px solid rgba(0,0,0,0.2);
        border-top-color: #455492;
        border-radius: 50%;
        animation: spin-rotate 0.8s linear infinite;
    }
    @keyframes spin-rotate { to { transform: rotate(360deg); } }
    "
);
?>

<div class="semester-search container-fluid px-0">
    <?php $form = ActiveForm::begin([
        'action' => ['index', 'filtersFor' => $filter],
        'method' => 'get',
        'options' => ['id' => 'semester-search-form'],
    ]); ?>

    <div class="card-body row g-3">
        <div class="col-md-2">
            <?= $form->field($model, 'ACADEMIC_YEAR')->widget(Select2::class, [
                'data' => $academicYears,
                'options' => [
                    'placeholder' => 'Select Academic Year...',
                    'id' => 'academicYearSelect',
                    'required' => true,
                    'onchange' => <<< JS
                        if($('#degreeCodeSelect').val()){
                                $('#degreeCodeSelect').val(null).trigger('change');
                        }
                        $('#levelSelect').val(null).trigger('change');
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
                    'required' => true,
                    'onchange' => <<<JS
                        const academicYear = $('#academicYearSelect').val();
                        // Put dependent selects into loading state
                        const lvlSel = $('#levelSelect');
                        lvlSel.prop('disabled', true)
                              .html('<option>Loading...</option>')
                              .trigger('change.select2');
                        lvlSel.next('.select2-container').addClass('select2-loading');
                        $.post("/semester/semcode?DEGREE_CODE=" + $(this).val()+'&ACADEMIC_YEAR='+academicYear, function(data) {
                            let lvlOptions = '<option value="">-- Select Level --</option>';
                            data.levels.forEach(function(item) {
                                lvlOptions += '<option value="' + item.id + '">' + item.text + '</option>';
                            });
                            $("#levelSelect").html(lvlOptions).val(null).trigger("change.select2");
                            lvlSel.prop('disabled', false);
                            lvlSel.next('.select2-container').removeClass('select2-loading');

                        }).fail(function(){
                            // On error, remove loading state and keep selects disabled until user retries
                            lvlSel.next('.select2-container').removeClass('select2-loading');
                        });
                    JS,
                ],
                'pluginOptions' => ['allowClear' => false],
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'LEVEL_OF_STUDY')->widget(Select2::class, [
                'data' => $yearLists,
                'options' => [
                    'placeholder' => empty($yearLists) ? 'Select Degree Code first...' : 'Select Level of Study...',
                    'id' => 'levelSelect',
                    'required' => !empty($yearLists),
                    'disabled' => empty($yearLists),
                ],
                'pluginOptions' => ['allowClear' => false],
            ]) ?>
        </div>
        <div class="col-md-1">
            <?= Html::button('Reset', [
                'class' => 'btn btn-outline-secondary px-4 mt-4',
                'onclick' => 'window.location.href = "' . \yii\helpers\Url::to([
                    'index'
                ]) . '";'

            ]) ?>
        </div>
        <!-- Semester Type moved to GridView panel -->

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



    <?php ActiveForm::end(); ?>
</div>

<?php
// Auto-submit when Level of Study is selected (after parent filters are set)
$this->registerJs(<<<JS
(function(){
  function tryAutoSubmitSemesterSearch(){
    var ay = $('#academicYearSelect').val();
    var dc = $('#degreeCodeSelect').val();
    var ls = $('#levelSelect').val();
    if (ay && dc && ls) {
      var form = document.getElementById('semester-search-form');
      if (form) form.submit();
    }
  }
  // Do not auto-submit on degree change; only when level is selected
  $('#levelSelect').on('select2:select', tryAutoSubmitSemesterSearch);
})();
JS);
?>