<?php

use app\models\Course;
use app\models\CourseAllocationFilter;
use app\models\DegreeProgramme;
use app\models\Group;
use app\models\LevelOfStudy;
use app\models\MarksheetDef;
use app\models\search\AllocationRequestsSearchNew;
use app\models\search\MarksheetDefAllocationSearchNew;
use app\models\Semester;

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\db\Query;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use app\components\BreadcrumbHelper;
use app\models\CourseAssignment;
use app\models\EmpVerifyView;

$searchAcademicYear = Yii::$app->request->get('SemesterSearch')['ACADEMIC_YEAR'] ?? '';
$searchDegreeCode = Yii::$app->request->get('SemesterSearch')['DEGREE_CODE'] ?? '';
$semCodeSearch = Yii::$app->request->get('SemesterSearch')['SEMESTER_CODE'] ?? '';
$semTypeSearch = Yii::$app->request->get('SemesterSearch')['SEMESTER_TYPE'] ?? '';
$semLevelOfStudySearch = Yii::$app->request->get('SemesterSearch')['LEVEL_OF_STUDY'] ?? '';

$this->registerCss(
    <<<CSS
    .bg-primary{
        color: white;
    }
    CSS
);


/** @var yii\web\View $this */
/** @var app\models\search\SemesterSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var bool $searchPerformed */
// dd(array_column($dataProvider->getModels(), 'ACADEMIC_YEAR'));
$code = $deptCode;
// dd($dataProvider->query->createCommand()->rawSql);
echo BreadcrumbHelper::generate([
    ['label' => 'Programme Timetables']
]);
$data = $dataProvider->query->all();

// dd($dataProvider->query->createCommand()->getRawSql());

$courseCodeFilter = [];
$courseNameFilter = [];

foreach ($data as $model) {
    $course = $model->course ?? null;
    if ($course === null) {
        continue;
    }

    $code = trim((string) ($course->COURSE_CODE ?? ''));
    $name = trim((string) ($course->COURSE_NAME ?? ''));
    $codename = ($code !== '' && $name !== '') ? $code . ' - ' . $name : $code;

    if ($code !== '') {
        // Key = COURSE_CODE, Value = "COURSE_CODE - COURSE_NAME"
        $courseCodeFilter[$code] = $codename;
    }

    if ($name !== '') {
        $courseNameFilter[$name] = $name;
    }
}

// if (empty($searchAcademicYear) || empty($searchDegreeCode) || empty($semCodeSearch) || empty($semLevelOfStudySearch)) {


//     $this->registerCss(
//         <<<CSS
//     #w1-filters{
//         display: none;
//     }
//     CSS
//     );
// }



$selectedCourseCode = trim((string) ($searchModel->courseCode ?? ''));
if ($selectedCourseCode !== '' && !array_key_exists($selectedCourseCode, $courseCodeFilter)) {
    $courseCodeFilter[$selectedCourseCode] = $selectedCourseCode;
}

$selectedCourseName = trim((string) ($searchModel->courseName ?? ''));
if ($selectedCourseName !== '' && !array_key_exists($selectedCourseName, $courseNameFilter)) {
    $courseNameFilter[$selectedCourseName] = $selectedCourseName;
}

if (!empty($courseCodeFilter)) {
    ksort($courseCodeFilter, SORT_NATURAL | SORT_FLAG_CASE);
}
if (!empty($courseNameFilter)) {
    asort($courseNameFilter, SORT_NATURAL | SORT_FLAG_CASE);
}

$lecturersList = ArrayHelper::map(
    EmpVerifyView::find()->where(['DEPT_CODE' => $deptCode, 'STATUS_DESC' => 'ACTIVE', 'JOB_CADRE' => 'ACADEMIC'])->orderBy('SURNAME')->all(),
    'PAYROLL_NO',
    function ($model) {
        return $model->SURNAME . ' ' . $model->OTHER_NAMES;
    }
);
$filtertype = [
    'nonSuppCourses' => 'Non Supplementary Courses',
    'suppCourses' => 'Supplementary Courses',
    'requestedCourses' => 'Requested Courses',
    'serviceCourses' => 'Service Courses',
];

$actionColumn = [
    'class' => 'kartik\grid\ActionColumn',
    'width' => '25%',
    'template' => '{allocate}  {manage}  {remove}',
    'contentOptions' => [
        'style' => 'white-space: nowrap; width: 30%;',
        'class' => 'text-center align-middle'
    ],

    'buttons' => [
        'allocate' => function ($url, $model, $key) {
            return Html::a('<i class="fas fa-user-plus text-success"></i> <span class="text-dark">Allocate / Request</span>', '#', [
                'title' => 'Allocate / Request',
                'class' => 'assign-lecturer text-decoration-none',
                'data-id' => 'NULL',
                'data-marksheetid' => $model->MRKSHEET_ID,
                'data-type' => 'departmental',
            ]);
        },
        'manage' => function ($url, $model, $key) {
            return Html::a(
                '<i class="fas fa-tasks text-primary"></i> <span class="text-dark">Manage</span>',
                Url::to(['/allocation/allocated-lecturer-render', 'marksheetId' => $model->MRKSHEET_ID, 'purpose' => 'manage']),
                [
                    'title' => 'Manage allocated lecturer(s)',
                    'class' => 'manage-lecturer text-decoration-none',
                ]
            );
        },
        'remove' => function ($url, $model, $key) {
            return Html::a('<i class="fas fa-trash text-danger"></i> <span class="text-dark">Remove</span>', Url::to(['/allocation/allocated-lecturer-render', 'marksheetId' => $model->MRKSHEET_ID, 'purpose' => 'remove']), [
                'title' => 'Remove allocated lecturer(s)',
                'class' => 'remove-lecturer text-decoration-none',
            ]);
        },
    ]
];

$this->title = 'Semesters';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="semester-index">
    <div class="card shadow-sm rounded-0 w-100">
        <?php
        $semesterSearchHdr = Yii::$app->request->get('SemesterSearch', []);
        $academicYearHdr = $semesterSearchHdr['ACADEMIC_YEAR'] ?? '';
        $degreeCodeHdr   = $semesterSearchHdr['DEGREE_CODE'] ?? '';
        $degreeNameHdr   = '';
        if (!empty($degreeCodeHdr)) {
            $degHdr = \app\models\DegreeProgramme::findOne(['DEGREE_CODE' => $degreeCodeHdr]);
            $degreeNameHdr = $degHdr->DEGREE_NAME ?? '';
        }
        $levelHdr = null;
        if (!empty($semesterSearchHdr)) {
            $levelHdr = \app\models\LevelOfStudy::findOne(['LEVEL_OF_STUDY' => $semesterSearchHdr['LEVEL_OF_STUDY'] ?? '']);
        }
        ?>
        <div class="card-header text-white fw-bold d-flex align-items-center justify-content-between" style="background-image: linear-gradient(#455492, #304186, #455492);">
            <?php if (!empty($academicYearHdr) || !empty($degreeCodeHdr) || !empty($degreeNameHdr)) : ?>
                <?= Html::encode($academicYearHdr) ?>
                <?= Html::encode($degreeCodeHdr) ?>
                <?= Html::encode($degreeNameHdr) ?>
                <div class="small fw-normal"><?= Html::encode($levelHdr->NAME ?? '') ?>
                    SEMESTER <?= Html::encode($semesterSearchHdr['SEMESTER_CODE'] ?? '') ?></div>
            <?php else: ?>
                Academic Filters
            <?php endif; ?>
            <div class="d-flex align-items-center ms-2">
                <span id="filter-loading-indicator" class="me-2 d-none" aria-hidden="true"></span>
                <span id="filter-loading-text" class="d-none">Loading...</span>
            </div>
        </div>
        <div class=" card-body row g-3">
            <?php echo $this->render('_search', [
                'model' => $searchModel,
                'facCode' => $facCode
            ]); ?>
        </div>

    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax' => false,

        // Add panel with Level of Study and Semester Type filters
        'panel' => (function () use ($searchModel) {
            $semesterSearch = Yii::$app->request->get('SemesterSearch', []);
            $academicYear = $semesterSearch['ACADEMIC_YEAR'] ?? '';
            $degreeCode   = $semesterSearch['DEGREE_CODE'] ?? '';

            // Build dynamic Level of Study options (same logic as in _search.php)
            $levelsQuery = (new Query())
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
                    'MUTHONI.SEMESTERS.ACADEMIC_YEAR' => $academicYear ?: null,
                    'MUTHONI.SEMESTERS.DEGREE_CODE' => $degreeCode ?: null,
                ])
                ->orderBy([
                    'MUTHONI.LEVEL_OF_STUDY.LEVEL_OF_STUDY' => SORT_ASC,
                    'MUTHONI.LEVEL_OF_STUDY.NAME' => SORT_ASC,
                ])
                ->all();

            $yearLists = [];
            if (!empty($academicYear) && !empty($degreeCode)) {
                $yearLists = \yii\helpers\ArrayHelper::map($levelsQuery, 'LEVEL_OF_STUDY', 'NAME');
            }
            // Ensure currently selected Level Of Study remains selectable/searchable
            $selectedLevel = $semesterSearch['LEVEL_OF_STUDY'] ?? '';
            if (!empty($selectedLevel) && !array_key_exists($selectedLevel, $yearLists)) {
                $lvl = \app\models\LevelOfStudy::findOne(['LEVEL_OF_STUDY' => $selectedLevel]);
                if ($lvl) {
                    $yearLists[$selectedLevel] = $lvl->NAME;
                }
            }

            // Build dynamic Group options based on selected Academic Year / Degree (and Semester Code if provided)
            $groupsQuery = (new Query())
                ->select([
                    'MUTHONI.GROUPS.GROUP_CODE',
                    'MUTHONI.GROUPS.GROUP_NAME',
                ])
                ->distinct()
                ->from('MUTHONI.SEMESTERS')
                ->innerJoin(
                    'MUTHONI.GROUPS',
                    'MUTHONI.GROUPS.GROUP_CODE = MUTHONI.SEMESTERS.GROUP_CODE'
                )
                ->andFilterWhere([
                    'MUTHONI.SEMESTERS.ACADEMIC_YEAR' => $academicYear ?: null,
                    'MUTHONI.SEMESTERS.DEGREE_CODE' => $degreeCode ?: null,
                    'MUTHONI.SEMESTERS.SEMESTER_CODE' => $semesterSearch['SEMESTER_CODE'] ?? null,
                ])
                ->orderBy([
                    'MUTHONI.GROUPS.GROUP_NAME' => SORT_ASC,
                ])
                ->all();

            $groupOptions = [];
            if (!empty($academicYear) && !empty($degreeCode)) {
                $groupOptions = \yii\helpers\ArrayHelper::map($groupsQuery, 'GROUP_CODE', 'GROUP_NAME');
            }
            // Ensure currently selected Group remains selectable/searchable
            $selectedGroup = $semesterSearch['GROUP_CODE'] ?? '';
            if (!empty($selectedGroup) && !array_key_exists($selectedGroup, $groupOptions)) {
                $grp = \app\models\Group::findOne(['GROUP_CODE' => $selectedGroup]);
                if ($grp) {
                    $groupOptions[$selectedGroup] = $grp->GROUP_NAME;
                }
            }

            $semesterTypeOptions = [
                'SUPPLEMENTARY' => 'SUPPLEMENTARY',
                'TEACHING' => 'TEACHING',
            ];

            // Disable panel filters until a search with required parents is made
            // Require both Academic Year and Degree Code present in the query string
            $panelDisabled = (empty($academicYear) || empty($degreeCode));

            ob_start();
            $form = ActiveForm::begin([
                'action' => ['index'],
                'method' => 'get',
                'options' => ['class' => 'mb-0'],
            ]);

            // Preserve other SemesterSearch params when submitting from panel
            echo Html::hiddenInput('SemesterSearch[ACADEMIC_YEAR]', $academicYear);
            echo Html::hiddenInput('SemesterSearch[DEGREE_CODE]', $degreeCode);
            echo Html::hiddenInput('SemesterSearch[SEMESTER_CODE]', $semesterSearch['SEMESTER_CODE'] ?? '');
            echo Html::hiddenInput('filtersFor', Yii::$app->request->get('filtersFor', ''));

            echo '<div class="row g-3 align-items-end">';
            echo '<div class="col-md-6">';
            echo $form->field($searchModel, 'LEVEL_OF_STUDY')->widget(Select2::class, [
                'data' => $yearLists,
                'options' => [
                    'placeholder' => $panelDisabled ? 'Select Degree Code first...' : 'Select Level of Study...',
                    'id' => 'levelSelect',
                    'required' => !$panelDisabled,
                    'disabled' => $panelDisabled,
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
                'pluginEvents' => [
                    'select2:select' => 'function (e) { this.form.submit(); }',
                    'select2:clear'  => 'function (e) { this.form.submit(); }',
                ],
            ]);
            echo '</div>';
            echo '<div class="col-md-6">';
            echo $form->field($searchModel, 'GROUP_CODE')->widget(Select2::class, [
                'data' => $groupOptions,
                'options' => [
                    'placeholder' => $panelDisabled ? 'Select Degree Code first...' : 'Select Group...',
                    'id' => 'groupSelect',
                    'required' => false,
                    'disabled' => $panelDisabled,
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
                'pluginEvents' => [
                    'select2:select' => 'function (e) { this.form.submit(); }',
                    'select2:clear'  => 'function (e) { this.form.submit(); }',
                ],
            ]);
            echo '</div>';
            echo '<div class="col-md-6">';
            // echo $form->field($searchModel, 'SEMESTER_TYPE')->widget(Select2::class, [
            //     'data' => $semesterTypeOptions,
            //     'options' => [
            //         'placeholder' => $panelDisabled ? 'Select Degree Code first...' : 'Select Semester Type...',
            //         'id' => 'semesterTypeSelect',
            //         'required' => !$panelDisabled,
            //         'disabled' => $panelDisabled,
            //     ],
            //     'pluginOptions' => [
            //         'allowClear' => true,
            //     ],
            //     'pluginEvents' => [
            //         'select2:select' => 'function (e) { this.form.submit(); }',
            //     ],
            // ]);
            echo '</div>';
            echo '</div>';

            ActiveForm::end();
            $before = ob_get_clean();

            // Build panel heading preserving title and description
            $degreeName = '';
            if (!empty($degreeCode)) {
                $degree = \app\models\DegreeProgramme::findOne(['DEGREE_CODE' => $degreeCode]);
                $degreeName = $degree->DEGREE_NAME ?? '';
            }
            $levelModel = null;
            if (!empty($semesterSearch)) {
                $levelModel = \app\models\LevelOfStudy::findOne(['LEVEL_OF_STUDY' => $semesterSearch['LEVEL_OF_STUDY'] ?? '']);
            }
            ob_start();
            echo '<h5>'
                . Html::encode($academicYear) . ' | '
                . Html::encode($degreeCode) . ' '
                . Html::encode($degreeName)
                . Html::encode($levelModel->NAME ?? '') . ' | SEMESTER '
                . Html::encode($semesterSearch['SEMESTER_CODE'] ?? '')
                . '</h5>';
            $heading = ob_get_clean();

            return [
                'heading' => false,
                'type' => 'default',
                'before' => $before,
            ];
        })(),

        'responsiveWrap' => false,
        'condensed' => true,
        'hover' => true,
        'striped' => false,
        'bordered' => true,
        // Keep the number-of-items summary visible even without a panel heading
        'summary' => '<span class="badge bg-light text-dark border">Total {totalCount} item(s)</span>',
        'summaryOptions' => [
            'class' => 'd-block text-end mb-2',
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // [
            //     'label' => 'Level of Study',
            //     'value' => function ($model) {
            //         $semDesc = $model->semester->semesterDescription;
            //         return $model->semester->levelOfStudy->NAME . '  |  Semester ' . $model->semester->SEMESTER_CODE . '  |  ' . $semDesc->SEMESTER_DESC;
            //     },
            //     'group' => true,
            //     'groupedRow' => true,
            //     'contentOptions' => [
            //         'style' => 'background-image: linear-gradient(#455492, #304186, #455492); color: white;padding-top: 10px; padding-bottom: 10px;',
            //         'class' => 'py-3'
            //     ],
            // ],
            [
                'label' => 'Level of Study',
                'value' => function ($model) {
                    $semDesc = $model->semester->semesterDescription ?? null;
                    $semType = $model->semester->SEMESTER_TYPE ?? '';
                    $groupName = $model->semester->group->GROUP_NAME ?? '';

                    $parts = [
                        $model->semester->ACADEMIC_YEAR,
                        $model->semester->levelOfStudy->NAME,
                        'Semester ' . ($model->semester->SEMESTER_CODE ?? ''),
                        ($semDesc->SEMESTER_DESC ?? ''),
                        ($semType !== '' ? $semType : ''),
                        ($groupName !== '' ? $groupName : ''), // group code last
                    ];

                    $parts = array_filter($parts, fn($v) => $v !== '' && $v !== null);

                    // add pipes between items
                    return implode(' | ', $parts);
                },
                'group' => true,
                'groupedRow' => true,
                'contentOptions' => [
                    'style' => 'background-image: linear-gradient(#455492, #304186, #455492); color: white;padding-top: 10px; padding-bottom: 10px;',
                    'class' => 'py-3'
                ],
            ],

            // [
            //     'label' => 'Semester Type',
            //     'attribute' => 'SEMESTER_TYPE',
            //     'value' => 'semester.SEMESTER_TYPE',
            //     'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
            //     'filter' => [
            //         'SUPPLEMENTARY' => 'SUPPLEMENTARY',
            //         'TEACHING' => 'TEACHING',
            //     ],
            //     'filterWidgetOptions' => [
            //         'pluginOptions' => [
            //             'allowClear' => true,
            //             'placeholder' => 'Select Semester Type...',
            //         ],
            //     ],
            // ],

            [
                'attribute' => 'courseCode',
                'label' => 'Course Code',
                'value' => 'course.COURSE_CODE',
                'value' => function ($model) {
                    $courseCode = $model->course->COURSE_CODE ?? '';
                    $courseName = $model->course->COURSE_NAME ?? '';
                    return $courseCode . ' - ' . $courseName;
                },
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $courseCodeFilter,
                'filterWidgetOptions' => [
                    'options' => ['placeholder' => 'Any course'],
                    'initValueText' => $selectedCourseCode,
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filterInputOptions' => ['placeholder' => 'Any course'],
            ],
            // [
            //     'attribute' => 'courseName',
            //     'label' => 'Course Name',
            //     'value' => 'course.COURSE_NAME',
            //     'filterType' => GridView::FILTER_SELECT2,
            //     'filter' => $courseNameFilter,
            //     'filterWidgetOptions' => [
            //         'options' => ['placeholder' => 'Any course name'],
            //         'initValueText' => $selectedCourseName,
            //         'pluginOptions' => ['allowClear' => true],
            //     ],
            //     'filterInputOptions' => ['placeholder' => 'Any course name'],
            // ],

            // [
            //     'label' => 'Group Name',
            //     'value' => function ($model) {
            //         return $model->semester->group->GROUP_NAME ?? '';
            //     },
            // ],
            [
                'attribute' => 'PAYROLL_NO',
                'header' => 'Assigned Lecturer(s)',
                'format' => 'raw',
                'vAlign' => 'middle',
                'width' => '25%',
                'value' => function ($d) {
                    $assignments = CourseAssignment::find()
                        ->select(['PAYROLL_NO'])
                        ->where(['MRKSHEET_ID' => $d->MRKSHEET_ID])
                        ->all();

                    if (empty($assignments)) {
                        return '<span class="badge bg-secondary fs-6 py-1">Not assigned</span>';
                    }

                    $output = '';
                    $courseLeaderFound = false;

                    foreach ($assignments as $assignment) {
                        $lecturer = EmpVerifyView::find()
                            ->select(['PAYROLL_NO', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE'])
                            ->where(['PAYROLL_NO' => $assignment->PAYROLL_NO])
                            ->one();

                        if (!$lecturer) {
                            continue;
                        }

                        $lecturerName = trim(($lecturer->EMP_TITLE ? $lecturer->EMP_TITLE . ' ' : '') .
                            $lecturer->SURNAME . ' ' . $lecturer->OTHER_NAMES);

                        // Check if this lecturer is the course leader
                        $isLeader = MarksheetDef::find()
                            ->where([
                                'MRKSHEET_ID' => $d->MRKSHEET_ID,
                                'PAYROLL_NO' => $lecturer->PAYROLL_NO
                            ])
                            ->exists();

                        if ($isLeader && !$courseLeaderFound) {
                            $courseLeaderFound = true;
                            $output .= '<div class="mb-1 text-dark"><i class="fas fa-user-tie"></i> ' . Html::encode($lecturerName) . ' <span class="text-success" style="font-weight: bold">(Leader)</span></div>';
                        } else {
                            $output .= '<div class="mb-1 text-dark"><i class="fas fa-user"></i> ' . Html::encode($lecturerName) . '</div>';
                        }
                    }

                    return $output;
                },
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $lecturersList,
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filterInputOptions' => ['placeholder' => 'Any lecturer'],
            ],

            $actionColumn,
        ],
    ]); ?>

</div>

<?php echo $this->render('@app/views/allocation/allocationHelpers', ['deptCode' => $deptCode]); ?>

<?php

// Ensure panel heading and its right-aligned summary text are white
$this->registerCss("\n.kv-panel .panel-heading,\n.kv-panel .panel-heading * {\n  color: #fff !important;\n}\n.kv-panel .panel-heading .summary {\n  color: #fff !important;\n}\n");

// Ensure the card header above the _search and the grouped row header share the same font size and weight
$this->registerCss("\n.semester-index .card-header {\n  font-size: 1rem;\n  font-weight: 700;\n}\n.kv-grid-table .kv-grouped-row > td {\n  font-size: 1rem;\n  font-weight: 700;\n}\n");

// Loading spinner for the filter header (top of filter partial) — make it more conspicuous
$this->registerCss("\n#filter-loading-indicator {\n  display: inline-block;\n  width: 22px;\n  height: 22px;\n  border: 3px solid rgba(255,255,255,0.45);\n  border-top-color: #fff;\n  border-radius: 50%;\n  animation: spin 0.8s linear infinite;\n}\n#filter-loading-text {\n  font-weight: 700;\n  text-transform: uppercase;\n  letter-spacing: 0.5px;\n  animation: pulse 1.2s ease-in-out infinite;\n}\n@keyframes spin { to { transform: rotate(360deg); } }\n@keyframes pulse { 0% { opacity: .5;} 50% { opacity: 1;} 100% { opacity: .5;} }\n.d-none { display: none !important; }\n");

// Show spinner when search or panel forms submit
$this->registerJs(<<<JS
(function(){
  function showFilterSpinner(){
    var sp = document.getElementById('filter-loading-indicator');
    var tx = document.getElementById('filter-loading-text');
    if(sp){ sp.classList.remove('d-none'); }
    if(tx){ tx.classList.remove('d-none'); }
  }
  document.addEventListener('submit', function(e){
    if(e.target && (e.target.id === 'semester-search-form' || e.target.id === 'panel-filter-form')){
      showFilterSpinner();
    }
  }, true);
  window.addEventListener('beforeunload', function(){ showFilterSpinner(); });
})();
JS);



$this->registerJs("
$(document).ready(function() {
    $('#academic-year-filter').on('change', function() {
        var form = $('#academic-year-filter-form');
        var selectedValue = $(this).val();
        
        var baseUrl = '" . Url::current([]) . "';
        var newUrl = baseUrl;
        
        if (selectedValue && selectedValue !== '') {
            var separator = baseUrl.indexOf('?') !== -1 ? '&' : '?';
            newUrl = baseUrl + separator + 'CourseAssignmentSearch%5BacademicYear%5D=' + encodeURIComponent(selectedValue);
        }
        
        window.location.href = newUrl;
    });
    
    $('#programme-filter').on('change', function() {
        var selectedProgramme = $(this).val();
        var currentUrl = new URL(window.location.href);
        var params = new URLSearchParams(currentUrl.search);
        
        params.delete('CourseAssignmentSearch[programme]');
        
        if (selectedProgramme && selectedProgramme !== '') {
            params.set('CourseAssignmentSearch[programme]', selectedProgramme);
        }
        
        var newUrl = currentUrl.pathname + '?' + params.toString();
        
        if (params.toString() === '') {
            newUrl = currentUrl.pathname;
        }
        
        window.location.href = newUrl;
    });
    
    $('.kv-grouped-row').each(function() {
        var groupText = $(this).find('td').text();
        
        if (groupText.indexOf('|') !== -1 && !groupText.includes('SEMESTER')) {
            $(this).addClass('group-programme-header');
        } 
        else if (groupText.indexOf('SEMESTER') !== -1 || groupText.indexOf('YEAR') !== -1) {
            $(this).addClass('group-level-header');
        }
        
        var nextRows = $(this).nextUntil('.kv-grouped-row');
        var count = nextRows.length;
        var headerText = $(this).find('td').html();
        $(this).find('td').html(headerText + ' <span class=\"badge badge-light ml-2\">' + count + ' course(s)</span>');
    });
});
");
?>