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
use app\models\SemesterDescription;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use app\components\BreadcrumbHelper;
use app\models\CourseAssignment;
use app\models\EmpVerifyView;

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
$data = $dataProvider->getModels();
$courseCodeFilter = [];
$courseNameFilter = [];

foreach ($data as $model) {
    $course = $model->course ?? null;
    if ($course === null) {
        continue;
    }

    $code = trim((string) ($course->COURSE_CODE ?? ''));
    if ($code !== '') {
        $courseCodeFilter[$code] = $code;
    }

    $name = trim((string) ($course->COURSE_NAME ?? ''));
    if ($name !== '') {
        $courseNameFilter[$name] = $name;
    }
}

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
            return Html::button('<i class="fas fa-user-plus text-success"></i> <span class="text-dark">Allocate</span>', [
                'type' => 'button',
                'title' => 'Allocate lecturer',
                'class' => 'assign-lecturer btn btn-link p-0 text-decoration-none',
                'data-id' => 'NULL',
                'data-marksheet-id' => $model->MRKSHEET_ID,
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
    <div class="card shadow-sm rounded-0 w-100">
        <div class="card-body row g-3">
            <h5><?= $filtertype[Yii::$app->request->get('filtersFor')] ?? $filtertype[Yii::$app->request->get('SemesterSearch')['purpose']] ?? '' ?></h5>
            <?php
            if (!empty(Yii::$app->request->get('SemesterSearch'))) {
                $level = LevelOfStudy::findOne(['LEVEL_OF_STUDY' => Yii::$app->request->get('SemesterSearch')['LEVEL_OF_STUDY']]);
            ?>
                <h5><?= Html::encode(Yii::$app->request->get('SemesterSearch')['ACADEMIC_YEAR']) ?> | <?= Html::encode(Yii::$app->request->get('SemesterSearch')['DEGREE_CODE']) ?> | <?= Html::encode(DegreeProgramme::findOne(['DEGREE_CODE' => Yii::$app->request->get('SemesterSearch')['DEGREE_CODE']])->DEGREE_NAME) ?></h5>
                <p><?= Html::encode($level['NAME']) ?>, SEMESTER <?= Html::encode(Yii::$app->request->get('SemesterSearch')['SEMESTER_CODE']) ?></p>
            <?php
            }
            ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'pjax' => false,

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
                        'attribute' => 'courseCode',
                        'label' => 'Course Code',
                        'value' => 'course.COURSE_CODE',
                        'filterType' => GridView::FILTER_SELECT2,
                        'filter' => $courseCodeFilter,
                        'filterWidgetOptions' => [
                            'options' => ['placeholder' => 'Any course code'],
                            'initValueText' => $selectedCourseCode,
                            'pluginOptions' => ['allowClear' => true],
                        ],
                        'filterInputOptions' => ['placeholder' => 'Any course code'],
                    ],
                    [
                        'attribute' => 'courseName',
                        'label' => 'Course Name',
                        'value' => 'course.COURSE_NAME',
                        'filterType' => GridView::FILTER_SELECT2,
                        'filter' => $courseNameFilter,
                        'filterWidgetOptions' => [
                            'options' => ['placeholder' => 'Any course name'],
                            'initValueText' => $selectedCourseName,
                            'pluginOptions' => ['allowClear' => true],
                        ],
                        'filterInputOptions' => ['placeholder' => 'Any course name'],
                    ],
                    [
                        'label' => 'Group Name',
                        'value' => function ($model) {
                            $semDesc = $model->semester->semesterDescription;
                            return $model->semester->group->GROUP_NAME;
                        },
                        'group' => true,
                    ],
                    [
                        'attribute' => 'PAYROLL_NO', // Attribute to filter on
                        'header' => 'Assigned Lecturer(s)',
                        'format' => 'raw',
                        'vAlign' => 'middle',
                        'width' => '25%',
                        'value' => function ($d) {
                            $assignments = CourseAssignment::find()->select(['PAYROLL_NO'])
                                ->where(['MRKSHEET_ID' => $d->MRKSHEET_ID])->all();
                            $leadLecturer = '';
                            $otherLecturers = '';
                            $courseLeaderFound = false;
                            $courseLeader = null;
                            if (!empty($assignments)) {
                                foreach ($assignments as $assignment) {
                                    $lecturer = EmpVerifyView::find()
                                        ->select(['PAYROLL_NO', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE'])
                                        ->where(['PAYROLL_NO' => $assignment->PAYROLL_NO])
                                        ->one();

                                    if (is_null($lecturer)) {
                                        continue;
                                    }

                                    $lecturerName = '';
                                    if (!empty($lecturer->EMP_TITLE)) {
                                        $lecturerName .= $lecturer->EMP_TITLE;
                                    }

                                    if (!empty($lecturer->OTHER_NAMES)) {
                                        $lecturerName .= ' ' . $lecturer->OTHER_NAMES;
                                    }

                                    if (empty($lecturerName)) {
                                        continue;
                                    }

                                    if (!$courseLeaderFound) {
                                        $courseLeader = MarksheetDef::find()
                                            ->select(['PAYROLL_NO'])
                                            ->where(['MRKSHEET_ID' => $d->MRKSHEET_ID, 'PAYROLL_NO' => $lecturer->PAYROLL_NO])->one();
                                    }

                                    if (!$courseLeaderFound && $courseLeader) {
                                        $courseLeaderFound = true;
                                        $leadLecturer = '<div class="lecturer-badge course-leader">' . Html::encode($lecturerName) . ' <span class="badge bg-success">Leader</span></div>';
                                    } else {
                                        $otherLecturers .= '<div class="lecturer-badge not-course-leader">' . Html::encode($lecturerName) . '</div>';
                                    }
                                }
                            }
                            return $leadLecturer . $otherLecturers;
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

    </div>

</div>

<?php echo $this->render('@app/views/allocation/allocationHelpers', ['deptCode' => $deptCode]); ?>

<?php



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