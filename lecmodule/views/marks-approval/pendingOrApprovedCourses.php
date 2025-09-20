<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc Show pending/approved courses
 */

/**
 * @var yii\web\View $this
 * @var app\models\MarksheetDef $model
 * @var yii\data\ActiveDataProvider $coursesDataProvider
 * @var app\models\search\CoursesToApproveSearch $coursesSearchModel
 * @var app\models\MarksApprovalFilter $filter
 * @var string $panelHeading
 * @var string $title
 * @var string $filtersInterface
 * @var string $resultsType
 * @var string $levelColumn
 * @var string $groupColumn
 * @var string $semesterColumn
 * @var string $courseCodeColumn
 * @var string $gradeAColumn
 * @var string $gradeBColumn
 * @var string $gradeCColumn
 * @var string $gradeCStarColumn
 * @var string $gradeDColumn
 * @var string $gradeEColumn
 * @var string $gradeEStarColumn
 * @var string $gradeFColumn
 * @var string $gradeNullColumn
 * @var string $averageScoreColumn
 * @var string $averageGradeColumn
 * @var string $totalCountColumn
 * @var string $actionColumn
 * @var string $checkboxColumn
 * @var string $actionColumn
 * @var string $columns
 */

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

$this->title = $title;
$this->params['breadcrumbs'][] = [
    'label' => 'Course filters',
    'url' => ['/marks-approval/new-filters', 'level' => $filter->approvalLevel, 'filtersInterface' => $filtersInterface]
];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading"><b>Please note the following</b></div>
            <div class="panel-body">
                <p>
                    1. If these reports are not current, update them using the
                    <?= Html::a('View consolidated marks', '#', ['class' => 'btn btn-xs'])?>
                    link for the course's assessments/exam. It is found on the pages used to enter and edit or approve marks.
                </p>
            </div>
        </div>
    </div>
</div>

<?php
if($filtersInterface === '1'){
    echo $this->render('courseToApproveUi1ActiveFilters', ['filter' => $filter]);
    echo $this->render('coursesToApproveUi1MoreFilters', ['filter' => $filter, 'filtersInterface' => $filtersInterface]);
}else{
    echo $this->render('courseToApproveUi2ActiveFilters', ['filter' => $filter]);
    echo $this->render('coursesToApproveUi2MoreFilters', ['filter' => $filter, 'filtersInterface' => $filtersInterface]);
}

include(Yii::getAlias('@views') . '/marks-approval/coursesToApproveColumns.php');

try {
    $gridId = 'pending-approved-courses-grid';
    if($resultsType === 'approved'){
        $toolbar = [
            '{toggleData}'
        ];
    }else{
        $toolbar = [
            [
                'content' =>
                    Html::Button('<i class="fa fa-check"></i> Approve', [
                        'title' => 'Approve Marks in bulk',
                        'id' => 'bulk-marks-approve-btn',
                        'class' => 'btn btn-spacer btn-create',
                        'data-level' => $filter->approvalLevel
                    ]),
                'options' => ['class' => 'btn-group mr-2']
            ],
            '{toggleData}'
        ];
    }
    echo GridView::widget([
        'id' => $gridId,
        'dataProvider' => $coursesDataProvider,
        'filterModel' => $coursesSearchModel,
        'headerRowOptions' => ['class' => 'kartik-sheet-style'],
        'filterRowOptions' => ['class' => 'kartik-sheet-style'],
        'pjax' => true,
        'pjaxSettings' => [
            'options' => [
                'id' => $gridId . '-pjax'
            ]
        ],
        'toolbar' => $toolbar,
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => $panelHeading
        ],
        'persistResize' => false,
        'toggleDataContainer' => ['class' => 'btn-group mr-2'],
        'toggleDataOptions' => ['minCount' => 20],
        'itemLabelSingle' => 'course',
        'itemLabelPlural' => 'courses',
        'columns' => $columns
    ]);
} catch (Exception $ex) {
    $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
    throw new ServerErrorHttpException($message, 500);
}

// Loader to show user
echo $this->render('../marks/_appIsLoading');

// JS
$urlApproveCourseMarks = Url::to(['/marks-approval/approve-course-marks']);
$urlMarkBackMarks = Url::to(['/marks-approval/mark-back-marks']);
$csrf = Yii::$app->request->csrfToken;
$marksApprovalsJs = <<< JS
    // Approve marks for an exam or exam components in a course
    $('#pending-approved-courses-grid-pjax').on('click', '#marks-approve-btn', function(e){
        e.preventDefault();
        const marksheetId = $(this).attr('data-marksheetId');
        const level = $(this).attr('data-level');
        approveMarks([marksheetId], level);
    });

    // Approve marks in bulk
    $('#pending-approved-courses-grid-pjax').on('click', '#bulk-marks-approve-btn', function(e){
        e.preventDefault();
        let marksheetIds = $('#pending-approved-courses-grid').yiiGridView('getSelectedRows');
        const level = $(this).attr('data-level');
        approveMarks(marksheetIds, level);
    });
    
    // Submit courses for which to approve their marks
    function approveMarks(marksheetIds, level){
        const confirmMsg = "The marks of this course(s) will be submitted. Are you sure you want to proceed?";
        const approveMarksurl = '$urlApproveCourseMarks';
        
        krajeeDialog.confirm(confirmMsg, function (result) {
            if(result){
                $('#app-is-loading-modal-title').html('<b class="text-center">Approving marks...</b>');
                $('#app-is-loading-modal').modal('show');
                
                let postData = {
                    'marksheetIds': marksheetIds,
                    'level': level
                };
             
                $.ajax({
                        type        :   'POST',
                        url         :   approveMarksurl,
                        data        :   postData,
                        dataType    :   'json',
                        encode      :   true             
                })
                .done(function(data){
                    if(!data.success){
                        $('#app-is-loading-message').html('<p class="text-danger">' + data.message + '</p>');
                    }
                })
                .fail(function(data){});
            }
        });
    }
    
    // Send marks back to the lecturer for editing
    $('#pending-approved-courses-grid-pjax').on('click', '#mark-back-btn', function(e){
        e.preventDefault();
        const marksheetId = $(this).attr('data-marksheetId');
        const level = $(this).attr('data-level');
        const confirmMsg = "The marks of this course will be sent back to the lecturer for editing. Are you sure you want to proceed?";
        const sendMarksBacksurl = '$urlMarkBackMarks';
        
        krajeeDialog.confirm(confirmMsg, function (result) {
            if(result){
                $('#app-is-loading-modal-title').html('<b class="text-center">Sending marks back to lecturer...</b>');
                $('#app-is-loading-modal').modal('show');
                
                let postData = {
                    'marksheetId': marksheetId,
                    'level': level
                };
             
                $.ajax({
                        type        :   'POST',
                        url         :   sendMarksBacksurl,
                        data        :   postData,
                        dataType    :   'json',
                        encode      :   true             
                })
                .done(function(data){
                    if(!data.success){
                        $('#app-is-loading-message').html('<p>Marks not sent back. </p><br/><p class="text-danger">' + data.message + '</p>');
                    }
                })
                .fail(function(data){});
            }else{
                krajeeDialog.alert('Sending marks back to lecturer was cancelled');
            }
        });
    });
JS;
$this->registerJs($marksApprovalsJs, yii\web\View::POS_READY);