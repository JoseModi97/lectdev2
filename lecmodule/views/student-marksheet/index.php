<?php

/**
 * @author Jack
 * @email jackmutiso37@gmail.com
 */

use yii\helpers\Html;
use app\models\ExamType;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\web\JsExpression;
use app\models\LecLateMark;
use yii\helpers\ArrayHelper;
use kartik\editable\Editable;
use yii\bootstrap\ActiveForm;
use yii\data\ArrayDataProvider;
use yii\web\ServerErrorHttpException;
?>



<?php
$examTypes = ArrayHelper::map(ExamType::find()->all(), 'EXAMTYPE_CODE', 'DESCRIPTION');

?>

<div class="student-marks-form" style="display: flex; justify-content: center; align-items: center;">
    <div style="width: 40%; max-width: 900px;">
        <?php $form = ActiveForm::begin(); ?>

        <table border="1" style="width: 100%; border-collapse: collapse; background-color: #cce5ff; text-align: left;">
            <tr style="background-color: #5c84c3; color: white;">
                <th colspan="2" style="padding: 8px; text-align: center;">Student Registration Number</th>
            </tr>
            <tr>
                <td style="padding: 8px;"><strong>Registration Number:</strong></td>
                <td style="padding: 8px;">
                    <?= $form->field($model, 'REGISTRATION_NUMBER')->textInput([
                        'maxlength' => true,
                        'required'  => true,
                    ])->label(false) ?>
                </td>

            </tr>
            <tr>
                <td colspan="2" style="padding: 8px; text-align: center;">
                    <?= Html::submitButton('View Marksheet', ['class' => 'btn manage-button']) ?>
                    <?php //Html::a('Examination System', ['/exam-system'], ['class' => 'btn btn-link disabled']) ?>
                </td>
            </tr>
        </table>

        <?php ActiveForm::end(); ?>
    </div>
</div>


<?php if ($student): ?>

    <div class="student-details" style="margin-top: 30px;">

        <div class="marksheet-table" style="margin-top: 20px;">
            <?php \yii\widgets\Pjax::begin(['id' => 'marksheet-grid']); ?>
            <?php
            try {
                echo GridView::widget([
                    'id' => "marksheet_table",
                    'dataProvider' => new ArrayDataProvider([
                        'allModels' => $marksheets,
                        'sort' => [
                            'attributes' => ['courseDescription'],
                            'defaultOrder' => ['courseDescription' => SORT_ASC],
                        ],
                        'pagination' => false,
                        'key' => 'MRKSHEET_ID',
                    ]),
                    'tableOptions' => ['class' => 'table table-bordered table-striped'],
                    'summary' => false,
                    'export' => false,
                    'columns' => [
                        [
                            'class' => 'kartik\grid\SerialColumn',
                            'contentOptions' => ['class' => 'kartik-sheet-style'],

                        ],
                        [
                            'attribute' => 'courseDescription',
                            'header' => 'Course Description',
                            'label' => 'Course Description',
                            'value' => function ($model) {
                                return $model->courseDescription ?? '';
                            },
                            'headerOptions' => ['style' => 'width: 44%;'],
                            'contentOptions' => ['style' => 'background-color: #f8f9fa;'],
                        ],
                        [
                            'attribute' => 'MRKSHEET_ID',
                            'label' => 'Year',
                            'value' => function ($model) {
                                if (!empty($model->MRKSHEET_ID) && strpos($model->MRKSHEET_ID, '_') !== false) {
                                    return explode('_', $model->MRKSHEET_ID)[0];
                                }
                                return $model->MRKSHEET_ID;
                            },
                            'headerOptions' => ['style' => 'width: 7%;'],
                            'contentOptions' => ['style' => 'background-color: #f8f9fa;'],
                            'vAlign' => 'middle',
                        ],
                        [
                            'attribute' => 'semesterCode',
                            'label' => 'Sem',
                            'value' => function ($model) {
                                return $model->semesterCode ?? '';
                            },
                            'headerOptions' => ['style' => 'width: 2%;'],
                            'contentOptions' => ['style' => 'background-color: #f8f9fa;'],
                            'vAlign' => 'middle',
                        ],
                        [
                            'class' => 'kartik\grid\EditableColumn',
                            'attribute' => 'COURSE_MARKS',
                            'label' => 'Cat',
                            'editableOptions' => function ($model) {
                                return [

                                    'model' => new \app\models\LecLateMark(),
                                    'header' => 'Course Mark',
                                    'inputType' => \kartik\editable\Editable::INPUT_SPIN,
                                    'asPopover' => true,


                                    'beforeInput' => function ($form, $widget) use ($model) {
                                        return \yii\helpers\Html::hiddenInput('registrationNumber', $model->REGISTRATION_NUMBER);
                                    },

                                    'options' => [
                                        'pluginOptions' => ['min' => 0, 'max' => 5000],
                                    ],
                                    'formOptions' => [
                                        'action' => ['/student-marksheet/update-course-mark'],
                                    ],
                                    'pluginEvents' => [
                                        "editableSuccess" => new \yii\web\JsExpression(
                                            'function(event, newValue, form, data) { 
                                                if (data.successMessage) {

                                                    let targetId = CSS.escape(data.target.replace("#", "")); 
                                                    let btn = document.querySelector("#" + targetId);
                                                    
                                                    Swal.fire({
                                                        icon: "success",
                                                        title: "Success",
                                                        text: data.successMessage,
                                                        timer: 1500, 
                                                    });

                                                    if (btn) {
                                                        btn.classList.add("btn-info");
                                                        btn.classList.remove("disabled");
                                                        btn.innerHTML = "<i class=\'fas fa-check-circle\'></i> Updated";
                                                    }
                                                } 
                                                    else {
                                                    Swal.fire({
                                                        icon: "success",
                                                        title: "Success",
                                                        text: "Success! Updated value: " + newValue,
                                                        timer: 1500, 
                                                    });
                                                }
                                            }'
                                        ),
                                        "editableError" => new \yii\web\JsExpression(
                                            'function(event, xhr, status, error) { 
                                            // Swal.fire({
                                            //     icon: "error",
                                            //     title: "Error",
                                            //     text: "Error: " + error + " - " + xhr.responseText,
                                            // });
                                        }'
                                        ),
                                    ],
                                ];
                            },
                            'hAlign' => 'right',
                            'vAlign' => 'middle',
                            'headerOptions' => ['style' => 'width: 4%;'],
                            'format' => 'raw',
                            'value' => function ($model) {
                                $condition = [
                                    'MRKSHEET_ID' => $model->MRKSHEET_ID,
                                    'REGISTRATION_NUMBER' => $model->REGISTRATION_NUMBER,
                                    'RECORD_VALIDITY' => 'VALID',

                                ];

                                $value = $model->COURSE_MARKS !== null ? $model->COURSE_MARKS : '';
                                $lateMarkModel = LecLateMark::findOne($condition);

                                if ($lateMarkModel && $lateMarkModel->RECORD_VALIDITY == "VALID" && $lateMarkModel->COURSE_MARKS !== null) {
                                    // if ($lateMarkModel->POST_STATUS == 'EDITING') {
                                    //     return "<span class='text-danger'>{$lateMarkModel->COURSE_MARKS}</span>";
                                    // } else {
                                    return "<span class='text-success'>{$lateMarkModel->COURSE_MARKS}</span>";
                                    // }
                                }
                                return $value;
                            },
                        ],



                        [
                            'class' => 'kartik\grid\EditableColumn',
                            'attribute' => 'EXAM_MARKS',
                            'label' => 'Exam',
                            'editableOptions' => function ($model) {
                                return [
                                    'model' => new LecLateMark(),
                                    'header' => 'Exam Mark',
                                    'inputType' => Editable::INPUT_SPIN,
                                    'asPopover'   => true,
                                    'beforeInput' => function ($form, $widget) use ($model) {
                                        return \yii\helpers\Html::hiddenInput('registrationNumber', $model->REGISTRATION_NUMBER);
                                    },

                                    'options' => [
                                        'pluginOptions' => ['min' => 0, 'max' => 100],
                                    ],
                                    'formOptions' => ['action' => ['/student-marksheet/update-exam-marks']],
                                    'pluginEvents' => [
                                        "editableSuccess" => new JsExpression(
                                            'function(event, newValue, form, data) { 
                                        if (data.successMessage) {

                                            let targetId = CSS.escape(data.target.replace("#", "")); 
                                            let btn = document.querySelector("#" + targetId);
                                            if (btn) {
                                                btn.classList.add("btn-info");
                                                btn.classList.remove("disabled");
                                                btn.innerHTML = "<i class=\'fas fa-check-circle\'></i> Updated";
                                            }
                                            Swal.fire({
                                                icon: "success",
                                                title: "Success",
                                                text: data.successMessage,
                                                timer: 1500, 
                                            });

                                        } else {
                                            Swal.fire({
                                                icon: "success",
                                                title: "Success",
                                                text: "Success! Updated value: " + newValue,
                                                timer: 1500, 
                                            });
                                        }
                                    }'
                                        ),
                                        "editableError" => new JsExpression(
                                            'function(event, xhr, status, error) { 
                                            // Swal.fire({
                                            //     icon: "error",
                                            //     title: "Error",
                                            //     text: "Error: " + error + " - " + xhr.responseText,
                                            // });
                                        }'
                                        ),
                                    ],

                                ];
                            },
                            'hAlign' => 'right',
                            'vAlign' => 'middle',
                            'headerOptions' => ['style' => 'width: 4%;'],
                            'format' => 'raw',
                            'pageSummary' => true,
                            'value' => function ($model) {
                                $condition = [
                                    'MRKSHEET_ID' => $model->MRKSHEET_ID,
                                    'REGISTRATION_NUMBER' => $model->REGISTRATION_NUMBER,
                                    'RECORD_VALIDITY' => 'VALID',

                                ];

                                $value = $model->EXAM_MARKS !== null ? $model->EXAM_MARKS : '';
                                $lateMarkModel = LecLateMark::findOne($condition);

                                if ($lateMarkModel && $lateMarkModel->RECORD_VALIDITY == "VALID" && $lateMarkModel->EXAM_MARKS !== null) {

                                    return "<span class='text-success'>{$lateMarkModel->EXAM_MARKS}</span>";
                                }
                                return $value;
                            },
                        ],
                        [
                            'class' => 'kartik\grid\EditableColumn',
                            'attribute' => 'FINAL_MARKS',
                            'label' => 'MARKS',
                            'readonly' => true,
                            'editableOptions' => [
                                'header' => 'Final Marks',
                                'inputType' => \kartik\editable\Editable::INPUT_SPIN,
                                'asPopover'     => true,
                                'options' => [
                                    'pluginOptions' => ['min' => 0, 'max' => 100],
                                ],
                                'formOptions' => ['action' => ['/student-marksheet/update-marks']],
                            ],
                            'hAlign' => 'right',
                            'vAlign' => 'middle',
                            'headerOptions' => ['style' => 'width: 5%;'],
                            'format' => ['decimal', 2],
                            'pageSummary' => true,
                            'value' => function ($model) {
                                return $model->FINAL_MARKS !== null ? $model->FINAL_MARKS : '';
                            },
                        ],
                        [
                            'attribute' => 'GRADE',
                            'label' => 'Grade',
                            'value' => function ($model) {
                                return $model->GRADE  ?? '';
                            },
                            'contentOptions' => ['style' => 'background-color: white;'],
                            'vAlign' => 'middle',
                        ],
                        [
                            'attribute' => 'EXAM_TYPE',
                            'label' => 'Exam Type',
                            'value' => function ($model) use ($examTypes) {
                                $displayValue = $examTypes[$model->EXAM_TYPE] ?? $model->EXAM_TYPE;
                                return  "<span style='font-size:13px !important;'>" . ucfirst($displayValue) . "</span>";
                            },
                            'contentOptions' => ['style' => 'background-color: white; white-space: nowrap;'],
                            'vAlign' => 'middle',
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'POST_STATUS',
                            'label' => 'Result Status',
                            'value' => function ($model) {
                                return $model->POST_STATUS  ?? '';
                            },
                            'contentOptions' => ['style' => 'background-color: #f8f9fa; font-size:13px;'],
                            'vAlign' => 'middle',
                        ],
                        [
                            'class' => 'kartik\grid\EditableColumn',
                            'attribute' => 'REMARKS',
                            'label' => 'Remarks',
                            'headerOptions' => ['style' => 'width: 7%;  '],
                            'editableOptions' => function ($model) {
                                return [
                                    'model' => new LecLateMark(),
                                    'header' => 'Remarks',
                                    'inputType' => Editable::INPUT_TEXT,
                                    'asPopover'   => true,
                                    'beforeInput' => function ($form, $widget) use ($model) {
                                        return Html::hiddenInput('registrationNumber', $model->REGISTRATION_NUMBER);
                                    },
                                    'formOptions' => ['action' => ['/student-marksheet/update-remarks']],
                                    'pluginEvents' => [
                                        "editableSuccess" => new JsExpression(
                                            'function(event, newValue, form, data) { 
                                                if (data.successMessage) {
                                               
                                                    Swal.fire({
                                                        icon: "success",
                                                        title: "Success",
                                                        text: data.successMessage,
                                                        timer: 1500, 
                                                    });

                                                } else {
                                                    Swal.fire({
                                                        icon: "success",
                                                        title: "Success",
                                                        text: "Success! Updated value: " + newValue,
                                                        timer: 1500, 
                                                    });
                                                }
                                                 
                                            }'
                                        ),
                                        "editableError" => new JsExpression(
                                            'function(event, xhr, status, error) { 
                                                // Swal.fire({
                                                //     icon: "error",
                                                //     title: "Error",
                                                //     text: "Error: " + error + " - " + xhr.responseText,
                                                // });
                                            }'
                                        ),
                                    ],
                                ];
                            },
                            'hAlign' => 'left',
                            'vAlign' => 'middle',
                            'format' => 'raw',
                            'value' => function ($model) {
                                $condition = [
                                    'MRKSHEET_ID' => $model->MRKSHEET_ID,
                                    'REGISTRATION_NUMBER' => $model->REGISTRATION_NUMBER,
                                    'RECORD_VALIDITY' => 'VALID',
                                ];
                                $lateMarkModel = LecLateMark::findOne($condition);
                                $value = $lateMarkModel ? substr($lateMarkModel->REMARKS, 0, 8) : '';
                                if ($lateMarkModel && in_array($lateMarkModel->POST_STATUS, ['EDITING', 'ONGOING']) && !empty($lateMarkModel->REMARKS)) {
                                    if ($lateMarkModel->POST_STATUS == 'EDITING') {
                                        return "<span class='text-danger' style='font-size:13px;' title='" . htmlspecialchars($lateMarkModel->REMARKS, ENT_QUOTES, 'UTF-8') . "'>{$value}</span>";
                                    }
                                    return "<span class='text-success' style='font-size:13px;' title='" . htmlspecialchars($lateMarkModel->REMARKS, ENT_QUOTES, 'UTF-8') . "'>{$value}</span>";
                                }
                                return $value;
                            },
                        ],
                        [
                            'class' => 'kartik\grid\ActionColumn',
                            'header' => 'Actions',
                            'template' => '{history}',
                            'buttons' => [
                                'history' => function ($url, $model, $key) {
                                    $hasHistory = LecLateMark::find()
                                        ->where([
                                            'MRKSHEET_ID' => $model->MRKSHEET_ID,
                                            'REGISTRATION_NUMBER' => $model->REGISTRATION_NUMBER
                                        ])
                                        ->exists();

                                    return Html::a(
                                        '<i class="fas fa-history"></i> ' . ($hasHistory ? 'View History' : 'No Updates'),
                                        '#',
                                        [
                                            'class' => 'btn btn-sm view-history-btn ' . ($hasHistory ? 'btn-info' : 'btn-secondary disabled'),
                                            'title' => $hasHistory ? 'View History' : 'No history available',
                                            'id' => $model->MRKSHEET_ID . "_" . $model->REGISTRATION_NUMBER,
                                            'data-bs-toggle' => 'modal',
                                            'data-bs-target' => '#history-modal',
                                            'data-mrksheet' => $model->MRKSHEET_ID,
                                            'data-regnumber' => $model->REGISTRATION_NUMBER,
                                        ]
                                    );
                                },
                            ],
                            'headerOptions' => ['style' => 'width: 10%; text-align: center;'],
                            'contentOptions' => ['style' => 'text-align: center; white-space: nowrap;'],
                        ],



                    ],
                    'panel' => [
                        'heading' => '<div style="font-size:14px; font-weight:bold;">STUDENT MARKSHEET</h5></div>',
                        'headingOptions' => ['class' => 'text-white p-2', 'style' => 'border:solid; background-color:#304186!important;  '],
                        'before' => $this->render('_student_info', ['student' => $student]),
                        'beforeOptions' => ['style' => 'border-bottom:1px solid #2a68af;']
                    ],
                ]);
            } catch (Throwable $ex) {
                $message = $ex->getMessage();
                if (YII_ENV_DEV) {
                    $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
                }
                throw new ServerErrorHttpException($message, 500);
            }
            ?>
            <?php \yii\widgets\Pjax::end(); ?>
        </div>
        <?php if ($student): ?>
            <div class="text-center" style="margin-top: 0px;">
                <?= Html::button('Submit Marksheet', [
                    'class' => 'btn btn-success',
                    'id' => 'submit-marksheet-button'
                ]) ?>
            </div>
        <?php endif; ?>

    </div>
    <style>
        .table thead th {
            background-color: #2c3e50;
            color: white;
            border-bottom: 2px solid #ddd;
        }

        .card {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-control:focus {
            border-color: #2c3e50;
            box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.25);
        }
    </style>

<?php endif; ?>


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
');
?>

<?php
$submitUrl = \yii\helpers\Url::to(['/student-marksheet/submit-marksheet']);
$regNumber = $student->REGISTRATION_NUMBER ?? "";
$js = <<<JS
$(document).ready(function() {
    $('.kv-editable-input').on('focus', function() {
        $(this).css({
            'box-shadow': 'none',
            'outline': 'none'
        });
    });
});

$('#submit-marksheet-button').on('click', function(){
    Swal.fire({
       title: 'Are you sure?',
       text: "You are about to submit the marksheet.",
       icon: 'warning',
       showCancelButton: true,
       confirmButtonText: 'Yes, submit it!'
    }).then((result) => {
       if(result.isConfirmed){
          Swal.fire({
             title: 'Submitting...',
             text: 'Please wait while we submit the marksheet.',
             allowOutsideClick: false,
             didOpen: () => {
                Swal.showLoading();
             }
          });
          $.ajax({
              url: '$submitUrl',
              type: 'POST',
              data: { registrationNumber: '$regNumber' },
              success: function(response) {
                Swal.close(); 
                if (response.success) {
                      Swal.fire({
                        title: 'Success!',
                        text: 'Status updated successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        timer: 1500, 
                      });
                  } else {
                      Swal.fire('Error!', response.message, 'error');
                  }
              },
              error: function(xhr, status, error){
                  Swal.close();
                  Swal.fire('Error!', error, 'error');
              }
          });
       }
    });
});

function attachHistoryEvent() {
    $(".view-history-btn").off("click").on("click", function (e) {
        e.preventDefault();
        var mrksheetId = $(this).data("mrksheet");
        var regNumber = $(this).data("regnumber");

        $("#history-modal").modal('show');  
        
        $("#history-content").html("<p class='text-muted'>Loading history...</p>");

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

attachHistoryEvent();

$(document).on('pjax:complete', '#marksheet-grid', function() {
    attachHistoryEvent();
});

JS;
$this->registerJs($js);
?>