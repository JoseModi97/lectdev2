<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var app\models\MarksheetDef $model
 * @var app\models\search\CreatedMarksheetsSearch $createdMarksheetsSearch
 * @var yii\data\ActiveDataProvider $createdMarksheetsProvider
 * @var string $title
 * @var string $academicYear
 * @var string $deptName
 * @var string $facCode
 * @var string $level
 */

use app\components\GridExport;
use app\models\Group;
use app\models\Semester;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
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
$deptTimetablesUrl = '';
if($level === 'dean'){
    $deptTimetablesUrl = '/dean-reports/department-timetables';
}elseif ($level === 'facAdmin'){
    $deptTimetablesUrl = '/faculty-admin-reports/department-timetables';
}elseif ($level === 'sysAdmin'){
    $deptTimetablesUrl = '/system-admin-reports/department-timetables';
}
$this->params['breadcrumbs'][] = [
    'label' => 'DEPARTMENT TIMETABLES',
    'url' => [$deptTimetablesUrl, 'facCode' => $facCode, 'academicYear' => $academicYear]
];

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="created-timetables">
    <h3>DEPARTMENT OF  <?= $deptName;?></h3>
    <?php
    $gridId = 'programmes-timetables';
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
            'width' => '55%'
        ],
    ];

    $gridId = 'created-timetables-programmes';
    $title = 'Department marksheets for the academic year ' . $academicYear;
    $fileName = 'programmes_timetables';

    try {
        echo GridView::widget([
            'id' => $gridId,
            'dataProvider' => $createdMarksheetsProvider,
            'filterModel' => $createdMarksheetsSearch,
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
                    'subject' => 'programmes timetables',
                    'keywords' => 'programmes timetables',
                    'contentBefore' => '',
                    'contentAfter' => '',
                    'centerContent' => $title
                ]),
            ],
            'itemLabelSingle' => 'course',
            'itemLabelPlural' => 'courses',
        ]);
    } catch (Exception $ex) {
        $message = 'There were errors while trying to display the programmes timetables.';
        if(YII_ENV_DEV){
            $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
        }
        throw new ServerErrorHttpException($message, 500);
    }
    ?>
</div>

