<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var app\models\StudentCoursework $cwModel
 * @var app\models\search\StudentCourseworkSearch $searchModel
 * @var yii\data\ActiveDataProvider $cwProvider
 * @var string $title
 * @var string $courseName
 * @var string $courseCode
 * @var string $assessmentName
 * @var string $marksheetId
 * @var string $assessmentId
 * @var string $type
 * @var bool $marksForSingleExam
 */

use app\components\SmisHelper;
use app\models\EmpVerifyView;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

$this->title = $title;
$this->params['breadcrumbs'][] = [
    'label' => 'MY COURSE ALLOCATIONS',
    'url' => [
        '/allocated-courses'
    ]
];

if(!$marksForSingleExam){
    $labelName = ($type === 'component') ? 'EXAM COMPONENTS' : 'COURSEWORK';
    $this->params['breadcrumbs'][] = [
        'label' => $labelName,
        'url' => [
            '/assessments',
            'marksheetId' => $marksheetId,
            'type' => $type
        ]
    ];
}

$this->params['breadcrumbs'][] = $this->title;

// build table grid columns
$idColumn = [
    'label' => 'ID',
    'value' => function($model){
        return $model['COURSE_WORK_ID'];
    },
    'hAlign' => 'left'
];
$registrationNoColumn = [
    'attribute' => 'REGISTRATION_NUMBER',
    'label' => 'REGISTRATION NUMBER',
    'hAlign' => 'left',
];
$surnameColumn = [
    'attribute' => 'student.SURNAME',
    'label' => 'SURNAME',
    'hAlign' => 'left',
    'value' => function($model){
        return $model['student']['SURNAME'];
    }
];
$otherNamesColumn = [
    'attribute' => 'student.OTHER_NAMES',
    'label' => 'OTHER NAMES',
    'hAlign' => 'left',
    'value' => function($model){
        return $model['student']['OTHER_NAMES'];
    }
];
$marksColumn = [
    'attribute' => 'MARK',
    'label' => 'WEIGHTED MARKS',
    'hAlign' => 'left'
];
$userColumn = [
    'attribute' => 'USER_ID',
    'label' => 'ENTERED BY',
    'hAlign' => 'left',
    'width' => '20%',
    'value' => function($model){
        $lecturer = EmpVerifyView::find()
            ->select(['PAYROLL_NO', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE'])
            ->where(['PAYROLL_NO' => $model['USER_ID']])->one();
        return $lecturer->EMP_TITLE.' '.$lecturer->SURNAME.' '.$lecturer->OTHER_NAMES;
    }
];
$dateColumn = [
    'attribute' => 'DATE_ENTERED',
    'label' => 'DATE ENTERED',
    'hAlign' => 'left',
    'width' => '20%',
    'format' => 'raw',
    'contentOptions'=>['class'=>'kartik-sheet-style kv-align-middle'],
    'filterType' => GridView::FILTER_DATE,
    'filterWidgetOptions' => [
        'options'=>[
            'id' => 'assessment-marks-date-entered'
        ],
        'pluginOptions' => [
            'autoclose' => true,
            'allowClear' => true,
            'format' => 'dd-M-yyyy'
        ]
    ],
    'filterInputOptions' => [
        'placeholder' => 'Date Entered'
    ],
    'value' => function($model){
        return Yii::$app->formatter->asDate($model['DATE_ENTERED'], 'full');
    }
];
$remarksColumn = [
    'label' => 'REMARKS',
    'value' => function($model){
        return is_null($model['REMARKS']) ? '' : $model['REMARKS'];
    },
    'width' => '20%',
    'hAlign' => 'left'
];
$actionColumn = [
    'class' => 'kartik\grid\ActionColumn',
    'template' => '{delete-marks}',
    'contentOptions' => ['style'=>'white-space:nowrap;','class'=>'kartik-sheet-style kv-align-middle'],
    'buttons' => [
        'delete-marks' => function ($url, $model){
            return Html::button('<i class="fas fa-trash"></i> marks',[
                'title' => 'Delete marks',
                'class' => 'btn btn-delete delete-marks btn-xs',
                'href' => Url::to(['/marks/delete']),
                'data-id' => $model['COURSE_WORK_ID'],
            ]);
        }
    ],
    'width' => '10%',
    'hAlign' => 'center',
];

$gridColumns = [
    ['class'=>'kartik\grid\SerialColumn'],
    $registrationNoColumn,
    $surnameColumn,
    $otherNamesColumn,
    $marksColumn,
    $userColumn,
    $dateColumn,
    $remarksColumn
];

// Allow actions on marks while not yet submitted
$marksPending = SmisHelper::marksPending($assessmentId, 'LECTURER_APPROVAL_STATUS');
if($marksPending){
    $gridColumns[] = $actionColumn;
}

$title = $courseName .' ('.$courseCode.') | '.$assessmentName;
?>

<div class="row">
    <div class="col-md-12 col-lg-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <i class="fa fa-exclamation" aria-hidden="true"></i> <b>Please Note the following:</b>
            </div>
            <div class="panel-body">
                <p>1. Marks submitted as final can only be deleted at the next level.</p>
                <p>2. Generate an updated consolidated marks report after deleting any marks.</p>
            </div>
        </div>
    </div>
</div>

<?php
$gridId = 'delete-marks-grid';

try {
    echo GridView::widget([
        'id' => $gridId,
        'dataProvider' => $cwProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumns,
        'headerRowOptions' => [
            'class' => 'kartik-sheet-style'
        ],
        'filterRowOptions' => [
            'class' => 'kartik-sheet-style'
        ],
        'pjax' => true,
        'toolbar' => [
            '{toggleData}'
        ],
        'toggleDataContainer' => [
            'class' => 'btn-group mr-2'
        ],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<h3 class="panel-title">' . $title . '</h3>',
        ],
        'persistResize' => false,
        'toggleDataOptions' => ['minCount' => 50],
        'itemLabelSingle' => 'student',
        'itemLabelPlural' => 'students',
    ]);
} catch (Exception $ex) {
    $message = 'An error occurred while attempting to create the table grid.';
    if (YII_ENV_DEV) {
        $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}

echo $this->render('_appIsLoading');
?>

<?php
$deleteMarksScript = <<< JS
// Delete marks
$('#delete-marks-grid-pjax').on('click', '.delete-marks', function(e){
deleteMarks.call(this, e);
});
JS;
$this->registerJs($deleteMarksScript, yii\web\View::POS_READY);


