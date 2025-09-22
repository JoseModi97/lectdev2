<?php

use app\components\BreadcrumbHelper;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $coursesProvider
 * @var app\models\search\MarksheetDefAllocationSearch $coursesSearch
 * @var app\models\CourseAllocationFilter $filter
 * @var string $title
 * @var string $deptCode
 * @var string $deptName
 * @var string $panelHeading
 * @var string|null $gridId
 * @var bool|null $renderBreadcrumbs
 * @var bool|null $includeHelpers
 * @var bool|null $showNotes
 * @var bool|null $wrap
 */

$gridId = $gridId ?? 'non-supp-courses-grid';
$renderBreadcrumbs = $renderBreadcrumbs ?? true;
$includeHelpers = $includeHelpers ?? true;
$showNotes = $showNotes ?? true;
$wrap = $wrap ?? true;

if ($renderBreadcrumbs) {
    echo BreadcrumbHelper::generate([
        [
            'label' => 'Semesters',
            'url' => [
                '/semester/index',
                'filtersFor' => $filter->purpose,
            ],
        ],
        'Courses',
    ]);

    $this->title = $title;
    $this->params['breadcrumbs'][] = ['label' => 'Semesters', 'url' => ['/semester/index']];
    $this->params['breadcrumbs'][] = [
        'label' => 'Lecturer allocation filters',
        'url' => ['/allocation/filters', 'filtersFor' => $filter->purpose],
    ];
}

$this->registerCss(
    '.m-0{color:#fff;}.float-end{color:#fff;}'
);

$this->registerCss(
    '.scrollable-content{max-height:80vh;overflow-y:auto;}'
);

echo $this->render('_coursesGrid', [
    'gridId' => $gridId,
    'coursesProvider' => $coursesProvider,
    'coursesSearch' => $coursesSearch,
    'filter' => $filter,
    'panelHeading' => $panelHeading,
    'deptCode' => $deptCode,
    'wrap' => $wrap,
    'showNotes' => $showNotes,
]);

if ($includeHelpers) {
    echo $this->render('allocationHelpers', ['deptCode' => $deptCode]);
}
