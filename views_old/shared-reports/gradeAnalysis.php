<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var app\models\MarksheetDef $model
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\CourseAnalysisFilter $filter
 * @var string $title
 * @var string $panelHeading
 */

use app\components\GridExport;
use app\components\SmisHelper;
use app\models\DegreeProgramme;
use app\models\Group;
use app\models\LevelOfStudy;
use app\models\Marksheet;
use app\models\Semester;
use app\models\TempMarksheet;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

$degree = DegreeProgramme::find()->select(['DEGREE_NAME'])->where(['DEGREE_CODE' => $filter->degreeCode])
    ->asArray()->one();

$level = LevelOfStudy::find()->select(['NAME'])
    ->where(['LEVEL_OF_STUDY' => $filter->levelOfStudy])->asArray()->one();

$group = Group::find()->select(['GROUP_NAME'])->where(['GROUP_CODE' => $filter->group])
    ->asArray()->one();

$semesterId = $filter->academicYear . '_' . $filter->degreeCode . '_' . $filter->levelOfStudy .
    '_' . $filter->semester . '_' . $filter->group;

$semester = Semester::find()->alias('SM')
    ->select(['SM.SEMESTER_ID', 'SM.SEMESTER_CODE', 'SM.DESCRIPTION_CODE'])
    ->joinWith(['semesterDescription SD' => function($q){
        $q->select(['SD.DESCRIPTION_CODE', 'SD.SEMESTER_DESC']);
    }], true, 'INNER JOIN')
    ->where(['SM.SEMESTER_ID' => $semesterId])
    ->asArray()->one();
$sem = $semester['SEMESTER_CODE'] . ' (' . strtoupper($semester['semesterDescription']['SEMESTER_DESC']) . ')';

$this->title = $title;
$this->params['breadcrumbs'][] = [
    'label' => 'Course analysis report filters',
    'url' => ['/shared-reports/course-analysis-filters', 'level' => $filter->approvalLevel]
];
$this->params['breadcrumbs'][] = $this->title;

// build grid columns
$gridId = 'courses-grade-analysis';
$marksheetIdColumn = [
    'attribute' => 'MRKSHEET_ID',
    'label' => 'MARKSHEET ID'
];
$courseCodeColumn = [
    'attribute' => 'course.COURSE_CODE',
    'label' => 'CODE',
    'width' => '7%'
];
$courseNameColumn = [
    'attribute' => 'course.COURSE_NAME',
    'label' => 'NAME'
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
    'header' => 'ADDITIONAL REPORTS',
    'template' => '{consolidated-marks} {class-performance-graphical} {missing-marks}',
    'contentOptions' => ['style'=>'white-space:nowrap;','class'=>'kartik-sheet-style kv-align-middle'],
    'buttons' => [
        'consolidated-marks' => function($url, $model){
            return Html::a('<i class="fas fa-eye"></i> Consolidated marks ',
                Url::to(['/shared-reports/consolidated-marks', 'marksheetId' => $model['MRKSHEET_ID']]),
                [
                    'title' => 'View consolidated marks report',
                    'class' => 'btn btn-xs btn-spacer'
                ]
            );
        },
        'class-performance-graphical' => function($url, $model){
            return Html::a('<i class="fas fa-eye"></i> Class performance',
                Url::to(['/shared-reports/class-performance', 'marksheetId' => $model['MRKSHEET_ID']]),
                [
                    'title' => 'View class performance report',
                    'class' => 'btn btn-xs btn-spacer'
                ]
            );
        },
        'missing-marks' => function($url, $model){
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
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading"><b>Please note the following</b></div>
            <div class="panel-body">
                <p>
                    1. You can update these reports by clicking on the link
                    <?= Html::a('<i class="fas fa-eye"></i> Consolidated marks ',
                        '#',
                        ['title' => 'View consolidated marks report', 'class' => 'btn btn-xs'])
                    ?>
                    in the table below and then returning to this page.
                </p>
            </div>
        </div>
    </div>
</div>

<?php
echo $this->render('activeFilters', ['filter' => $filter]);
echo $this->render('moreFilters', ['filter' => $filter]);
?>

<?php
// render course columns

$fileName = $filter->academicYear . '_' . $filter->degreeCode . '_' . $level['NAME'] . '_sem_' . $sem . '_' . $group['GROUP_NAME'];
$fileName .= '_grade_analysis_report';
$fileName = strtolower($fileName);
$fileName = str_replace(' ', '_', $fileName);

try {
    $contentBefore = '<p style="color:#333333; font-weight: bold;">ACADEMIC YEAR: ' . $filter->academicYear . '</p>';
    $contentBefore .= '<p style="color:#333333; font-weight: bold;">PROGRAMME: ' . $degree['DEGREE_NAME'] . ' (' . $filter->degreeCode . ') </p>';
    $contentBefore .= '<p style="color:#333333; font-weight: bold;">LEVEL OF STUDY: ' . strtoupper($level['NAME']) . '</p>';
    $contentBefore .= '<p style="color:#333333; font-weight: bold;">SEMESTER: ' . $sem . '</p>';
    $contentBefore .= '<p style="color:#333333; font-weight: bold;">GROUP: ' . strtoupper($group['GROUP_NAME']) . '</p>';

    $contentAfter = '<p style="color:#333333; font-weight: bold;">Dean signature: ................................................</p>';

    echo GridView::widget([
        'id' => $gridId,
        'dataProvider' => $dataProvider,
        'headerRowOptions' => [
            'class' => 'kartik-sheet-style'
        ],
        'filterRowOptions' => [
            'class' => 'kartik-sheet-style'
        ],
        'pjax' => true,
        'pjaxSettings' => [
            'options' => [
                'id' => $gridId . '-pjax'
            ]
        ],
        'toolbar' => [
            '{export}',
            '{toggleData}'
        ],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<h3 class="panel-title">' . $panelHeading . '</h3>',
        ],
        'persistResize' => false,
        'toggleDataContainer' => [
            'class' => 'btn-group mr-2'
        ],
        'toggleDataOptions' => [
            'minCount' => 50
        ],
        'export' => [
            'fontAwesome' => false,
            'label' => 'Download report'
        ],
        'exportConfig' => [
            GridView::PDF => GridExport::exportPdf([
                'filename' => $fileName,
                'title' => 'Grade analysis report',
                'subject' => 'grade analysis',
                'keywords' => 'grade analysis',
                'contentBefore' => $contentBefore,
                'contentAfter' => $contentAfter,
                'centerContent' => 'Grade analysis report'
            ]),
        ],
        'itemLabelSingle' => 'course',
        'itemLabelPlural' => 'courses',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
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
        ]
    ]);
} catch (Exception $ex) {
    $message = $ex->getMessage();
    if (YII_ENV_DEV) {
        $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}

