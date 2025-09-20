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
    [
        'attribute' => 'PUBLISH_STATUS',
        'header' => 'Publish Status',
        'format' => 'html',
        'value' => function ($model) {
            if ($model['PUBLISH_STATUS'] == 1) {
                return '<i class="bi bi-check-circle-fill text-success" title="Published"></i> Published';
            } else {
                return '<i class="bi bi-clock text-muted" title="Not Published"></i> Not Published';
            }
        },
        'contentOptions' => [
            'class' => 'hod-approval',
            'style' => 'text-align: center; white-space: nowrap;',
        ],
    ],

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
                        'class' => 'btn-link btn-sm view-history-btn',
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

/**
 * Common buttons
 */
$viewPendingButton = Html::a(
    '<i class="fas fa-eye"></i> View Pending',
    [html::decode($level) . '-approval'],
    ['class' => 'btn btn-outline-success', 'id' => 'pending-btn']
);

$viewDisapprovedButton = Html::a(
    '<i class="fas fa-eye"></i> View Disapproved',
    ['disapproved-marks', 'level' => html::decode($level)],
    ['class' => 'btn btn-outline-danger', 'id' => 'approved-btn']
);

$viewApprovedButton = Html::a(
    '<i class="fas fa-eye"></i> View Approved',
    ['approved-marks', 'level' => html::decode($level)],
    ['class' => 'btn btn-outline-dark', 'id' => 'approved-btn']
);

$disabledDisapprovedButton = Html::a(
    ' Disapproved',
    [''],
    ['class' => 'btn btn-danger disabled', 'id' => 'approved-btn']
);

$disabledApprovedButton = Html::a(
    ' Approved Marks',
    [''],
    ['class' => 'btn btn-dark disabled', 'id' => 'approved-btn']
);

$resetButton = Html::a(
    '<i class="fas fa-redo"></i>',
    ['approved-marks', 'level' => html::decode($level)],
    ['data-pjax' => 1, 'class' => 'btn btn-outline-success', 'title' => Yii::t('yii2-ajaxcrud', 'Reset Grid')]
);

$publishButton = Html::a(
    '<i class="fas fa-share-square"></i> Publish',
    ['publish-marks', 'level' => Html::decode($level)],
    [
        'class' => 'btn btn-outline-primary',
        'title' => 'Push updated marks to the student portal',
    ]
);

/**
 * Toolbar content based on the view
 */
$toolbarContent = [
    'content' => ''
];
$before = '';
if ($view == 'approved-marks') {
    if ($level == 'dean') {
        $toolbarContent['content'] = $publishButton . " " . $viewPendingButton . ' ' . $viewDisapprovedButton . ' ' . $disabledApprovedButton . ' ' . $resetButton . '{toggleData}';
    } else {
        $toolbarContent['content'] = $viewPendingButton . ' ' . $viewDisapprovedButton . ' ' . $disabledApprovedButton . ' ' . $resetButton . '{toggleData}';
    }
    $before =  '<div class="text-muted fw-bold"><i class="fas fa-check-circle text-success"></i> ' . strtoupper(html::decode($level)) . ' APPROVED MISSING MARKS</div>';
} else {
    if ($level == 'dean') {

        $toolbarContent['content'] = $publishButton . " " . $viewPendingButton . ' ' . $disabledDisapprovedButton . ' ' . $viewApprovedButton . ' ' . $resetButton . '{toggleData}';
    } else {
        $toolbarContent['content'] =  $viewPendingButton . ' ' . $disabledDisapprovedButton . ' ' . $viewApprovedButton . ' ' . $resetButton . '{toggleData}';
    }
    $before =  '<div class="text-muted fw-bold"><i class="fas fa-times-circle text-danger"></i> ' . strtoupper(html::decode($level)) . ' DISAPPROVED MISSING MARKS</div>';
}

$toolbar = $toolbarContent;
?>

<?php
Pjax::begin(['id' => 'crud-datatable-pjax']);
try {

    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'id' => 'crud-datatable',
        'filterModel' => $searchModel,
        'export' => false,
        'pjax' => false,
        'toolbar' => [
            $toolbar
        ],
        'tableOptions' => ['class' => 'table table-bordered table-striped small-font'],
        'summary' => false,
        'columns' => $columns,
        'panel' => [
            'heading' => '<div style="font-size:14px; font-weight:bold;">STUDENT MARKSHEET - ' . strtoupper(html::decode($level)) . ' SECTION</div>',
            'headingOptions' => ['class' => 'text-white p-2', 'style' => 'border:solid; background-color:#304186!important;'],
            'before' => $before,
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
    'id' => 'history-modal',
    'size' => Modal::SIZE_LARGE,
    'options' => [
        'data-bs-backdrop' => 'static',
        'data-bs-keyboard' => 'false',
        'class' => 'modal-wide',
    ],
    'clientOptions' => [
        'backdrop' => 'static',
        'keyboard' => false,
    ],
]);
?>
<div class="modal-header">
    <h4 class="modal-title">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-dark">
                <i class="fas fa-history mx-4"></i>Previous Updates
            </div>
        </div>
    </h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <div id='history-content' class='p-3'>Loading...</div>
</div>
<?php
Modal::end();
?>

<?php

$this->registerCss('
        .modal-wide .modal-dialog {
            max-width: 90%;
            width: 90%;
        }
        .modal-dialog-wide {
            max-width: 95%;
            width: 95%;
        }
        ');



// Modal::begin([
//     'id' => 'history-modal',
//     'size' => Modal::SIZE_LARGE,
//     'options' => [
//         'data-bs-backdrop' => 'static',
//         'data-bs-keyboard' => 'false',
//         'class' => 'modal-wide',
//     ],
//     'dialogOptions' => [
//         'class' => 'modal-dialog-wide'
//     ],
//     'title' => '<div class="d-flex justify-content-between align-items-center">'
//         . '<div class="text-dark"><i class="fas fa-history mx-4"></i>Previous Updates</div>'
//         . '</div>',
// ]);

// echo "<div id='history-content' class='p-3'>Loading...</div>";

// Modal::end();

$this->registerCss('
        .modal-wide .modal-dialog {
            max-width: 70%;
            width: 70%;
        }
        /* Rotating Spinner */
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
            0% {
                transform: rotate(0deg); 
            }
            100% {
                transform: rotate(360deg); 
            }
        }

        .loading-text {
            text-align: center;
            color: #6c757d; 
            margin-top: 10px; 
        }
        ');
?>
<?php
$url = \yii\helpers\Url::to(['student-marksheet/view-history']);

$js = <<<JS

$(document).on('pjax:complete', function() {
    attachEventHandlers();
});

function attachEventHandlers() {

    $(document).on("click", ".view-history-btn", function (e) {
        e.preventDefault();

        var mrksheetId = $(this).data("mrksheet");
        var regNumber = $(this).data("regnumber");

        $("#history-content").html(`
                    <div class="spinner"></div>
                    <p class="loading-text">Loading history...</p>
                `);
        $.ajax({
            url: "$url",
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