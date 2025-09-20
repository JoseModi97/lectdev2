<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc Show pending courses awaiting hod/dean approval. These courses can be marked to the lecturer for amendments.
 */

/**
 * @var yii\web\View $this
 * @var app\models\MarksheetDef $model
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\CoursesToApproveSearch $searchModel
 * @var app\models\MarksApprovalFilter $filter
 * @var string $panelHeading
 * @var string $gridId
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
 * @var string $filtersInterface
 * @var string $checkboxColumn
 * @var string $resultsType
 */

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

include(Yii::getAlias('@views') . '/marks-approval/coursesToApproveColumns.php');

$actionColumn = [
    'class' => 'kartik\grid\ActionColumn',
    'header' => 'ACTIONS | REPORTS',
    'template' => '{approve} {mark-back} {consolidated-marks} {class-performance-graphical} {missing-marks}',
    'contentOptions' => ['style'=>'white-space:nowrap;','class'=>'kartik-sheet-style kv-align-middle'],
    'buttons' => [
        'approve' => function($url, $model) use ($filter){
            return Html::button('<i class="fa fa-check"></i> Approve', [
                'title' => 'Approve Marks',
                'id' => 'marks-approve-btn',
                'class' => 'btn btn-spacer btn-create',
                'data-marksheetId' => $model['MRKSHEET_ID'],
                'data-level' => $filter->approvalLevel
            ]);
        },
        'mark-back' => function($url, $model) use ($filter){
            return Html::button('<i class="fa fa-undo"></i> Mark back', [
                'title' => 'Approve Marks',
                'id' => 'mark-back-btn',
                'class' => 'btn btn-spacer btn-delete',
                'data-marksheetId' => $model['MRKSHEET_ID'],
                'data-level' => $filter->approvalLevel
            ]);
        },
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

try {
    if($filtersInterface === '1'){
        $columns = [
            ['class' => 'yii\grid\SerialColumn'],
            $checkboxColumn,
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
    }else{
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
    }

    echo GridView::widget([
        'id' => $gridId,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'headerRowOptions' => ['class' => 'kartik-sheet-style'],
        'filterRowOptions' => ['class' => 'kartik-sheet-style'],
        'pjax' => true,
        'pjaxSettings' => [
            'options' => [
                'id' => $gridId . '-pjax'
            ]
        ],
        'toolbar' => [
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
        ],
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
}catch (Exception $ex) {
    $message = $ex->getMessage();
    if(YII_ENV_DEV){
        $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}

// JS
$urlApproveCourseMarks = Url::to(['/marks-approval/approve-course-marks']);
$urlMarkBackMarks = Url::to(['/marks-approval/mark-back-marks']);
$csrf = Yii::$app->request->csrfToken;
$pendingCoursesJs = <<< JS
    // Approve marks for an exam or exam components in a course
    $('#pending-courses-grid-pjax').on('click', '#marks-approve-btn', function(e){
        e.preventDefault();
        const marksheetId = $(this).attr('data-marksheetId');
        const level = $(this).attr('data-level');
        approveMarks([marksheetId], level);
    });

    // Send marks back to the lecturer for editing
    $('#pending-courses-grid-pjax').on('click', '#mark-back-btn', function(e){
        e.preventDefault();
        const marksheetId = $(this).attr('data-marksheetId');
        const level = $(this).attr('data-level');
        const confirmMsg = "The marks of this course will be sent back to the lecturer for editing. Are you sure you want to proceed?";
        const sendMarksBacksurl = '$urlMarkBackMarks';
        const csrf = '$csrf';
        
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
    
    // Approve marks in bulk
    $('#pending-courses-grid-pjax').on('click', '#bulk-marks-approve-btn', function(e){
        e.preventDefault();
        let marksheetIds = $('#pending-courses-grid').yiiGridView('getSelectedRows');
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
JS;
$this->registerJs($pendingCoursesJs, yii\web\View::POS_READY);

