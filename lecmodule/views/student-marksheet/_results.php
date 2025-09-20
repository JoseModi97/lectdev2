<?php

use yii\helpers\Html;
use app\models\ExamType;
use kartik\grid\GridView;
use yii\web\JsExpression;
use app\models\LecLateMark;
use yii\helpers\ArrayHelper;
use kartik\editable\Editable;
use yii\data\ArrayDataProvider;
use yii\web\ServerErrorHttpException;
?>

<?php
$examTypes = ArrayHelper::map(ExamType::find()->all(), 'EXAMTYPE_CODE', 'DESCRIPTION');

?>

<style>
    .popover {
        min-width: 200px;
    }

    .editable-popover .popover-content {
        padding: 9px 14px;
    }

    .kv-editable-input.form-control {
        height: calc(2.25rem + 2px);
    }
</style>
<?php if ($student): ?>
    <div class="student-details" style="margin-top: 30px;">

        <div class="marksheet-table" style="margin-top: 20px;">
            <?php
            try {
                echo GridView::widget([
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
                    'columns' => [
                        [
                            'class' => 'kartik\grid\SerialColumn',
                            'contentOptions' => ['class' => 'kartik-sheet-style'],

                        ],
                        [
                            'attribute' => 'courseDescription',
                            'header' => 'Course Description',
                            'label' => 'Course Description',
                            'value' => fn($model) => $model->courseDescription ?? '',
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
                            'value' => fn($model) => $model->semesterCode ?? '',
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
                                                    Swal.fire({
                                                        icon: "success",
                                                        title: "Success",
                                                        text: data.successMessage,
                                                    });
                                                } else {
                                                    Swal.fire({
                                                        icon: "success",
                                                        title: "Success",
                                                        text: "Success! Updated value: " + newValue,
                                                    });
                                                }
                                            }'
                                        ),
                                        "editableError" => new \yii\web\JsExpression(
                                            'function(event, xhr, status, error) { 
                                            Swal.fire({
                                                icon: "error",
                                                title: "Error",
                                                text: "Error: " + error + " - " + xhr.responseText,
                                            });
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
                                    'REGISTRATION_NUMBER' => $model->REGISTRATION_NUMBER
                                ];

                                $value = $model->COURSE_MARKS !== null ? $model->COURSE_MARKS : '';
                                $lateMarkModel = LecLateMark::findOne($condition);
                                if ($lateMarkModel && in_array($lateMarkModel->POST_STATUS, ['EDITING', 'ONGOING']) && $lateMarkModel->EXAM_MARKS !== null) {
                                    return "<span style='color: red;'>{$lateMarkModel->COURSE_MARKS}</span>";
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
                                    'header' => 'Course Mark',
                                    'inputType' => Editable::INPUT_SPIN,
                                    'asPopover'   => true,
                                    'beforeInput' => function ($form, $widget) use ($model) {
                                        return \yii\helpers\Html::hiddenInput('registrationNumber', $model->REGISTRATION_NUMBER);
                                    },

                                    'options' => [
                                        'pluginOptions' => ['min' => 0, 'max' => 5000],
                                    ],
                                    'formOptions' => ['action' => ['/student-marksheet/update-exam-marks']],
                                    'pluginEvents' => [
                                        "editableSuccess" => new JsExpression(
                                            'function(event, newValue, form, data) { 
                                        if (data.successMessage) {
                                            Swal.fire({
                                                icon: "success",
                                                title: "Success",
                                                text: data.successMessage,
                                            });
                                        } else {
                                            Swal.fire({
                                                icon: "success",
                                                title: "Success",
                                                text: "Success! Updated value: " + newValue,
                                            });
                                        }
                                    }'
                                        ),
                                        "editableError" => new JsExpression(
                                            'function(event, xhr, status, error) { 
                                            Swal.fire({
                                                icon: "error",
                                                title: "Error",
                                                text: "Error: " + error + " - " + xhr.responseText,
                                            });
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
                                    'REGISTRATION_NUMBER' => $model->REGISTRATION_NUMBER
                                ];
                                $value = $model->EXAM_MARKS !== null ? $model->EXAM_MARKS : '';
                                $lateMarkModel = \app\models\LecLateMark::findOne($condition);
                                if ($lateMarkModel && in_array($lateMarkModel->POST_STATUS, ['EDITING', 'ONGOING']) && $lateMarkModel->EXAM_MARKS !== null) {
                                    return "<span style='color: red;'>{$lateMarkModel->EXAM_MARKS}</span>";
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
                                    'pluginOptions' => ['min' => 0, 'max' => 5000],
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
                            'value' => fn($model) => $model->GRADE ?? '',
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
                            'value' => fn($model) => $model->POST_STATUS ?? '',
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
                                                    });
                                                } else {
                                                    Swal.fire({
                                                        icon: "success",
                                                        title: "Success",
                                                        text: "Success! Updated value: " + newValue,
                                                    });
                                                }
                                            }'
                                        ),
                                        "editableError" => new JsExpression(
                                            'function(event, xhr, status, error) { 
                                                Swal.fire({
                                                    icon: "error",
                                                    title: "Error",
                                                    text: "Error: " + error + " - " + xhr.responseText,
                                                });
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
                                    'REGISTRATION_NUMBER' => $model->REGISTRATION_NUMBER
                                ];
                                $lateMarkModel = LecLateMark::findOne($condition);
                                $value = $lateMarkModel ? substr($lateMarkModel->REMARKS, 0, 10) : '';
                                if ($lateMarkModel && in_array($lateMarkModel->POST_STATUS, ['EDITING', 'ONGOING']) && !empty($lateMarkModel->REMARKS)) {
                                    return "<span style='color: red; font-size:13px;' title='" . htmlspecialchars($lateMarkModel->REMARKS, ENT_QUOTES, 'UTF-8') . "'>{$value}</span>";
                                }
                                return $value;
                            },
                        ],
                        [
                            'attribute' => 'POST_STATUS',
                            'label' => 'STATUS',
                            'value' => function ($model) {
                                $condition = [
                                    'MRKSHEET_ID' => $model->MRKSHEET_ID,
                                    'REGISTRATION_NUMBER' => $model->REGISTRATION_NUMBER
                                ];
                                $lateMarkModel = LecLateMark::findOne($condition);
                                $value = $lateMarkModel ? $lateMarkModel->POST_STATUS : '';
                                if ($lateMarkModel && in_array($lateMarkModel->POST_STATUS, ['EDITING', 'ONGOING'])) {
                                    return "<span style='color: red; font-size:13px;'>{$value}</span>";
                                }
                                return $value;
                            },
                            'headerOptions' => ['style' => 'width: 4%;'],
                            'contentOptions' => ['style' => 'background-color: white;'],
                            'vAlign' => 'middle',
                            'format' => 'raw',
                        ],
                    ],
                    'panel' => [
                        'heading' => '<div style="font-size:14px; font-weight:bold;">STUDENT MARKSHEET</h5></div>',
                        'headingOptions' => ['class' => 'text-white p-2', 'style' => 'border:solid; background-color:#304186!important; ; '],
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
        </div>
    </div>
<?php endif; ?>