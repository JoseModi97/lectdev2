<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var app\models\TempMarksheet $model
 * @var app\models\search\MarksPreviewSearch $marksPreviewSearch
 * @var yii\data\ActiveDataProvider $marksPreviewProvider
 * @var string $title
 * @var string $panelHeading
 * @var string[] $course
 * @var string $marksheetId
 */

use app\components\GridExport;
use app\components\SmisHelper;
use app\models\MarksheetDef;
use app\models\ProjectDescription;
use kartik\grid\GridView;
use yii\web\ServerErrorHttpException;

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;

$regNumColumn = [
    'attribute' => 'REGISTRATION_NUMBER',
    'label' => 'REGISTRATION NUMBER',
    'hAlign' => 'left',
    'width' => '15%'
];
$surnameColumn = [
    'attribute' => 'student.SURNAME',
    'label' => 'SURNAME',
    'hAlign' => 'left',
    'width' => '15%',
    'value' => function($model){
        return $model['student']['SURNAME'];
    }
];
$otherNamesColumn =[
    'attribute' => 'student.OTHER_NAMES',
    'label' => 'OTHER NAMES',
    'hAlign' => 'left',
    'width' => '25%',
    'value' => function($model){
        return $model['student']['OTHER_NAMES'];
    }
];
$examTypeColumn = [
    'attribute' => 'EXAM_TYPE',
    'label' => 'EXAM TYPE',
    'hAlign' => 'left'
];
$cwMarksColumn = [
    'attribute' => 'COURSE_MARKS',
    'label' => 'CW MARKS',
    'hAlign' => 'left',
    'value' => function($model){
        if(is_null($model['COURSE_MARKS'])){
            return '--';
        }
        return $model['COURSE_MARKS'];
    }
];
$examMarksColumn = [
    'attribute' => 'EXAM_MARKS',
    'label' => 'EXAM MARKS',
    'hAlign' => 'left',
    'value' => function($model){
        if(is_null($model['EXAM_MARKS'])){
            return '--';
        }
        return $model['EXAM_MARKS'];
    }
];
$totalMarksColumn = [
    'attribute' => 'FINAL_MARKS',
    'label' => 'FINAL MARKS',
    'hAlign' => 'left',
    'value' => function($model){
        if(is_null($model['FINAL_MARKS'])){
            return '--';
        }
        return $model['FINAL_MARKS'];
    }
];
$gradeColumn = [
    'attribute' => 'GRADE',
    'label' => 'GRADE',
    'hAlign' => 'left',
    'value' => function($model){
        if(is_null($model['GRADE'])){
            return '--';
        }
        return $model['GRADE'];
    }
];


try {
    $marksheetModel = MarksheetDef::find()->select(['COURSE_ID'])->where(['MRKSHEET_ID' => $marksheetId])->asArray()->one();
    $courseId = $marksheetModel['COURSE_ID'];
    $isAProjectCourse = SmisHelper::isAProjectCourse($marksheetId);
    if ($isAProjectCourse) {
        $projectHours = '';

        $projectTitleColumn = [
            'label' => 'TITLE',
            'hAlign' => 'left',
            'width' => '15%',
            'format' => 'raw',
            'value' => function ($model) use ($courseId, &$projectHours){
                $projectDescription = ProjectDescription::find()
                    ->select(['PROJECT_TITLE', 'HOURS'])
                    ->where(['REGISTRATION_NUMBER' => $model['REGISTRATION_NUMBER'], 'PROJECT_CODE' => $courseId])
                    ->asArray()->one();

                if(!empty($projectDescription)){
                    $projectHours = $projectDescription['HOURS'];
                    return is_null($projectDescription['PROJECT_TITLE']) ? '' : $projectDescription['PROJECT_TITLE'];
                }

                return '';
            }
        ];

        $projectHoursColumn = [
            'label' => 'HOURS',
            'hAlign' => 'left',
            'width' => '8%',
            'format' => 'raw',
            'value' => function($model) use (&$projectHours){
                return is_null($projectHours) ? '' : $projectHours;
            }
        ];

        $gridColumns = [
            [
                'class'=>'kartik\grid\SerialColumn',
                'width' => '2%'
            ],
            $regNumColumn,
            $surnameColumn,
            $otherNamesColumn,
            $examTypeColumn,
            $cwMarksColumn,
            $examMarksColumn,
            $totalMarksColumn,
            $gradeColumn,
            $projectTitleColumn,
            $projectHoursColumn,
        ];
    }else{
        $gridColumns = [
            [
                'class'=>'kartik\grid\SerialColumn',
                'width' => '2%'
            ],
            $regNumColumn,
            $surnameColumn,
            $otherNamesColumn,
            $examTypeColumn,
            $cwMarksColumn,
            $examMarksColumn,
            $totalMarksColumn,
            $gradeColumn
        ];
    }
} catch (Exception $ex) {
    $message = $ex->getMessage();
    if (YII_ENV_DEV) {
        $message = $message . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}

$gridId = 'marks-preview';
$fileName = $course['COURSE_CODE'] . '_consolidated_marks_preview';
$centerContent = $course['COURSE_CODE'] . ' marks';
try {
    echo GridView::widget([
        'id' => $gridId,
        'dataProvider' => $marksPreviewProvider,
        'filterModel' => $marksPreviewSearch,
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
        ],
        'exportConfig' => [
            GridView::PDF => GridExport::exportPdf([
                'filename' => $fileName,
                'title' => $title,
                'subject' => 'marks',
                'keywords' => 'marks preview',
                'centerContent' => $centerContent,
                'contentBefore' => '',
                'contentAfter' => '',
            ]),
            GridView::EXCEL => GridExport::exportExcel([
                'filename' => $fileName,
                'worksheet' => $course['COURSE_CODE'] . '_marks'
            ])
        ],
        'itemLabelSingle' => 'student',
        'itemLabelPlural' => 'students',
        'columns' => $gridColumns
    ]);
} catch (Exception $ex) {
    $message = $ex->getMessage();
    if (YII_ENV_DEV) {
        $message = $message . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}
?>
