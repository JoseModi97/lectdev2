<?php

/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $coursesProvider
 * @var app\models\search\AllocationRequestsSearch $coursesSearch
 * @var app\models\CourseAllocationFilter $filter
 * @var string $title
 * @var string $deptCode
 * @var string $deptName
 * @var string $panelHeading
 */

use app\components\BreadcrumbHelper;
use app\models\AllocationStatus;
use app\models\CourseAssignment;
use kartik\grid\GridView;
use yii\db\ActiveQuery;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

use app\models\DegreeProgramme;
use app\models\Group;
use app\models\LevelOfStudy;
use app\models\Semester;

echo BreadcrumbHelper::generate([
    [
        'label' => 'HOD',
        'url' => [
            '/site/hod',
            'filtersFor' => Yii::$app->request->get('CourseAllocationFilter')['purpose']
        ]
    ],
    'Courses'
]);
$activeFiltersContent = function ($filter) {
    $content = '<div class="active-filters">';
    $content .= '<span class="filter-item"><strong>Academic year:</strong> ' . $filter->academicYear . '</span>';

    if ($filter->purpose === 'nonSuppCourses' || $filter->purpose === 'suppCourses') {
        $degree = DegreeProgramme::find()->select(['DEGREE_NAME'])->where(['DEGREE_CODE' => $filter->degreeCode])->asArray()->one();
        if ($degree) {
            $content .= '<span class="filter-item"><strong>Degree:</strong> ' . $degree['DEGREE_NAME'] . ' (' . $filter->degreeCode . ')</span>';
        }

        $level = LevelOfStudy::find()->select(['NAME'])->where(['LEVEL_OF_STUDY' => $filter->levelOfStudy])->asArray()->one();
        if ($level) {
            $content .= '<span class="filter-item"><strong>Level of study:</strong> ' . strtoupper($level['NAME']) . '</span>';
        }

        $group = Group::find()->select(['GROUP_NAME'])->where(['GROUP_CODE' => $filter->group])->asArray()->one();
        if ($group) {
            $content .= '<span class="filter-item"><strong>Group:</strong> ' . strtoupper($group['GROUP_NAME']) . '</span>';
        }

        $semesterId = $filter->academicYear . '_' . $filter->degreeCode . '_' . $filter->levelOfStudy . '_' . $filter->semester . '_' . $filter->group;
        $semester = Semester::find()->alias('SM')->select(['SM.SEMESTER_ID', 'SM.SEMESTER_CODE', 'SM.DESCRIPTION_CODE'])->joinWith(['semesterDescription SD' => function ($q) {
            $q->select(['SD.DESCRIPTION_CODE', 'SD.SEMESTER_DESC']);
        }], true, 'INNER JOIN')->where(['SM.SEMESTER_ID' => $semesterId])->asArray()->one();
        if ($semester) {
            $content .= '<span class="filter-item"><strong>Semester:</strong> ' . $semester['SEMESTER_CODE'] . ' (' . strtoupper($semester['semesterDescription']['SEMESTER_DESC']) . ')</span>';
        }
    }
    $content .= '</div>';
    return $content;
};

$this->registerCss('
    .panel-title-white { color: #fff; }
    .m-0{ color: #fff; }
    .float-end { color: #fff; }
    .active-filters {
        padding: 5px 10px;
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    .filter-item {
        display: inline-block;
        margin-right: 15px;
        font-size: 12px;
        color: #6c757d;
    }
    .filter-item strong {
        color: #495057;
    }
');

$this->title = $title;
$this->params['breadcrumbs'][] = [
    'label' => 'Lecturer allocation filters',
    'url' => ['/allocation/filters', 'filtersFor' => $filter->purpose]
];
$this->params['breadcrumbs'][] = $this->title;

$gridId = 'requested-courses-grid';
if ($filter->purpose === 'serviceCourses') {
    $gridId = 'service-courses-grid';

    $departmentColumn = [
        'attribute' => 'requestingDept.DEPT_NAME',
        'label' => 'REQUESTING DEPARTMENT',
        'vAlign' => 'middle',
    ];
} else {
    $departmentColumn = [
        'attribute' => 'servicingDept.DEPT_NAME',
        'label' => 'SERVICING DEPARTMENT',
        'vAlign' => 'middle',
    ];
}

$courseCodeColumn = [
    'attribute' => 'marksheet.course.COURSE_CODE',
    'label' => 'COURSE CODE',
    'vAlign' => 'middle',
];

$courseNameColumn = [
    'attribute' => 'marksheet.course.COURSE_NAME',
    'label' => 'COURSE NAME',
    'vAlign' => 'middle',
];

$requestStatusColumn = [
    'label' => 'STATUS',
    'attribute' => 'status.STATUS_NAME',
    'format' => 'raw',
    'vAlign' => 'middle',
    'value' => function ($model) {
        $status = $model->status->STATUS_NAME;
        $icon = '';
        $badgeClass = '';

        switch ($status) {
            case 'APPROVED':
                $icon = '<i class="fas fa-check-circle"></i>';
                $badgeClass = 'badge bg-success';
                break;
            case 'PENDING':
                $icon = '<i class="fas fa-clock"></i>';
                $badgeClass = 'badge bg-warning text-dark';
                break;
            case 'NOT APPROVED':
                $icon = '<i class="fas fa-times-circle"></i>';
                $badgeClass = 'badge bg-danger';
                break;
            default:
                $badgeClass = 'badge bg-secondary';
                break;
        }

        return '<span class="' . $badgeClass . '">' . $icon . ' ' . $status . '</span>';
    }
];

$allocatedLecturer = [
    'label' => 'ALLOCATED LECTURER(S)',
    'vAlign' => 'middle',
    'value' => function ($model) use ($deptCode) {
        if ($model->status->STATUS_NAME === 'APPROVED') {
            $assignments = CourseAssignment::find()->alias('CS')->select(['CS.PAYROLL_NO'])
                ->where(['CS.MRKSHEET_ID' => $model->MARKSHEET_ID])
                ->joinWith(['staff ST' => function (ActiveQuery $q) {
                    $q->select([
                        'ST.PAYROLL_NO',
                        'ST.DEPT_CODE',
                        'ST.SURNAME',
                        'ST.OTHER_NAMES',
                        'ST.EMP_TITLE',
                        'ST.MOBILE',
                        'ST.EMAIL'
                    ]);
                }], true, 'INNER JOIN')
                ->asArray()->all();

            $lecturers = '';
            if (!empty($assignments)) {
                foreach ($assignments as $assignment) {
                    $lecturers .= $assignment['staff']['EMP_TITLE'] . ' ' . $assignment['staff']['SURNAME'] . ' ' .
                        $assignment['staff']['OTHER_NAMES'] . ' (' . $assignment['staff']['EMAIL'] . ' ' .
                        $assignment['staff']['MOBILE'] . '), ';
                }
            }

            return rtrim($lecturers, ', ');;
        }

        return '';
    }
];

if ($gridId === 'service-courses-grid') {
    $actionColumn = [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{assign-course-render}',
        'vAlign' => 'middle',
        'contentOptions' => ['style' => 'white-space:nowrap;', 'class' => 'kartik-sheet-style kv-align-middle'],
        'buttons' => [
            'assign-course-render' => function ($url, $model) {
                $status = AllocationStatus::findOne($model->STATUS_ID);
                if ($status->STATUS_NAME === 'PENDING') {
                    return Html::button('<i class="fa fa-user-plus"></i> Allocate', [
                        'title' => 'Allocate lecturer',
                        'href' => Url::to([
                            '/allocation/assign-course-render',
                            'id' => $model->REQUEST_ID,
                            'marksheetId' => $model->MARKSHEET_ID,
                            'type' => 'service'
                        ]),
                        'class' => 'btn  btn-xs btn-spacer assign-external-lecturer',
                        'data-id' => $model->REQUEST_ID,
                        'data-marksheetId' => $model->MARKSHEET_ID,
                        'data-type' => 'service'
                    ]);
                } else {
                    return Html::button('<i class="fas fa-eye" style="color: #17a2b8;"></i> Details', [
                        'title' => 'View lecturer request details',
                        'href' => Url::to(['/allocation/view-request-render', 'requestId' => $model->REQUEST_ID]),
                        'class' => 'btn btn-xs btn-spacer view-course-request'
                    ]);
                }
            },
        ]
    ];
} else {
    $actionColumn = [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{view-request-render} {cancel-request}',
        'vAlign' => 'middle',
        'contentOptions' => ['style' => 'white-space:nowrap;', 'class' => 'kartik-sheet-style kv-align-middle'],
        'buttons' => [
            'view-request-render' => function ($url, $model) {
                return Html::button('<i class="fas fa-eye" style="color: #17a2b8;"></i> Details', [
                    'title' => 'View lecturer request details',
                    'href' => Url::to(['/allocation/view-request-render', 'requestId' => $model->REQUEST_ID]),
                    'class' => 'btn btn-xs btn-spacer view-course-request'
                ]);
            },
            'cancel-request' => function ($url, $model) use ($deptCode) {
                $status = AllocationStatus::findOne($model->STATUS_ID);
                $isPending = $status && $status->STATUS_NAME === 'PENDING';
                $isOwner = (isset($model->REQUESTING_DEPT) && $model->REQUESTING_DEPT === $deptCode);
                if ($isPending && $isOwner) {
                    return Html::button('<i class="fas fa-times text-danger"></i> Cancel', [
                        'title' => 'Cancel this lecturer request',
                        'class' => 'btn btn-xs btn-spacer cancel-request',
                        'data-id' => $model->REQUEST_ID,
                    ]);
                }
                return '';
            }
        ]
    ];
}
?>

<?php
echo $this->render('moreFilters', ['filter' => $filter]);
?>

<div class="requested-courses">
    <?php
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
            'striped' => false,
            'bordered' => true,
            'responsiveWrap' => false,
            'toolbar' => [
                '{toggleData}'
            ],
            'panel' => [
                'type' => GridView::TYPE_DEFAULT,
                'heading' => '<div class="panel-title-white"><i class="fas fa-book"></i> ' . Html::encode($panelHeading) . '</div>',
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
                $departmentColumn,
                $requestStatusColumn,
                $allocatedLecturer,
                $actionColumn
            ]
        ]);
    } catch (Exception $ex) {
        $message = 'Failed to create grid for department courses.';
        if (YII_ENV_DEV) {
            $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
        }
        throw new ServerErrorHttpException($message, 500);
    }
    ?>
</div>
<?php
echo $this->render('allocationHelpers', ['deptCode' => $deptCode]);
