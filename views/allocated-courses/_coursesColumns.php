<?php

use kartik\grid\GridView;
use yii\helpers\ArrayHelper;

/**
 * @author Jack Jmm
 * @email jackmutiso37@gmail.com
 * @create date 12-9-2025 20:50:22 
 * @desc 
 */

$data = $dataProvider->getModels();


$courseCodeColumn = [
    'attribute' => 'marksheetDef.course.COURSE_CODE',
    'label' => 'CODE',
    'filterType' => GridView::FILTER_SELECT2,
    'filter' => ArrayHelper::map($data, 'marksheetDef.course.COURSE_CODE', 'marksheetDef.course.COURSE_CODE'),
    'filterWidgetOptions' => [
        'pluginOptions' => ['allowClear' => true],
    ],
    'filterInputOptions' => ['placeholder' => '--All--'],
    'contentOptions' => ['style' => 'white-space: nowrap; width: 260px;'],
    'headerOptions' => ['style' => 'white-space: nowrap; width: 260px;'],
    'group' => true,
    'subGroupOf' => 1,
    'vAlign' => 'middle',

];


$courseNameColumn = [
    'attribute' => 'marksheetDef.course.COURSE_NAME',
    'label' => 'COURSE NAME',
    'contentOptions' => ['style' => 'white-space: nowrap; min-width: 280px;'],
    'headerOptions' => ['style' => 'white-space: nowrap; min-width: 280px;'],
    'filterType' => GridView::FILTER_SELECT2,
    'filter' => ArrayHelper::map($data, 'marksheetDef.course.COURSE_NAME', 'marksheetDef.course.COURSE_NAME'),
    'filterWidgetOptions' => [
        'pluginOptions' => ['allowClear' => true],
    ],
    'filterInputOptions' => ['placeholder' => 'Search course name'],
];

$lecutureRoomColumn = [
    'attribute' => 'marksheetDef.EXAM_ROOM',
    'label' => 'ROOM'
];

$levelOfStudyColumn = [
    'label' => 'LEVEL',
    'value' => function ($model) {
        return $model->marksheetDef->semester->LEVEL_OF_STUDY;
    },
    'contentOptions' => ['style' => 'white-space: nowrap; width: 140px;'],
    'headerOptions' => ['style' => 'white-space: nowrap; width: 140px;']
];

$sessionTypeColumn = [
    'label' => 'SESSION',
    'value' => function ($model) {
        $session =  $model->marksheetDef->semester->SESSION_TYPE;
        if (empty($session)) {
            return 'NOT SET';
        } else {
            return $session;
        }
    }
];

$semesterCodeColumn = [
    'label' => 'SEMESTER',
    'width' => '20%',
    'value' => function ($model) {
        $description = $model->marksheetDef->semester->semesterDescription->SEMESTER_DESC;
        $code = $model->marksheetDef->semester->SEMESTER_CODE;
        return $code . ' (' . $description . ')';
    },
    'contentOptions' => ['style' => 'white-space: nowrap;'],
    'headerOptions' => ['style' => 'white-space: nowrap;']
];

$groupNameColumn = [
    'attribute' => 'marksheetDef.group.GROUP_NAME',
    'label' => 'GROUP',
    'contentOptions' => ['style' => 'white-space: nowrap; width: 150px;'],
    'headerOptions' => ['style' => 'white-space: nowrap; width: 150px;'],
    'filterType' => GridView::FILTER_SELECT2,
    'filter' => ArrayHelper::map($data, 'marksheetDef.group.GROUP_NAME', 'marksheetDef.group.GROUP_NAME'),
    'filterWidgetOptions' => [
        'pluginOptions' => ['allowClear' => true],
    ],
    'filterInputOptions' => ['placeholder' => '-- All --'],
    'group' => true,
    'subGroupOf' => 1,
    'vAlign' => 'middle',
];

$academicYearColumn = [
    'attribute' => 'marksheetDef.semester.ACADEMIC_YEAR',
    'label' => 'ACADEMIC YEAR',
    'value' => function ($model) {
        return $model->marksheetDef->semester->ACADEMIC_YEAR;
    },
    'group' => true,
    'groupedRow' => true,
    'groupOddCssClass' => 'kv-grouped-row bg-primary text-white',
    'groupEvenCssClass' => 'kv-grouped-row bg-primary text-white',
    'groupHeader' => function ($model, $key, $index) {
        return '<i class="fas fa-calendar-alt"></i> <strong>Academic Year: ' .
            $model->marksheetDef->semester->ACADEMIC_YEAR . '</strong>';
    },
    'width' => '200px'
];

$degreeNameColumn = [
    'label' => 'PROGRAMME & LEVEL',
    'value' => function ($model) {
        $academicYear = $model->marksheetDef->semester->ACADEMIC_YEAR;
        $degreeName = $model->marksheetDef->semester->degreeProgramme->DEGREE_NAME;
        $level = $model->marksheetDef->semester->LEVEL_OF_STUDY;
        return $degreeName . ' - Level ' . $level . ' (' . $academicYear . ')';
    },
    'width' => '350px',
    'group' => true,
    'groupedRow' => true,
    'groupOddCssClass' => 'kv-grouped-row bg-info text-white',
    'groupEvenCssClass' => 'kv-grouped-row bg-info text-white',
    'groupHeader' => function ($model, $key, $index) {
        $degreeName = $model->marksheetDef->semester->degreeProgramme->DEGREE_NAME;
        $level = $model->marksheetDef->semester->LEVEL_OF_STUDY;
        return '<i class="fas fa-graduation-cap"></i> <strong>' . $degreeName . ' - Level ' . $level . '</strong>';
    }
];
$levelOfStudyColumn = [
    'attribute' => 'marksheetDef.semester.LEVEL_OF_STUDY',
    'label' => 'LEVEL',
    'value' => function ($model) {

        $levelsArry = [
            '1' => 'FIRST YEAR',
            '2' => 'SECOND YEAR',
            '3' => 'THIRD YEAR',
            '4' => 'FOURTH YEAR',
            '5' => 'FIFTH YEAR',
            '6' => 'SIXTH YEAR',
            '99' => 'MODULE II',
        ];

        return  $levelsArry[$model->marksheetDef->semester->LEVEL_OF_STUDY];
    },
    'contentOptions' => ['style' => 'white-space: nowrap;'],
    'filterType' => GridView::FILTER_SELECT2,
    'filter' => ArrayHelper::map($data, 'marksheetDef.semester.LEVEL_OF_STUDY', 'marksheetDef.semester.LEVEL_OF_STUDY'),
    'filterWidgetOptions' => [
        'pluginOptions' => ['allowClear' => true],
    ],
    'filterInputOptions' => ['placeholder' => '-- All --'],
    'group' => true,
    'subGroupOf' => 1,
    'vAlign' => 'middle',

];

$programmeGroupColumn = [
    'label' => 'PROGRAMME',
    'value' => function ($model) {
        $academicYear = $model->marksheetDef->semester->ACADEMIC_YEAR;
        $degreeName = $model->marksheetDef->semester->degreeProgramme->DEGREE_NAME;
        $degreeCode = $model->marksheetDef->semester->degreeProgramme->DEGREE_CODE;
        $semester = $model->marksheetDef->semester->SEMESTER_CODE;
        $semesterDesc = $model->marksheetDef->semester->semesterDescription->SEMESTER_DESC;


        return  $degreeCode . ' - ' . $degreeName . " - SEMESTER " . $semester . ' (' . $semesterDesc . ')';
    },
    'width' => '380px',
    'headerOptions' => ['style' => 'white-space: nowrap; min-width: 380px;'],
    'group' => true,
    'groupedRow' => true,
    'groupOddCssClass' => 'group-academic-year',
    'groupEvenCssClass' => 'group-academic-year',
    'groupHeader' => function ($model, $key, $index) {
        $academicYear = $model->marksheetDef->semester->ACADEMIC_YEAR;
        $degreeName = $model->marksheetDef->semester->degreeProgramme->DEGREE_NAME;

        return '<i class="fas fa-graduation-cap"></i> <strong>' .
            $academicYear . ' | ' . strtoupper($degreeName) . '</strong>';
    }
];

// $levelSemesterGroupColumn = [
//     'label' => 'LEVEL & SEMESTER',
//     'value' => function ($model) {
//         $academicYear = $model->marksheetDef->semester->ACADEMIC_YEAR;
//         $degreeName = $model->marksheetDef->semester->degreeProgramme->DEGREE_NAME;
//         $level = $model->marksheetDef->semester->LEVEL_OF_STUDY;
//         $semesterCode = $model->marksheetDef->semester->SEMESTER_CODE;
//         $description = $model->marksheetDef->semester->semesterDescription->SEMESTER_DESC;

// $levelsArry = [
//     '1' => 'FIRST YEAR',
//     '2' => 'SECOND YEAR',
//     '3' => 'THIRD YEAR',
//     '4' => 'FOURTH YEAR',
//     '5' => 'FIFTH YEAR',
//     '6' => 'SIXTH YEAR',
//     '99' => 'MODULE II',
// ];

//         return $levelsArry[$level] . ' <strong class="text-primary"> | </strong> SEMESTER ' . $semesterCode . ' <strong class="text-primary"> | </strong> ' . $description;
//     },
//     'format' => 'raw',
//     'width' => '400px',
//     'group' => true,
//     'groupedRow' => true,
//     'subGroupOf' => 1,
//     'groupOddCssClass' => 'group-programme',
//     'groupEvenCssClass' => 'group-programme',
//     'groupHeader' => function ($model, $key, $index) {
//         $level = $model->marksheetDef->semester->LEVEL_OF_STUDY;
//         $semesterCode = $model->marksheetDef->semester->SEMESTER_CODE;
//         $description = $model->marksheetDef->semester->semesterDescription->SEMESTER_DESC;

//         $levelsArry = [
//             '1' => 'FIRST YEAR',
//             '2' => 'SECOND YEAR',
//             '3' => 'THIRD YEAR',
//             '4' => 'FOURTH YEAR',
//             '5' => 'FIFTH YEAR',
//             '6' => 'SIXTH YEAR',
//             '99' => 'MODULE II',
//         ];

//         $levelText = $levelsArry[$level];

//         return '<i class="fas fa-layer-group"></i> <strong>' .
//             $levelText . ' | SEMESTER ' . $semesterCode . ' (' . $description . ')</strong>';
//     }
// ];

$academicYearFilterColumn = [
    'attribute' => 'academicYear',
    'label' => 'ACADEMIC YEAR',
    'filter' => false, // This will be handled by a separate dropdown filter
    'value' => function ($model) {
        return $model->marksheetDef->semester->ACADEMIC_YEAR;
    }
];
