<?php

/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

use app\models\Course;


/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $coursesProvider
 * @var app\models\search\MarksheetDefAllocationSearch $coursesSearch
 * @var app\models\CourseAllocationFilter $filter
 * @var string $title
 * @var string $deptCode
 * @var string $deptName
 * @var string $panelHeading
 */


use app\models\CourseAllocationFilter;
use app\models\CourseAssignment;
use app\models\DegreeProgramme;
use app\models\EmpVerifyView;
use app\models\Group;
use app\models\LevelOfStudy;
use app\models\MarksheetDef;
use app\models\search\AllocationRequestsSearchNew;
use app\models\search\MarksheetDefAllocationSearchNew;
use app\models\Semester;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

$this->registerCss(
    '
.m-0{
    color: #fff;}
.float-end{
    color: #fff;}
    '
);

$this->registerCss('
.scrollable-content {
    max-height: 80vh; /* Set this to your desired height */
    overflow-y: auto;
}
');
$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => 'Semesters', 'url' => ['/semester/index']];
$this->params['breadcrumbs'][] = [
    'label' => 'Lecturer allocation filters',
    'url' => ['/allocation/filters', 'filtersFor' => $filter->purpose]
];

// Data for lecturer filter
$lecturersList = ArrayHelper::map(
    EmpVerifyView::find()->where(['DEPT_CODE' => $deptCode, 'STATUS_DESC' => 'ACTIVE', 'JOB_CADRE' => 'ACADEMIC'])->orderBy('SURNAME')->all(),
    'PAYROLL_NO',
    function ($model) {
        return $model->SURNAME . ' ' . $model->OTHER_NAMES;
    }
);


$courseCodeColumn = [
    'attribute' => 'course.COURSE_CODE',
    'label' => 'Code',
    'vAlign' => 'middle',
];

$courseNameColumn = [
    'attribute' => 'course.COURSE_NAME',
    'label' => 'Course Name',
    'vAlign' => 'middle',
    'width' => '40%'
];

$lecturerColumn = [
    'attribute' => 'PAYROLL_NO', // Attribute to filter on
    'label' => 'Assigned Lecturer(s)',
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



// $actionColumn = [
//     'class' => 'kartik\grid\ActionColumn',
//     'template' => '{buttons}',
//     'buttons' => [
//         'buttons' => function ($url, $model) {
//             $items = '';
//             $items .= Html::a('<i class="fas fa-user-plus"></i> Allocate', '#', [
//                 'title' => 'Allocate lecturer',
//                 'class' => 'dropdown-item assign-lecturer',
//                 'data-id' => 'NULL',
//                 'data-marksheetid' => $model->MRKSHEET_ID,
//                 'data-type' => 'departmental',
//             ]);
//             $items .= Html::a(
//                 '<i class="fas fa-tasks"></i> Manage',
//                 Url::to(['/allocation/allocated-lecturer-render', 'marksheetId' => $model->MRKSHEET_ID, 'purpose' => 'manage']),
//                 [
//                     'title' => 'Manage allocated lecturer(s)',
//                     'class' => 'dropdown-item manage-lecturer',
//                 ]
//             );

//             $items .= Html::a('<i class="fas fa-trash"></i> Remove', '#', [
//                 'title' => 'Remove allocated lecturer(s)',
//                 'class' => 'dropdown-item remove-lecturer',
//                 'href' => Url::to(['/allocation/allocated-lecturer-render', 'marksheetId' => $model->MRKSHEET_ID, 'purpose' => 'remove']),
//             ]);

//             return '<div class="dropdown">' .
//                 Html::button('Actions <i class="fas fa-caret-down"></i>', [
//                     'class' => 'btn btn-sm btn-secondary dropdown-toggle',
//                     'data-bs-toggle' => 'dropdown',
//                     'aria-haspopup' => 'true',
//                     'aria-expanded' => 'false',
//                 ]) .
//                 '<div class="dropdown-menu">' . $items . '</div>' .
//                 '</div>';
//         }
//     ],
//     'hAlign' => 'center',
// ];


$activeFiltersContent = function ($filter) {
    $content = '<div class="row"><div class="col-sm-12 col-md-6 col-lg-6"><div class="row"><div class="col-md-4"><span class="pull-left">Academic year:</span></div><div class="col-md-8"><span class="pull-left">' . $filter->academicYear . '</span></div></div>';
    if ($filter->purpose === 'nonSuppCourses' || $filter->purpose === 'suppCourses') {
        $degree = DegreeProgramme::find()->select(['DEGREE_NAME'])->where(['DEGREE_CODE' => $filter->degreeCode])->asArray()->one();
        $content .= '<div class="row"><div class="col-md-4"><span class="pull-left">Degree:</span></div><div class="col-md-8"><span class="pull-left">' . $degree['DEGREE_NAME'] . ' (' . $filter->degreeCode . ')</span></div></div>';

        $level = LevelOfStudy::find()->select(['NAME'])->where(['LEVEL_OF_STUDY' => $filter->levelOfStudy])->asArray()->one();
        $content .= '<div class="row"><div class="col-md-4"><span class="pull-left">Level of study:</span></div><div class="col-md-8"><span class="pull-left">' . strtoupper($level['NAME']) . '</span></div></div>';

        $group = Group::find()->select(['GROUP_NAME'])->where(['GROUP_CODE' => $filter->group])->asArray()->one();
        $content .= '<div class="row"><div class="col-md-4"><span class="pull-left">Group:</span></div><div class="col-md-8"><span class="pull-left">' . strtoupper($group['GROUP_NAME']) . '</span></div></div>';

        $semesterId = $filter->academicYear . '_' . $filter->degreeCode . '_' . $filter->levelOfStudy . '_' . $filter->semester . '_' . $filter->group;
        $semester = Semester::find()->alias('SM')->select(['SM.SEMESTER_ID', 'SM.SEMESTER_CODE', 'SM.DESCRIPTION_CODE'])->joinWith(['semesterDescription SD' => function ($q) {
            $q->select(['SD.DESCRIPTION_CODE', 'SD.SEMESTER_DESC']);
        }], true, 'INNER JOIN')->where(['SM.SEMESTER_ID' => $semesterId])->asArray()->one();
        $content .= '<div class="row"><div class="col-md-4"><span class="pull-left">Semester:</span></div><div class="col-md-8"><span class="pull-left">' . $semester['SEMESTER_CODE'] . ' (' . strtoupper($semester['semesterDescription']['SEMESTER_DESC']) . ')</span></div></div>';
    }
    $content .= '</div></div>';
    return $content;
};

?>

<div class="scrollable-content">
    <div class="accordion mb-3" id="notesAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="notesHeader">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNotes" aria-expanded="false" aria-controls="collapseNotes">
                    <i class="fas fa-info-circle me-2"></i> Important Notes
                </button>
            </h2>
            <div id="collapseNotes" class="accordion-collapse collapse" aria-labelledby="notesHeader" data-bs-parent="#notesAccordion">
                <div class="accordion-body">
                    <p>1. Every course must have a course leader who is responsible for defining examination assessments and uploading marks.</p>
                    <p>2. Courses taught by part-time lecturers (Non-UON Staff) MUST be assigned a course leader who is a UON staff member.</p>
                    <p>3. Use the <i class="fas fa-tasks"></i> <strong>Manage</strong> action to set a lead lecturer. A <span class="badge bg-success">Leader</span> badge indicates a lead lecturer.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="allocation-grid-container">
        <?php
        $gridId = 'non-supp-courses-grid';
        try {
            echo GridView::widget([
                'id' => $gridId,
                'dataProvider' => $coursesProvider,
                'filterModel' => $coursesSearch,
                'headerRowOptions' => ['style' => 'background-color:#f8f9fa;'],
                'filterRowOptions' => ['style' => 'background-color:#f8f9fa;'],
                'pjax' => true,
                'pjaxSettings' => ['options' => ['id' => $gridId . '-pjax']],
                'condensed' => true,
                'hover' => true,
                'toolbar' => [
                    '{export}',
                    '{toggleData}',
                ],
                'export' => [
                    'formats' => [GridView::PDF],
                ],
                'responsiveWrap' => false,
                'condensed' => true,
                'hover' => true,
                'striped' => false,
                'bordered' => true,

                'exportConfig' => [
                    GridView::PDF => [
                        'label' => 'PDF',
                        'icon' => 'fas fa-file-pdf',
                        'filename' => 'course-allocations',
                        'options' => ['title' => 'Course Allocations Report'],
                        'config' => [
                            'format' => 'A4-L', // Landscape for wider tables
                            'marginTop' => 20,
                            'marginBottom' => 15,
                            'marginLeft' => 10,
                            'marginRight' => 10,
                            'methods' => [
                                'SetHeader' => [
                                    '<div style="text-align:left; font-size:10pt; font-weight:bold;">
                        University of Nairobi
                    </div>
                    <div style="text-align:center; font-size:12pt;">
                        Course Allocations Report
                    </div>
                    <div style="text-align:right; font-size:9pt; color:#555;">
                        {PAGENO}/{nbpg}
                    </div>'
                                ],
                                'SetFooter' => [
                                    '<div style="text-align:left; font-size:8pt; color:#777;">
                        Generated on ' . date("Y-m-d H:i:s") . '
                    </div>
                    <div style="text-align:right; font-size:8pt; color:#777;">
                        Powered by Lecturer Allocation System
                    </div>'
                                ],
                            ],
                            'cssInline' => '
                body { font-family: DejaVu Sans, sans-serif; font-size:9pt; }
                table { border-collapse: collapse; }
                th { background-color: #f0f0f0; font-weight: bold; font-size:9pt; }
                td { font-size:8.5pt; padding: 4px; }
            ',
                        ],
                    ],
                ],
                'panel' => [
                    'type' => GridView::TYPE_DEFAULT,
                    'heading' => '<i class="fas fa-book"></i> ' . Html::encode($panelHeading . ' - ' . Yii::$app->request->get('CourseAllocationFilter')['purpose']),
                    'headingOptions' => [
                        'style' => 'background-color:#1a73e8; color:#fff; font-size:16px; font-weight:bold;'
                    ],
                    'before' => $activeFiltersContent($filter),
                    'after' => false,
                ],

                'panel' => [
                    'heading' => '<i class="fas fa-book"></i> ' . Html::encode($panelHeading . ' - ' . Yii::$app->request->get('CourseAllocationFilter')['purpose']),
                    'headingOptions' => [
                        'style' => 'background-image: linear-gradient(#455492, #304186, #455492); color:#fff; font-size:16px; font-weight:bold; padding:8px 12px;'
                    ],
                    'before' => $activeFiltersContent($filter),
                    'after' => false,
                ],


                'columns' => [
                    ['class' => 'kartik\grid\SerialColumn', 'vAlign' => 'middle'],
                    $courseCodeColumn,
                    $courseNameColumn,
                    $lecturerColumn,
                    $actionColumn
                ]
            ]);
        } catch (Exception $ex) {
            $message = 'Failed to create grid for department courses.';
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        } ?>
    </div>
</div>



<?php
echo $this->render('allocationHelpers', ['deptCode' => $deptCode]);
