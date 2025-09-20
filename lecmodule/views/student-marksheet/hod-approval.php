<?php

/**
 * @author Jack
 * @email jackmutiso37@gmail.com
 */

use yii\helpers\Html;
use yii\widgets\Pjax;
use app\models\Marksheet;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\web\ServerErrorHttpException;

$columns = [
    ['class' => 'yii\grid\SerialColumn'],
    [
        'class' => 'yii\grid\CheckboxColumn',
        'name' => 'selection',
        'checkboxOptions' => function ($model, $key, $index, $column) {
            return ['value' => $model['LEC_LATE_MARKS_ID']];
        }
    ],
    [
        'attribute' => 'COURSE_NAME',
        'header' => 'Course Name',
        'value' => function ($model) {
            return $model['COURSE_NAME'] . " - " . $model['COURSE_CODE'];
        },
        'contentOptions' => ['style' => 'width:30%;'],
    ],
    [
        'attribute' => 'REGISTRATION_NUMBER',
        'header' => 'Reg. No',
        'value' => function ($model) {
            return $model['REGISTRATION_NUMBER'];
        },
        'contentOptions' => ['style' => 'width:9%;'],
    ],
    [
        'attribute' => 'OTHER_NAMES',
        'header' => 'Student Name',
        'value' => function ($model) {
            return $model['STUDENT_SURNAME'] . " " . $model['STUDENT_OTHER_NAMES'];
        },
        'contentOptions' => ['style' => 'width:15%;'],
    ],
    [
        'attribute' => 'COURSE_MARKS',
        'header' => 'Cat',
        'format' => 'html',
        'value' => function ($model) {
            $condition = [
                'MRKSHEET_ID' => $model['MRKSHEET_ID'],
                'REGISTRATION_NUMBER' => $model['REGISTRATION_NUMBER'],
            ];
            $mrksheetModel = Marksheet::findOne($condition);

            $courseMarks = $model['COURSE_MARKS'] ?? $mrksheetModel['COURSE_MARKS'];
            if ($model['COURSE_MARKS']) {
                return "<span style='color: #007bff; font-weight: bold;'>$courseMarks</span>";
            }

            return $courseMarks;
        },
        'contentOptions' => ['style' => 'width:5%;'],
    ],
    [
        'attribute' => 'EXAM_MARKS',
        'header' => 'Exam',
        'format' => 'html',
        'value' => function ($model) {
            $condition = [
                'MRKSHEET_ID' => $model['MRKSHEET_ID'],
                'REGISTRATION_NUMBER' => $model['REGISTRATION_NUMBER'],
            ];
            $mrksheetModel = Marksheet::findOne($condition);

            $examMarks = $model['EXAM_MARKS'] ?? $mrksheetModel['EXAM_MARKS'];
            if ($model['EXAM_MARKS']) {
                return "<span style='color: #007bff; font-weight: bold;'>$examMarks</span>";
            }

            return $examMarks;
        },
        'contentOptions' => ['style' => 'width:5%;'],
    ],
    [
        'attribute' => 'FINAL_MARKS',
        'header' => 'Final Marks',
        'format' => 'decimal',
        'value' => function ($model) {
            $condition = [
                'MRKSHEET_ID' => $model['MRKSHEET_ID'],
                'REGISTRATION_NUMBER' => $model['REGISTRATION_NUMBER'],
            ];
            $mrksheetModel = Marksheet::findOne($condition);

            $finalMarks = $model['FINAL_MARKS'] ?? $mrksheetModel['FINAL_MARKS'];
            if ($model['FINAL_MARKS']) {
                return $finalMarks;
            }

            return $finalMarks;
        },
        'contentOptions' => ['style' => 'width:8%;'],
    ],
    [
        'attribute' => 'ENTRY_DATE',
        'header'    => 'Entry',
        'format'    => ['date', 'php:Y-m-d'],
        'value'     => function ($model) {
            return date('Y-m-d', strtotime($model['ENTRY_DATE']));
        },
        'contentOptions' => ['style' => 'width:8%;'],
    ],
    // [
    //     'attribute' => 'HOD_APPROVAL',
    //     'header' => 'Hod Approval',
    //     'format' => 'html',
    //     'value' => function ($model) {
    //         if ($model['HOD_APPROVAL'] == 0) {
    //             return '<span class="text-warning"><i class="fa fa-clock"></i> Pending</span>';
    //         } elseif ($model['HOD_APPROVAL'] == 1) {
    //             return '<span class="text-success"><i class="fa fa-check-circle"></i> Approved</span>';
    //         } else {
    //             return '<span class="text-danger"><i class="fa fa-times-circle"></i> Disapproved</span>';
    //         }
    //     },
    //     'contentOptions' => ['class' => 'hod-approval', 'style' => 'text-align: center;  white-space: nowrap;'],

    // ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'header' => 'Actions',
        'template' => '{history}',
        'buttons' => [
            'history' => function ($url, $model, $key) {

                return Html::a(
                    '<i class="fas fa-history"></i> View History',
                    '#',
                    [
                        'class' => 'btn btn-info btn-sm view-history-btn',
                        'title' => 'View History',
                        'data-bs-toggle' => 'modal',
                        'data-bs-target' => '#history-modal',
                        'data-mrksheet' => $model['MRKSHEET_ID'],
                        'data-regnumber' => $model['REGISTRATION_NUMBER'],
                    ]
                );
            },
        ],
        'headerOptions' => ['style' => 'text-align: center;'],
        'contentOptions' => ['style' => 'text-align: center;  white-space: nowrap;'],
    ],


];


?>

<?php
Pjax::begin(['id' => 'crud-datatable-pjax']);
try {

    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'id' => 'crud-datatable',
        'filterModel' => $searchModel,
        'export' => false,
        'pjax' => true,
        'toolbar' => [
            [
                'content' =>
                Html::button(
                    '<i class="fas fa-check"></i> Approve',
                    ['class' => 'btn btn-success', 'id' => 'approve-btn']
                ) . ' ' .
                    Html::button(
                        '<i class="fas fa-times"></i> Disapprove',
                        ['class' => 'btn btn-danger', 'id' => 'disapprove-btn']
                    ) .
                    '{toggleData}' .
                    '{export}',
            ],
        ],
        'tableOptions' => ['class' => 'table table-bordered table-striped small-font'],
        'summary' => false,
        'columns' => $columns,

        'panel' => [
            'heading' => '
                <div style="font-size:14px; font-weight:bold;">
                    STUDENT MARKSHEET - HOD SECTION
                    <div class="text-white mt-1" style="font-size:12px; font-weight:normal;">
                        <i class="bi bi-info-circle"></i> 
                        Select records to approve/disapprove, then click the appropriate button
                    </div>
                </div>',
            'headingOptions' => [
                'class' => 'text-white p-2',
                'style' => 'border:solid; background-color:#304186!important;'
            ],
            'before' => '
                <div class="mb-3">
                    ' . Html::a(
                '<i class="fas fa-eye"></i> View Disapproved',
                ['disapproved-marks', 'level' => 'hod'],
                ['class' => 'btn btn-outline-danger', 'id' => 'approved-btn']
            ) . '
                    ' . Html::a(
                '<i class="fas fa-eye"></i> View Approved',
                ['approved-marks', 'level' => 'hod'],
                ['class' => 'btn btn-outline-dark', 'id' => 'approved-btn']
            ) . '
                </div>
                <div class="card border-info mb-3">
                    <div class="card-header text-white" style="background-color:#304186!important;">
                        <i class="bi bi-info-circle-fill"></i>
                        Approval Guidelines
                    </div>
                    <div class="card-body text-dark">
                        <p>
                            <strong>HODs</strong> can approve or disapprove student marksheets based on the accuracy and completeness of submitted grades.
                        </p>
                        <p>
                            Click <b>"Approve"</b> if the marks are correct and complete, or
                            <b class="text-danger">"Disapprove"</b> if corrections are needed.
                        </p>
                    </div>
                </div>'
        ],
    ]);
} catch (Throwable $ex) {
    $message = $ex->getMessage();
    if (YII_ENV_DEV) {
        $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}
Pjax::end();
?>

<?php
Modal::begin([
    'id' => 'disapprove-modal',
    'options' => [
        'data-backdrop' => 'static',    
        'data-keyboard' => 'false',    
    ],
    'header' => '<span class="text-dark">Enter Disapproval Comment</span>',
    'footer' => Html::button('Submit', ['class' => 'btn btn-danger', 'id' => 'submit-disapprove'])
]);

echo '<textarea id="disapprove-comment" class="form-control" rows="4" placeholder="Enter comment..."></textarea>';

Modal::end();
?>

<?php

Modal::begin([
    'id' => 'history-modal',
    'size' => Modal::SIZE_LARGE,
    'options' => [
        'data-backdrop' => 'static',   
        'data-keyboard' => 'false',    
        'class' => 'modal-wide',
    ],
    'header' => '<div class="d-flex justify-content-between align-items-center">'
        . '<div class="text-dark"><i class="fas fa-history mx-4"></i>Previous Updates</div>'
        . '</div>',
]);

echo "<div id='history-content' class='p-3'>Loading...</div>";

Modal::end();

$this->registerCss('
        .modal-wide .modal-dialog {
            max-width: 70%;
            width: 70%;
        }
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1); 
            border-top: 4px solid #3498db; 
            border-radius: 50%;
            width: 40px; 
            height: 40px; 
            animation: spin 1s linear infinite; 
            margin: 0 auto; 
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            text-align: center;
            color: #6c757d; 
            margin-top: 10px; 
        }
');
?>

<?php
$approveUrl = \yii\helpers\Url::to(['approve']);
$disapproveUrl = \yii\helpers\Url::to(['disapprove']);

$js = <<<JS
$(document).on('pjax:complete', function() {
    attachEventHandlers();
});

function attachEventHandlers() {
    console.log("Attaching event handlers after Pjax reload...");

    $(document).off('click', '#crud-datatable tbody tr').on('click', '#crud-datatable tbody tr', function (e) {
        if (!$(e.target).is('input[type=checkbox]')) {
            let checkbox = $(this).find('input[type=checkbox]');
            checkbox.prop('checked', !checkbox.prop('checked'));
        }
    });

    function getSelectedRecords() {
        let selected = [];
        $('input[name="selection[]"]:checked').each(function() {
            selected.push($(this).val());
        });
        return selected;
    }

    $(document).off('click', '#approve-btn').on('click', '#approve-btn', function() {
        let selected = getSelectedRecords();
        if (selected.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Selection',
                text: 'Please select at least one record.',
            });
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to approve the selected records.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, submit it!'
        }).then((result) => {
            if(result.isConfirmed){
                Swal.fire({
                    title: 'Submitting...',
                    text: 'Please wait while we submit the records.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '$approveUrl',
                    type: 'POST',
                    data: {selected: selected},
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                        }).then(() => {
                            $.pjax.reload({container: '#crud-datatable-pjax', async: false});
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred: ' + xhr.responseText,
                        });
                    }
                });
            }
        });
    });

    $(document).off('click', '#disapprove-btn').on('click', '#disapprove-btn', function() {
        let selected = getSelectedRecords();
        if (selected.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Selection',
                text: 'Please select at least one record.',
            });
            return;
        }
        $('#disapprove-modal').modal('show'); 
    });

    $(document).off('click', '#submit-disapprove').on('click', '#submit-disapprove', function() {
        let selected = getSelectedRecords();
        let comment = $('#disapprove-comment').val().trim();

        if (comment === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Comment',
                text: 'Please enter a comment for disapproval.',
            });
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to disapprove the selected records.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, submit it!'
        }).then((result) => {
            if(result.isConfirmed){
                Swal.fire({
                    title: 'Submitting...',
                    text: 'Please wait while we submit the records.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: '$disapproveUrl',
                    type: 'POST',
                    data: {selected: selected, comment: comment},
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Disapproved!',
                            text: response.message,
                        }).then(() => {
                            $('#disapprove-modal').modal('hide'); 
                            $('#disapprove-comment').val('');
                            $.pjax.reload({container: '#crud-datatable-pjax', async: false});
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Request Failed: ' + xhr.responseText,
                        });
                    }
                });
            }
        });
    });

    $(document).off('click', '.view-history-btn').on('click', '.view-history-btn', function (e) {
        e.preventDefault();

        var mrksheetId = $(this).data("mrksheet");
        var regNumber = $(this).data("regnumber");

        $("#history-modal").modal('show');

        $("#history-content").html(`
            <div class="spinner"></div>
            <p class="loading-text">Loading history...</p>
        `);
        
        $.ajax({
            url: "/student-marksheet/view-history",
            type: "GET",
            data: { mrksheet_id: mrksheetId, reg_number: regNumber },
            success: function (response) {
                $("#history-content").html(response);
            },
            error: function () {
                $("#history-content").html("<p class='text-danger'>Failed to load history.</p>");
            }
        });
    });
}

attachEventHandlers();
JS;

$this->registerJs($js);
?>