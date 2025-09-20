<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var app\models\MarksheetDef $model
 * @var app\models\search\MarksApprovalSearch $searchModel
 * @var yii\data\ActiveDataProvider $marksProvider
 * @var app\models\MarksApprovalFilter $filter
 * @var string $title
 * @var string $deptCode
 * @var string $deptName
 * @var string $level
 * @var string $facName
 * @var string $facCode
 * @var string $panelHeading
 * @var array $listOfGroups
 * @var array $listOfLevels
 */

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

$this->title = $title;
$this->params['breadcrumbs'][] = [
    'label' => 'Course filters',
    'url' => ['/marks-approval/filters', 'approvalLevel' => $filter->approvalLevel, 'degreeCode' => $filter->degreeCode]
];
$this->params['breadcrumbs'][] = $this->title;

// Build grid columns
$gridId = 'courses-in-a-department';
$marksheetId = [
    'attribute' => 'MRKSHEET_ID',
    'label' => 'MARKSHEET ID'
];
$courseCodeColumn = [
    'attribute' => 'course.COURSE_CODE',
    'label' => 'COURSE CODE',
    'width' => '10%'
];
$courseNameColumn = [
    'attribute' => 'course.COURSE_NAME',
    'label' => 'COURSE NAME'
];
$groupColumn = [
    'attribute' => 'group.GROUP_CODE',
    'label' => 'GROUP',
    'filterType' => GridView::FILTER_SELECT2,
    'filter' => $listOfGroups,
    'vAlign' => 'middle',
    'format' => 'raw',
    'width' => '25%',
    'filterWidgetOptions' => [
        'options'=>[
            'id' => $gridId . '-groups',
            'placeholder' => '--- all ---'
        ],
        'pluginOptions' => [
            'allowClear' => true,
            'autoclose' => true
        ]
    ],
    'value' => function($model){
        return $model['group']['GROUP_NAME'];
    }
];
$levelOfStudyColumn = [
    'attribute' => 'semester.levelOfStudy.LEVEL_OF_STUDY',
    'label' => 'LEVEL OF STUDY',
    'filterType' => GridView::FILTER_SELECT2,
    'filter' => $listOfLevels,
    'vAlign' => 'middle',
    'format' => 'raw',
    'width' => '20%',
    'filterWidgetOptions' => [
        'options'=>[
            'id' => $gridId . '-levels',
            'placeholder' => '--- all ---'
        ],
        'pluginOptions' => [
            'allowClear' => true,
            'autoclose' => true
        ]
    ],
    'value' => function($model){
        return $model['semester']['levelOfStudy']['NAME'];
    }
];
$actionColumn = [
    'class' => 'kartik\grid\ActionColumn',
    'header' => 'ACTIONS',
    'template' => '{view-uploaded-marks}',
    'contentOptions' => [
        'style'=>'white-space:nowrap;',
        'class'=>'kartik-sheet-style kv-align-middle'
    ],
    'buttons' => [
        'view-uploaded-marks' => function($url, $model) use ($level) {
            return Html::a('<i class="fas fa-eye"></i> uploaded marks',
                Url::to(['/marks-approval/assessments-with-marks', 'marksheetId' => $model['MRKSHEET_ID'],
                    'level' => $level]),
                [
                    'title' => 'View uploaded marks',
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
                <?php if($level === 'hod'):?>
                    <p>1. Only courses with marks submitted to the HOD will be shown.</p>
                <?php elseif ($level === 'dean'):?>
                    <p>1. Only courses with marks submitted to the dean will be shown.</p>
                <?php endif;?>
            </div>
        </div>
    </div>
</div>

<?php
echo $this->render('activeFilters', ['filter' => $filter]);
?>

<?php
// Render programmes table grid
try {
    echo GridView::widget([
        'id' => $gridId,
        'dataProvider' => $marksProvider,
        'filterModel' => $searchModel,
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
        'itemLabelSingle' => 'course',
        'itemLabelPlural' => 'courses',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            $courseCodeColumn,
            $courseNameColumn,
            $groupColumn,
            $levelOfStudyColumn,
            $actionColumn
        ]
    ]);
} catch (Exception $ex) {
    $message = 'An error occurred while trying to display the courses in the department';
    if (YII_ENV_DEV) {
        $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}


