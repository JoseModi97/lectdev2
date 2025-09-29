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
    'template' => '{allocate} {manage} {remove}',
    'contentOptions' => [
        'style' => 'white-space: nowrap; width: 30%;',
        'class' => 'text-center align-middle'
    ],

    'buttons' => [
        'allocate' => function ($url, $model, $key) {
            return Html::a('<i class="fas fa-user-plus text-success"></i> <span class="text-dark">Allocate</span>', '#', [
                'title' => 'Allocate lecturer',
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
        <div class="card-header text-white fw-bold" style="background-image: linear-gradient(#455492, #304186, #455492);">
            Academic Filters
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

            $semesterTypeOptions = [
                'SUPPLEMENTARY' => 'SUPPLEMENTARY',
                'TEACHING' => 'TEACHING',
            ];

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
                            'placeholder' => 'Select Level of Study...',
                            'id' => 'levelSelect',
                            'required' => true,
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                        'pluginEvents' => [
                            'select2:select' => 'function (e) { this.form.submit(); }',
                        ],
                    ]);
                    echo '</div>';
                    echo '<div class="col-md-6">';
                    echo $form->field($searchModel, 'SEMESTER_TYPE')->widget(Select2::class, [
                        'data' => $semesterTypeOptions,
                        'options' => [
                            'placeholder' => 'Select Semester Type...',
                            'id' => 'semesterTypeSelect',
                            'required' => true,
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                        'pluginEvents' => [
                            'select2:select' => 'function (e) { this.form.submit(); }',
                        ],
                    ]);
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
            echo '<div class="d-flex flex-column">';
            echo '<h5>'
                . Html::encode($academicYear) . ' '
                . Html::encode($degreeCode) . ' '
                . Html::encode($degreeName)
                . '</h5>';
            echo '<p>'
                . Html::encode($levelModel->NAME ?? '') . ' SEMESTER '
                . Html::encode($semesterSearch['SEMESTER_CODE'] ?? '')
                . '</p>';
            echo '</div>';
            $heading = ob_get_clean();

                    return [
                        'heading' => $heading,
                        'type' => 'default',
                        'before' => $before,
                        'headingOptions' => [
                            'style' => 'background-image: linear-gradient(#455492, #304186, #455492); color: white; padding-left: 12px; padding-right: 12px;',
                            'class' => 'text-white',
                        ],
                    ];
                })(),

        'responsiveWrap' => false,
        'condensed' => true,
        'hover' => true,
        'striped' => false,
        'bordered' => true,
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
                    $semDesc = $model->semester->semesterDescription ?? '';
                    return $model->semester->ACADEMIC_YEAR . ' - ' . $model->semester->levelOfStudy->NAME . '  |  Semester ' . $model->semester->SEMESTER_CODE . '  |  ' . $semDesc->SEMESTER_DESC ?? '';
                },
                'group' => true,
                'groupedRow' => true,
                'contentOptions' => [
                    'style' => 'background-image: linear-gradient(#455492, #304186, #455492); color: white;padding-top: 10px; padding-bottom: 10px;',
                    'class' => 'py-3'
                ],
            ],
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
            [
                'label' => 'Semester Type',
                'attribute' => 'SEMESTER_TYPE',
                'value' => 'semester.SEMESTER_TYPE',
                'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                'filter' => [
                    'SUPPLEMENTARY' => 'SUPPLEMENTARY',
                    'TEACHING' => 'TEACHING',
                ],
                'filterWidgetOptions' => [
                    'pluginOptions' => [
                        'allowClear' => true,
                        'placeholder' => 'Select Semester Type...',
                    ],
                ],
            ],


            [
                'label' => 'Group Name',
                'value' => function ($model) {
                    $semDesc = $model->semester->semesterDescription;
                    return $model->semester->group->GROUP_NAME;
                },
                'group' => true,
                'subGroupOf' => 1,
            ],
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
                        return '<span class="badge bg-secondary">No lecturer assigned</span>';
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
                            $output .= '<div class="mb-1">
                                        <span class="badge bg-primary">
                                            <i class="fas fa-user-tie"></i> ' . Html::encode($lecturerName) . ' (Leader)
                                        </span>
                                    </div>';
                        } else {
                            $output .= '<div class="mb-1">
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-user"></i> ' . Html::encode($lecturerName) . '
                                        </span>
                                    </div>';
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
