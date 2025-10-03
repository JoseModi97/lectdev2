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

$currentAcademicYear = (date('Y') - 1) . '/' . date('Y');
/**
 * @var yii\web\View $this
 * @var app\models\search\SemesterSearch $model
 * @var yii\widgets\ActiveForm $form
 */
$data = (new \yii\db\Query())
    ->select([
        'MUTHONI.SEMESTERS.SEMESTER_CODE',
        'MUTHONI.SEMESTER_DESCRIPTIONS.SEMESTER_DESC',
        new \yii\db\Expression("MUTHONI.SEMESTERS.SEMESTER_CODE || ' - ' || MUTHONI.SEMESTER_DESCRIPTIONS.SEMESTER_DESC || ' - ' || MUTHONI.SEMESTERS.SEMESTER_TYPE AS SEMESTER_LABEL")
    ])
    ->distinct()
    ->from('MUTHONI.SEMESTERS')
    ->innerJoin(
        'MUTHONI.SEMESTER_DESCRIPTIONS',
        'MUTHONI.SEMESTER_DESCRIPTIONS.DESCRIPTION_CODE = MUTHONI.SEMESTERS.DESCRIPTION_CODE'
    )
    ->andFilterWhere([
        'MUTHONI.SEMESTERS.ACADEMIC_YEAR' => Yii::$app->request->get('SemesterSearch')['ACADEMIC_YEAR'] ?? null,
        'MUTHONI.SEMESTERS.DEGREE_CODE' => Yii::$app->request->get('SemesterSearch')['DEGREE_CODE'] ?? null,
        'MUTHONI.SEMESTERS.LEVEL_OF_STUDY' => Yii::$app->request->get('SemesterSearch')['LEVEL_OF_STUDY'] ?? null,

    ])
    ->all();

$levels = (new \yii\db\Query())
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

$semesterLists = '';
$yearLists = '';
if (!empty(Yii::$app->request->get('SemesterSearch')['ACADEMIC_YEAR']) && !empty(Yii::$app->request->get('SemesterSearch')['DEGREE_CODE'])) {
    $semesterLists = ArrayHelper::map($data, 'SEMESTER_CODE', 'SEMESTER_LABEL');
    $yearLists = ArrayHelper::map($levels, 'LEVEL_OF_STUDY', 'NAME');
}



$semester = Semester::find()
    ->where([
        'ACADEMIC_YEAR' => (Yii::$app->request->get('SemesterSearch')['ACADEMIC_YEAR'] ?? '')
    ])
    ->all();



$rows = (new Query())
    ->select([
        'MUTHONI.SEMESTERS.SEMESTER_CODE',
        'MUTHONI.SEMESTERS.SEMESTER_TYPE',
    ])
    ->distinct()
    ->from('MUTHONI.SEMESTERS')
    ->all();

// dd($model);
$semType = (new Query())
    ->select([
        'MUTHONI.SEMESTERS.SEMESTER_TYPE',
    ])
    ->distinct()
    ->from('MUTHONI.SEMESTERS')
    ->all();
$semesterTypeList = [
    'REGULAR' => 'REGULAR',
    'SUPPLEMENTARY' => 'SUPPLEMENTARY',
    '' => '',
];
$semesterList = ArrayHelper::map($rows, 'SEMESTER_CODE', 'SEMESTER_CODE');



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




$sem = ArrayHelper::map(Semester::find()->select(['SEMESTER_CODE'])->distinct()->all(), 'SEMESTER_CODE', 'SEMESTER_CODE');
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
                        if($('#degreeCodeSelect').val() || $('#semesterCodeSelect').val()){
                                $('#degreeCodeSelect').val(null).trigger('change');
                                $('#levelSelect').val(null).trigger('change').prop('disabled', true);
                                $('#semesterCodeSelect').val(null).trigger('change').prop('disabled', true);
                        }
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
                        const queryString = window.location.search;
                        const urlParams = new URLSearchParams(queryString);
                        const academicYear = $('#academicYearSelect').val();
                        // Put dependent selects into loading state
                        const lvlSel = $('#levelSelect');
                        const semSel = $('#semesterCodeSelect');

                        // Clear and disable semester and level selects
                        semSel.val(null).trigger('change').prop('disabled', true);
                        lvlSel.val(null).trigger('change').prop('disabled', false); // Enable level select

                        // Put level select into loading state
                        lvlSel.html('<option>Loading...</option>').trigger('change.select2');
                        lvlSel.next('.select2-container').addClass('select2-loading');

                        $.post("/semester/levels?DEGREE_CODE=" + $(this).val() + '&ACADEMIC_YEAR=' + academicYear, function(data) {
                            let lvlOptions = '<option value="">-- Select Level --</option>';
                            data.levels.forEach(function(item) {
                                lvlOptions += '<option value="' + item.id + '">' + item.text + '</option>';
                            });
                            lvlSel.html(lvlOptions).val(null).trigger("change.select2");
                        }).fail(function() {
                            // On failure, perhaps reset the level select to a default state
                            lvlSel.html('<option value="">-- Error Loading Levels --</option>').val(null).trigger("change.select2");
                        }).always(function() {
                            // Remove loading state from level select
                            lvlSel.next('.select2-container').removeClass('select2-loading');
                        });
                    JS,
                ],
                'pluginOptions' => ['allowClear' => false],
            ]) ?>
        </div>
       <div class="col-md-3">
       <?php
          echo $form->field($model, 'LEVEL_OF_STUDY')->widget(Select2::class, [
            'data' => $yearLists,
            'options' => [
                'placeholder' => 'Select Level of Study...',
                'id' => 'levelSelect',
                'required' => true,
                'onchange' => <<<JS
                const academicYear = $('#academicYearSelect').val();
                const degreeCode = $('#degreeCodeSelect').val();
                const semSel = $('#semesterCodeSelect');

                if ($(this).val()) {
                    semSel.prop('disabled', true).html('<option>Loading...</option>').trigger('change.select2');
                    semSel.next('.select2-container').addClass('select2-loading');

                    $.post("/semester/semcode?DEGREE_CODE=" + degreeCode + '&ACADEMIC_YEAR=' + academicYear + '&LEVEL_OF_STUDY=' + $(this).val(), function(data) {
                        let semOptions = '<option value="">-- Select Semester --</option>';
                        data.semesters.forEach(function(item) {
                            semOptions += '<option value="' + item.id + '">' + item.text + '</option>';
                        });
                        semSel.html(semOptions).val(null).trigger("change.select2");
                        semSel.prop('disabled', false);
                    }).fail(function() {
                        semSel.html('<option value="">-- Error Loading Semesters --</option>').val(null).trigger("change.select2");
                    }).always(function() {
                        semSel.next('.select2-container').removeClass('select2-loading');
                    });
                } else {
                    semSel.val(null).trigger('change').prop('disabled', true);
                }
            JS,

            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]);
        ?>
       </div>
        <!-- Level of Study moved to GridView panel -->
        <div class="col-md-3">
            <?= $form->field($model, 'SEMESTER_CODE')->widget(Select2::class, [
                'data' => $semesterLists,
                'options' => [
                    'placeholder' => 'Select Semester...',
                    'id' => 'semesterCodeSelect',
                    'required' => true,
                    'disabled' => empty($semesterLists),
                ],
                'pluginOptions' => ['allowClear' => true],
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
$this->registerJs(<<<JS
(function(){
  function tryAutoSubmitSemesterSearch(){
    var ay = $('#academicYearSelect').val();
    var dc = $('#degreeCodeSelect').val();
    var sc = $('#semesterCodeSelect').val();
    var lv = $('#levelSelect').val();

    if (ay && dc && sc && lv) {
      // Build params
      let params = new URLSearchParams(window.location.search);

      let displayedText = $('#semesterCodeSelect option:selected').text();
      let parts = displayedText.split(" - ").map(s => s.trim());



      // Preserve the other existing selects
      params.set("SemesterSearch[ACADEMIC_YEAR]", ay);
      params.set("SemesterSearch[DEGREE_CODE]", dc);
      params.set("SemesterSearch[SEMESTER_CODE]", sc);
      params.set("SemesterSearch[LEVEL_OF_STUDY]", lv);
      params.set("SemesterSearch[SEMESTER_DESC]", parts[1]);
      params.set("SemesterSearch[SEMESTER_TYPE]", parts[2]);
      // Reload with new params
      window.location.search = params.toString();
    }
  }

  // Attach handler to the semester code select field
  $('#semesterCodeSelect').on('change', tryAutoSubmitSemesterSearch);
})();
JS);
?>
