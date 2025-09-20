<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc Show columns pending/approved courses
 */

/**
 * @var app\models\MarksApprovalFilter $filter
 * @var string $resultsType
 * @var string $filtersInterface
 */

use app\components\SmisHelper;
use app\models\TempMarksheet;
use yii\helpers\Html;
use yii\helpers\Url;

$checkboxColumn = [
    'class' => '\kartik\grid\CheckboxColumn',
    'checkboxOptions' => function($model, $key, $index, $widget) {
        return [
            'value' => $model['MRKSHEET_ID']
        ];
    }
];
$marksheetIdColumn = [
    'attribute' => 'MRKSHEET_ID',
    'label' => 'MARKSHEET ID'
];
$levelColumn = [
    'label' => 'LEVEL',
    'group' => true,
    'value' => function($model){
        return $model['semester']['levelOfStudy']['NAME'];
    }
];
$groupColumn = [
    'label' => 'GROUP',
    'group' => true,
    'value' => function($model){
        return $model['group']['GROUP_NAME'];
    }
];
$semesterColumn = [
    'label' => 'SEMESTER',
    'group' => true,
    'value' => function($model){
        return $model['semester']['SEMESTER_CODE'] . ' (' . $model['semester']['semesterDescription']['SEMESTER_DESC'] . ')';
    }
];
$courseCodeColumn = [
    'label' => 'CODE',
    'width' => '7%',
    'value' => function($model){
        return $model['course']['COURSE_CODE'];
    }
];
$courseNameColumn = [
    'label' => 'NAME',
    'value' => function($model){
        return $model['course']['COURSE_NAME'];
    }
];
$gradeAColumn = [
    'label' => 'A',
    'value' => function($model){
        return TempMarksheet::find()->where([
            'MRKSHEET_ID' => $model['MRKSHEET_ID'],
            'GRADE' => 'A     '
        ])->count();
    }
];
$gradeBColumn = [
    'label' => 'B',
    'value' => function($model){
        return TempMarksheet::find()->where([
            'MRKSHEET_ID' => $model['MRKSHEET_ID'],
            'GRADE' => 'B     '
        ])->count();
    }
];
$gradeCColumn = [
    'label' => 'C',
    'value' => function($model){
        return TempMarksheet::find()->where([
            'MRKSHEET_ID' => $model['MRKSHEET_ID'],
            'GRADE' => 'C     '
        ])->count();
    }
];
$gradeCStarColumn = [
    'label' => 'C*',
    'value' => function($model){
        return TempMarksheet::find()->where([
            'MRKSHEET_ID' => $model['MRKSHEET_ID'],
            'GRADE' => 'C*    '
        ])->count();
    }
];
$gradeDColumn = [
    'label' => 'D',
    'value' => function($model){
        return TempMarksheet::find()->where([
            'MRKSHEET_ID' => $model['MRKSHEET_ID'],
            'GRADE' => 'D     '
        ])->count();
    }
];
$gradeEColumn = [
    'label' => 'E',
    'value' => function($model){
        return TempMarksheet::find()->where([
            'MRKSHEET_ID' => $model['MRKSHEET_ID'],
            'GRADE' => 'E     '
        ])->count();
    }
];
$gradeEStarColumn = [
    'label' => 'E*',
    'value' => function($model){
        return TempMarksheet::find()->where([
            'MRKSHEET_ID' => $model['MRKSHEET_ID'],
            'GRADE' => 'E*    '
        ])->count();
    }
];
$gradeFColumn = [
    'label' => 'F',
    'value' => function($model){
        return TempMarksheet::find()->where([
            'MRKSHEET_ID' => $model['MRKSHEET_ID'],
            'GRADE' => 'F     '
        ])->count();
    }
];
$gradeNullColumn = [
    'label' => 'NOT GRADED',
    'width' => '7%',
    'value' => function($model){
        return TempMarksheet::find()->where([
            'MRKSHEET_ID' => $model['MRKSHEET_ID'],
            'GRADE' => null
        ])->count();
    }
];

$sumScore = null;
$averageScore = null;
$totalCount = null;
$averageScoreColumn = [
    'label' => 'AVG SCORE',
    'width' => '7%',
    'value' => function($model){
        $sumScore = TempMarksheet::find()->where(['MRKSHEET_ID' => $model['MRKSHEET_ID']])
            ->andWhere(['NOT', ['FINAL_MARKS' => NULL]])->sum('FINAL_MARKS');
        $totalCount = TempMarksheet::find()->where(['MRKSHEET_ID' => $model['MRKSHEET_ID']])
            ->andWhere(['NOT', ['FINAL_MARKS' => NULL]])->count();

        if(intval($sumScore) === 0 || intval($totalCount) === 0){
            return '--';
        }

        return round(($sumScore/$totalCount), 2);
    }
];
$averageGradeColumn = [
    'label' => 'AVG GRADE',
    'width' => '7%',
    'value' => function($model){
        $averageGrade = '--';
        $sumScore = TempMarksheet::find()->where(['MRKSHEET_ID' => $model['MRKSHEET_ID']])
            ->andWhere(['NOT', ['FINAL_MARKS' => NULL]])->sum('FINAL_MARKS');
        $totalCount = TempMarksheet::find()->where(['MRKSHEET_ID' => $model['MRKSHEET_ID']])
            ->andWhere(['NOT', ['FINAL_MARKS' => NULL]])->count();

        if(intval($sumScore) === 0 || intval($totalCount) === 0){
            return '--';
        }

        $averageScore = round(($sumScore/$totalCount), 2);
        $grades = SmisHelper::getGradingDetails($model['MRKSHEET_ID']);
        foreach ($grades as $grade) {
            if ($averageScore >= $grade['lowerBound'] && $averageScore <= $grade['upperBound']){
                $averageGrade = $grade['grade'];
                break;
            }
        }
        return $averageGrade;
    }
];

$totalCountColumn = [
    'label' => 'TOTAL STUDENTS',
    'width' => '10%',
    'value' => function($model){
        return TempMarksheet::find()->where(['MRKSHEET_ID' => $model['MRKSHEET_ID']])->count();
    }
];

$actionColumn = [
    'class' => 'kartik\grid\ActionColumn',
    'header' => 'ACTIONS | REPORTS',
    'template' => '{approve} {mark-back} {consolidated-marks} {class-performance-graphical} {missing-marks}',
    'contentOptions' => ['style' => 'white-space:nowrap;', 'class' => 'kartik-sheet-style kv-align-middle'],
    'buttons' => [
        'approve' => function ($url, $model) use ($filter, $resultsType) {
            if($resultsType === 'pending'){
                return Html::button('<i class="fa fa-check"></i> Approve', [
                    'title' => 'Approve Marks',
                    'id' => 'marks-approve-btn',
                    'class' => 'btn btn-spacer btn-create',
                    'data-marksheetId' => $model['MRKSHEET_ID'],
                    'data-level' => $filter->approvalLevel
                ]);
            }
            return '';
        },
        'mark-back' => function ($url, $model) use ($filter) {
            return Html::button('<i class="fa fa-undo"></i> Mark back', [
                'title' => 'Approve Marks',
                'id' => 'mark-back-btn',
                'class' => 'btn btn-spacer btn-delete',
                'data-marksheetId' => $model['MRKSHEET_ID'],
                'data-level' => $filter->approvalLevel
            ]);
        },
        'consolidated-marks' => function ($url, $model) {
            return Html::a('<i class="fas fa-eye"></i> Consolidated marks ',
                Url::to(['/shared-reports/consolidated-marks', 'marksheetId' => $model['MRKSHEET_ID']]),
                [
                    'title' => 'View consolidated marks report',
                    'class' => 'btn btn-xs btn-spacer'
                ]
            );
        },
        'class-performance-graphical' => function ($url, $model) {
            return Html::a('<i class="fas fa-eye"></i> Class performance',
                Url::to(['/shared-reports/class-performance', 'marksheetId' => $model['MRKSHEET_ID']]),
                [
                    'title' => 'View class performance report',
                    'class' => 'btn btn-xs btn-spacer'
                ]
            );
        },
        'missing-marks' => function ($url, $model) {
            return Html::a('<i class="fas fa-eye"></i> Missing marks',
                Url::to(['/shared-reports/assessments', 'marksheetId' => $model['MRKSHEET_ID']]),
                [
                    'title' => 'View missing marks report',
                    'class' => 'btn btn-xs'
                ]
            );
        }
    ]
];

$columns = [
    ['class' => 'yii\grid\SerialColumn'],
    $checkboxColumn,
    $levelColumn,
    $groupColumn,
    $semesterColumn,
    $courseCodeColumn,
    $gradeAColumn,
    $gradeBColumn,
    $gradeCColumn,
    $gradeCStarColumn,
    $gradeDColumn,
    $gradeEColumn,
    $gradeEStarColumn,
    $gradeFColumn,
    $gradeNullColumn,
    $averageScoreColumn,
    $averageGradeColumn,
    $totalCountColumn,
    $actionColumn
];

if($resultsType === 'approved'){
    unset($columns[1]);
}

if($filtersInterface === '1') {
    unset($columns[2]);
    unset($columns[3]);
    unset($columns[4]);
}

