<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var app\models\MarksheetDef $model
 * @var yii\data\ActiveDataProvider $programmeCourseWorkProvider
 * @var app\models\search\ProgrammeCourseWorkSearch $programmeCourseWorkSearch
 * @var string $title
 * @var string $academicYear
 * @var string $departmentName
 * @var string $departmentCode
 * @var string $facultyCode
 * @var string $level
 */

use app\components\GridExport;
use app\models\CourseWorkAssessment;
use app\models\Group;
use app\models\Semester;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

$semesters = Semester::find()->select(['SEMESTER_CODE'])->distinct()
    ->orderBy(['SEMESTER_CODE' => SORT_ASC])->all();
$semestersList = ArrayHelper::map($semesters, 'SEMESTER_CODE', function($sem){
    return $sem->SEMESTER_CODE;
});

$levels = Semester::find()->select(['LEVEL_OF_STUDY'])->distinct()
    ->orderBy(['LEVEL_OF_STUDY' => SORT_ASC])->all();
$levelList = ArrayHelper::map($levels, 'LEVEL_OF_STUDY', function($level){
    return $level->LEVEL_OF_STUDY;
});

$groups = Group::find()->select(['GROUP_NAME'])->all();
$groupsList = ArrayHelper::map($groups, 'GROUP_NAME', function($group){
    return $group->GROUP_NAME;
});

$this->title = $title;

$cwInDeptsUrl = '';
$cwDefinitionsUrl = '';
if($level === 'dean'){
    $cwInDeptsUrl = '/dean-reports/course-work-definition-in-departments';
    $cwDefinitionsUrl = '/dean-reports/course-work-definitions';
}elseif ($level === 'facAdmin'){
    $cwInDeptsUrl = '/faculty-admin-reports/course-work-definition-in-departments';
    $cwDefinitionsUrl = '/faculty-admin-reports/course-work-definitions';
}elseif ($level === 'sysAdmin'){
    $cwInDeptsUrl = '/system-admin-reports/course-work-definition-in-departments';
    $cwDefinitionsUrl = '/system-admin-reports/course-work-definitions';
}
$this->params['breadcrumbs'][] = [
    'label' => 'COURSE WORK DEFINITIONS IN DEPARTMENTS',
    'url' => [
        $cwInDeptsUrl,
        'facCode' => $facultyCode,
        'academicYear' => $academicYear
    ]
];
?>

<div class="course-work-definition-in-programmes">
    <h6> DEPARTMENT OF <?= $departmentName; ?></h6>
    <?php
    $gridId = 'cw-definition-in-programmes';
    $gridColumns = [
        [
            'class' => 'kartik\grid\SerialColumn',
            'width' => '5%'
        ],
        [
            'attribute' => 'semester.degreeProgramme.DEGREE_NAME',
            'label' => 'DEGREE NAME',
            'value' => function($model){
                return $model['semester']['degreeProgramme']['DEGREE_NAME'];
            },
            'group' => true,
            'width' => '20%'
        ],
        [
            'attribute' => 'semester.LEVEL_OF_STUDY',
            'label' => 'LEVEL',
            'value' => function($model){
                return $model['semester']['LEVEL_OF_STUDY'];
            },
            'filterType' => GridView::FILTER_SELECT2,
            'filter' => $levelList,
            'vAlign' => 'middle',
            'filterWidgetOptions' => [
                'options'=>['id'=>$gridId.'-study-level'],
                'pluginOptions' => ['allowClear' => true],
            ],
            'filterInputOptions' => ['placeholder' => '-- ALL --'],
            'width' => '5%'
        ],
        [
            'attribute' => 'group.GROUP_NAME',
            'label' => 'GROUP',
            'filterType' => GridView::FILTER_SELECT2,
            'filter' => $groupsList,
            'vAlign' => 'middle',
            'filterWidgetOptions' => [
                'options'=>['id'=>$gridId.'-groups'],
                'pluginOptions' => ['allowClear' => true],
            ],
            'filterInputOptions' => ['placeholder' => '-- ALL --'],
            'width' => '10%'
        ],
        [
            'attribute' => 'semester.SEMESTER_CODE',
            'label' => 'SEMESTER',
            'value' => function($model){
                return $model['semester']['SEMESTER_CODE'];
            },
            'filterType' => GridView::FILTER_SELECT2,
            'filter' => $semestersList,
            'vAlign' => 'middle',
            'filterWidgetOptions' => [
                'options'=>['id'=>$gridId.'-semester'],
                'pluginOptions' => ['allowClear' => true],
            ],
            'filterInputOptions' => ['placeholder' => '-- ALL --'],
            'width' => '5%'
        ],
        [
            'attribute' => 'course.COURSE_CODE',
            'label' => 'COURSE',
            'value' => function($model){
                return $model['course']['COURSE_CODE'] .' - '.$model['course']['COURSE_NAME'];
            },
            'width' => '40%'
        ],
        [
            'label' => 'COURSE WORK DEFINED',
            'width' => '15%',
            'value' => function($model){
                $marksheetId = $model['MRKSHEET_ID'];
                return CourseWorkAssessment::find()->alias('AS')
                    ->joinWith(['assessmentType AT'])
                    ->where(['AS.MARKSHEET_ID' => $marksheetId])
                    ->andWhere(['NOT', ['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT']])
                    ->andWhere(['NOT', ['AT.ASSESSMENT_NAME' => 'EXAM']])
                    ->count();
            }
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'template' => '{course-works}',
            'contentOptions' => ['style'=>'white-space:nowrap;','class'=>'kartik-sheet-style kv-align-middle'],
            'buttons' => [
                'course-works' => function ($url, $model) use($departmentCode, $academicYear, $cwDefinitionsUrl){
                    return Html::a('<i class="fas fa-file"></i> Course work definition',
                        Url::to([
                            $cwDefinitionsUrl,
                            'marksheetId' => $model['MRKSHEET_ID'],
                            'deptCode' => $departmentCode,
                            'academicYear' => $academicYear
                        ]),
                        [
                            'title' => 'Course work definition report',
                            'class' => 'btn btn-xs course-work-definition'
                        ]
                    );
                }
            ],
            'hAlign' => 'center',
        ]
    ];

    $title = 'Course work definitions for the academic year ' . $academicYear;
    $fileName = 'course_work_definition_in_programmes';
    try {
        echo GridView::widget([
            'id' => $gridId,
            'dataProvider' => $programmeCourseWorkProvider,
            'filterModel' => $programmeCourseWorkSearch,
            'columns' => $gridColumns,
            'headerRowOptions' => ['class' => 'kartik-sheet-style'],
            'filterRowOptions' => ['class' => 'kartik-sheet-style'],
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
                'heading' => '<h3 class="panel-title">' . $title . '</h3>',
            ],
            'toggleDataContainer' => ['class' => 'btn-group mr-2'],
            'toggleDataOptions' => ['minCount' => 50],
            'exportConfig' => [
                GridView::EXCEL => GridExport::exportExcel([
                    'filename' => $fileName,
                    'worksheet' => 'programmes'
                ]),
                GridView::PDF => GridExport::exportPdf([
                    'filename' => $fileName,
                    'title' => $title,
                    'subject' => 'programmes course work',
                    'keywords' => 'programmes course work',
                    'contentBefore' => '',
                    'contentAfter' => '',
                    'centerContent' => $title
                ]),
            ],
            'itemLabelSingle' => 'programme',
            'itemLabelPlural' => 'programmes',
        ]);
    } catch (Exception $ex) {
        $message = 'An error occurred while creating the table grid.';
        if(YII_ENV_DEV){
            $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
        }
        throw new ServerErrorHttpException($message, 500);
    }
    ?>
</div>
