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
        'contentOptions' => ['style' => 'width:40%;'],
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
    ]
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
                'pjax' => false,
                'toolbar' => [
                    [
                        'content' =>
                        Html::button('<i class="fas fa-check"></i> Publish', ['class' => 'btn btn-success', 'id' => 'publish-btn']) . ' ' .
                            Html::a(
                                '<i class="fa fa-redo"></i>',
                                [''],
                                ['data-pjax' => 1, 'class' => 'btn btn-outline-success', 'title' => Yii::t('yii2-ajaxcrud', 'Reset Grid')]
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
                            STUDENT MARKSHEET - DEAN SECTION
                            <div class="text-white mt-1" style="font-size:12px; font-weight:normal;">
                                <i class="bi bi-info-circle"></i> 
                                Review records carefully before publishing to student portal
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
                        ['disapproved-marks', 'level' => 'dean'],
                        ['class' => 'btn btn-outline-danger', 'id' => 'approved-btn']
                    ) . '
                            ' . Html::a(
                        '<i class="fas fa-eye"></i> View Approved',
                        ['approved-marks', 'level' => 'dean'],
                        ['class' => 'btn btn-outline-dark', 'id' => 'approved-btn']
                    ) . '
                            ' . Html::a(
                        '<i class="fas fa-share-square"></i> Published',
                        ['published-marks', 'level' => 'dean'],
                        ['class' => 'btn btn-outline-success', 'id' => 'published-btn']
                    ) . '
                        </div>
                        <div class="card border-primary mb-3">
                            <div class="card-header text-white" style="background-color:#304186!important;">
                                <i class="bi bi-info-circle-fill"></i>
                                Publishing Guidelines
                            </div>
                            <div class="card-body text-dark">
                                <p>
                                    <strong>Publishing Notice:</strong> Ensure all missing or updated marks are accurate and verified before publishing to the student portal.
                                </p>
                                <p>
                                    <strong>Instructions:</strong> Review the updated records carefully. Select the entries you want to publish.
                                </p>
                                <p>
                                    Click <b>"Publish"</b> to make the selected marks visible to students. If any discrepancies are found, update the records before publishing.
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
        'header' => '<span class="text-dark">Enter Disapproval Comment</span>', 
        'options' => [
            'data-backdrop' => 'static',    
            'data-keyboard' => 'false',     
        ],
        'footer' => Html::button('Submit', ['class' => 'btn btn-danger', 'id' => 'submit-disapprove'])
    ]);

    echo '<textarea id="disapprove-comment" class="form-control" rows="4" placeholder="Enter comment..."></textarea>';

    Modal::end();

    $publishUrl = \yii\helpers\Url::to(['publish']);

    $js = <<<JS
$(document).on('pjax:complete', function() {
    attachEventHandlers();
});

function attachEventHandlers() {
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

    $(document).off('click', '#publish-btn').on('click', '#publish-btn', function() {
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
            text: "You are about to publish the selected records.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, publish it!'  
        }).then((result) => {
            if(result.isConfirmed){
                Swal.fire({
                    title: 'Publishing...',
                    text: 'Please wait while we publish the records.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '$publishUrl',
                    type: 'POST',
                    data: {selected: selected},
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Published!',
                            text: response.message,
                            timer: 1500,  
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
}

attachEventHandlers();
JS;

    $this->registerJs($js);
    ?>