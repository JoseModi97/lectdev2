<?php

/**
 * @author Jack jmm <jackmutiso37@gmail.com>
 * @desc UI for editing marks of an assessment for a marksheet.
 */

/* @var $this yii\web\View */
/* @var $model app\models\StudentCoursework */
/* @var $searchModel app\models\search\StudentCourseworkSearch */
/* @var $cwProvider yii\data\ActiveDataProvider */
/* @var $title string */
/* @var $marksheetId string */
/* @var $courseCode string */
/* @var $courseName string */
/* @var $assessmentId string */
/* @var $assessmentName string */
/* @var $assessmentWeight string */
/* @var string $maximumMarks */
/* @var string $type  */

use app\components\SmisHelper;
use app\components\BreadcrumbHelper;
use app\models\CourseAssignment;
use app\models\EmpVerifyView;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\ServerErrorHttpException;

// $assessmentId = Yii::$app->request->get('assessmentId') ?? '';
// $type = Yii::$app->request->get('type') ?? '';
echo BreadcrumbHelper::generate([
    ['label' => 'My Course Allocations', 'url' => ['/allocated-courses']],
    ['label' => 'Coursework Marks', 'url' => ['/assessments', 'marksheetId' => $marksheetId, 'type' => $type, 'section' => 'manage-marks']],
    ['label' => 'Edit Marks']
]);

$assignmentModel = CourseAssignment::find()
    ->where(['MRKSHEET_ID' => $marksheetId, 'PAYROLL_NO' => Yii::$app->user->identity->PAYROLL_NO])
    ->one();

if ($section == 'submit-as-final') {
    $cwProvider->query->andWhere(['LECTURER_APPROVAL_STATUS' => 'PENDING']);
}
if ($section == 'fully-submited') {
    $cwProvider->query->andWhere(['LECTURER_APPROVAL_STATUS' => 'APPROVED']);
}

$models = $cwProvider->getModels();

$surnames = [];
$other_names = [];
foreach ($models as $model) {
    if (isset($model['student']['SURNAME'])) {
        $surname = trim($model['student']['SURNAME']); 
        $other_name = trim($model['student']['OTHER_NAMES']);
        $surnames[$surname] = $surname; 
        $other_names[$other_name] = $other_name;
    }
}

$surnames = array_unique(array_column(array_column($models, 'student'), 'SURNAME'));
$surnames = array_combine($surnames, $surnames); 

$this->title = $title;

$this->params['breadcrumbs'][] = [
    'label' => 'MY COURSE ALLOCATIONS',
    'url' => [
        '/allocated-courses'
    ]
];

$labelName = ($type === 'component') ? 'EXAM COMPONENTS' : 'COURSEWORK';
$this->params['breadcrumbs'][] = [
    'label' => $labelName,
    'url' => [
        '/assessments',
        'marksheetId' => $marksheetId,
        'type' => $type
    ]
];


$this->params['breadcrumbs'][] = $this->title;
?>

<!-- Build grid columns -->
<?php

$registrationNoColumn = [
    'attribute' => 'REGISTRATION_NUMBER',
    'label' => 'REGISTRATION NUMBER',
    'filterType' => GridView::FILTER_SELECT2,
    'filter' => ArrayHelper::map($models, 'REGISTRATION_NUMBER', 'REGISTRATION_NUMBER'),
    'filterWidgetOptions' => [
        'pluginOptions' => ['allowClear' => true],
    ],
    'filterInputOptions' => ['placeholder' => 'Search'],
    'contentOptions' => ['style' => 'white-space: nowrap; width: 150px;'],
    'hAlign' => 'left',
];
$surnameColumn = [
    'attribute' => 'SURNAME',
    'label' => 'SURNAME',
    'hAlign' => 'left',
    'value' => function ($model) {
        return $model['student']['SURNAME'];
    },
    'filterType' => GridView::FILTER_SELECT2,
    'filter' =>$surnames,
    'filterWidgetOptions' => [
        'pluginOptions' => ['allowClear' => true],
    ],
    'filterInputOptions' => ['placeholder' => 'Search'],
    'contentOptions' => ['style' => 'white-space: nowrap;'],
];
$otherNamesColumn = [
    'attribute' => 'OTHER_NAMES',
    'label' => 'OTHER NAMES',
    'hAlign' => 'left',
    'value' => function ($model) {
        return $model['student']['OTHER_NAMES'];
    },
     'filterType' => GridView::FILTER_SELECT2,
    'filter' =>$other_names,
    'filterWidgetOptions' => [
        'pluginOptions' => ['allowClear' => true],
    ],
    'filterInputOptions' => ['placeholder' => 'Search'],
    'contentOptions' => ['style' => 'white-space: nowrap;'],
];
$marksColumn = [
    'attribute' => 'MARK',
    'label' => 'WEIGHTED MARKS',
    'hAlign' => 'left'
];
$rawmarksColumn = [
    'attribute' => 'RAW_MARK',
    'label' => 'RAW MARKS',
];
$userColumn = [
    'attribute' => 'USER_ID',
    'label' => 'ENTERED BY',
    'hAlign' => 'left',
    'width' => '20%',
    'value' => function ($model) {
        $lecturer = EmpVerifyView::find()
            ->select(['PAYROLL_NO', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE'])
            ->where(['PAYROLL_NO' => $model['USER_ID']])
            ->one();

        if (is_null($lecturer)) {
            return '';
        }

        $date = Yii::$app->formatter->asDate($model['DATE_ENTERED'], 'full');

        return 'ENTERED BY - ' . $lecturer->EMP_TITLE . ' ' . $lecturer->SURNAME . ' ' . $lecturer->OTHER_NAMES." - ";
    },
    'group' => true,
    'groupedRow' => true,
    'contentOptions' => ['style' => 'font-size: 16px; font-weight: bold;'],
];
$dateColumn = [
    'attribute' => 'DATE_ENTERED',
    'label' => 'DATE ENTERED',
    'hAlign' => 'left',
    'width' => '20%',
    'format' => 'raw',
    'contentOptions' => ['class' => 'kartik-sheet-style kv-align-middle'],
    'filterType' => GridView::FILTER_DATE,
    'filterWidgetOptions' => [
        'options' => ['id' => 'assessment-marks-date-entered'],
        'pluginOptions' => ['autoclose' => true, 'allowClear' => true, 'format' => 'dd-M-yyyy',],
    ],
    'filterInputOptions' => ['placeholder' => 'Date Entered'],
    'value' => function ($model) {
        return "DATE ENTERED - ". Yii::$app->formatter->asDate($model['DATE_ENTERED'], 'full');
    },
    'contentOptions' => ['style' => 'font-size: 16px; font-weight: bold;'],
    'group' => true,
    'groupedRow' => true,
];
$remarksColumn = [
    'label' => 'REMARKS',
    'value' => function ($model) use ($section) {
        $status = $model['LECTURER_APPROVAL_STATUS'];
        $remarks = '<i class="fas fa-check text-success"></i> Fully Submitted';
        if($section == 'submit-as-final')
        {
            return "Not Submitted";
        }
        if($status == 'PENDING')
        {
            $remarks = "Not Submitted";
        }
        
        return is_null($model['REMARKS']) ? $remarks : $model['REMARKS'];
    },
    'format' => 'raw',
    'contentOptions' => ['style' => 'white-space:nowrap; text-align: center;'],
    'width' => '20%',
    'hAlign' => 'left'
];
$actionColumn = [
    'class' => 'kartik\grid\ActionColumn',
    'template' => '{update-assessment-marks}',
    'contentOptions' => ['style' => 'white-space:nowrap;', 'class' => 'kartik-sheet-style kv-align-middle'],
    'buttons' => [
        'update-assessment-marks' => function ($url, $model) {
            $status = $model['LECTURER_APPROVAL_STATUS'];
            if ($status === 'PENDING') {
                return Html::button('<i class="fas fa-edit"></i> marks', [
                    'title' => 'Edit Marks',
                    'href' => Url::to(['/marks/edit', 'studentCourseworkId' => $model['COURSE_WORK_ID']]),
                    'class' => 'btn btn-xs edit-marks'
                ]);
            } else {
                return Html::button('<i class="fas fa-edit"></i> Marks', [
                    'title' => 'Marks already submitted can not be edited',
                    'class' => 'text-danger btn',
                    'style'=>"border:none !important",
                    'disabled' => 'disabled'
                ]);
            }
        }
    ],
    'hAlign' => 'center',
];

$gridColumns = [
    ['class' => 'kartik\grid\SerialColumn'],
    $registrationNoColumn,
    $surnameColumn,
    $otherNamesColumn,
    $marksColumn,
    $rawmarksColumn,
    $userColumn,
    $dateColumn,
    $remarksColumn
];

// Allow actions on marks while not yet submitted
$marksPending = SmisHelper::marksPending($assessmentId, 'LECTURER_APPROVAL_STATUS');
if ($marksPending) {
    $gridColumns[] = $actionColumn;
}
?>
<!-- End build grid columns -->

<!-- Display grid and columns -->
<div class="row">
    <div class="col-md-12 col-lg-12">
        <div class="panel panel-primary">
            <div class="panel-heading"><b>Please Note the following:</b></div>
            <div class="panel-body">
                <p>1. Click on the View consolidated marks button to see the class performance before submitting marks as final.</p>
            </div>
        </div>
    </div>
</div>

<div class="edit-assessment-marks-index">
    <?php
    $title = $courseName . ' (' . $courseCode . ') | ' . strtoupper($assessmentName) . ' | ASSESSMENT WEIGHT: '
        . $assessmentWeight . ' | MARKS OUT OF: ' . $maximumMarks;

    // Allow actions on marks while not yet submitted
    if ($marksPending) {
        $toolbar = [
            [
                'content' =>
                Html::button('Submit Marks As Final', [
                    'title' => 'Submit Marks As Final',
                    'id' => 'submit-final-cw-marks-btn',
                    'class' => 'btn btn-spacer',
                ]) .
                    Html::a(
                        'View consolidated marks',
                        Url::to(['/marks/consolidate', 'marksheetId' => $marksheetId]),
                        ['title' => 'View consolidated marks', 'class' => 'btn btn-spacer']
                    ),
                'options' => ['class' => 'btn-group mr-2']
            ],
            '{toggleData}',
        ];
    } else {
        $toolbar = [
            [
                'content' =>
                Html::a(
                    'View consolidated marks',
                    Url::to(['/marks/consolidate', 'marksheetId' => $marksheetId]),
                    ['title' => 'View consolidated marks', 'class' => 'btn btn-spacer']
                ),
                'options' => ['class' => 'btn-group mr-2']
            ],
            '{toggleData}',
        ];
    }

    try {
        echo GridView::widget([
            'id' => 'edit-cw-marks-gridview',
            'dataProvider' => $cwProvider,
            'filterModel' => $searchModel,
            'columns' => $gridColumns,
            'headerRowOptions' => ['class' => 'kartik-sheet-style'],
            'filterRowOptions' => ['class' => 'kartik-sheet-style'],
            'pjax' => true,
            'toolbar' => $toolbar,
            'toggleDataContainer' => ['class' => 'btn-group mr-2'],
            'panel' => [
                'type' => GridView::TYPE_PRIMARY,
                'heading' => '<h5 class="panel-title text-dark">' . $title . '</h5>',
            ],
            'persistResize' => false,
            'toggleDataOptions' => ['minCount' => 50],
            'itemLabelSingle' => 'record',
            'itemLabelPlural' => 'records',
        ]);
    } catch (Exception $ex) {
        $message = $ex->getMessage();
        if (YII_ENV_DEV) {
            $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
        }
        throw new ServerErrorHttpException($message, 500);
    }
    ?>
</div>

<!-- Start include modals -->
<?php
echo $this->render('_editMarksModal');
echo $this->render('_appIsLoading');
?>
<!-- End include modals -->

<?php
$urlSubmitMarks = Url::to(['/marks/submit']);
$loader = '<h1 class="text-center text-primary" style="font-size: 100px;"><i class="fas fa-spinner fa-pulse"></i></h1>';

$editAssessmentMarksScript = <<< JS
$('#app-is-loading-modal-title').html('');
$('#pp-is-loading-modal').modal('hide');

const loader = '$loader';
const urlSubmitMarks = '$urlSubmitMarks';
const assessmentId = '$assessmentId';

/** Edit individual assessment marks modal */
$('#edit-cw-marks-gridview-pjax').on('click', '.edit-marks', function(e){
    e.preventDefault();
    $('#edit-marks-modal-content').html(loader);
    $('#edit-marks-modal').modal('show').find('#edit-marks-modal-content')
        .load($(this).attr('href'), function(e){});
});

/** Submit assessment marks as final */
$('#edit-cw-marks-gridview-pjax').on('click', '#submit-final-cw-marks-btn', function(e){ 
    e.preventDefault();
    let confirmMsg = "If submitted as final, you will be unable to edit these marks at this level. Are you sure you want to proceed?";
    krajeeDialog.confirm(confirmMsg, function (result) {
        if (result) {
            let postData = {
                'assessmentId': assessmentId
            };

            $('#app-is-loading-modal-title').html('<b class="text-center">Submitting Marks As Final</b>');
            $('#app-is-loading-modal').modal('show');

            $.ajax({
                type        :   'POST',
                url         :   urlSubmitMarks,
                data        :   postData,
                dataType    :   'json',
                encode      :   true             
            })
            .done(function(data){
                if(!data.success){
                    $('#app-is-loading-message').html('<p>Marks not submitted. </p><br/><p class="text-danger">' + data.message + '</p>');
                }
            })
            .fail(function(data){});
        } else {
            krajeeDialog.alert('Marks submission as final cancelled.');
        }
    });
});
JS;
$this->registerJs($editAssessmentMarksScript, yii\web\View::POS_READY);
