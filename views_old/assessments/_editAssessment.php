<?php

/**
 * @author Jack Jmm
 * @email jackmutiso37@gmail.com
 * @desc [description]
 */

/* @var $this yii\web\View */
/* @var $cwModel app\models\CourseWorkAssessment */
/* @var $courseModel app\models\Course */
/* @var $assessmentTypeModel app\models\AssessmentType */
/* @var $title string */
/* @var $type string */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\date\DatePicker;

$this->title = $title;
?>

<div class="lecturer-create-cw-form">
    <div class="form-border">
        <?php
        $form = ActiveForm::begin([
            'id' => 'edit-cw-form',
            'action' => Url::to(['/assessments/update']),
            'enableAjaxValidation' => false,
            'options' => ['enctype' => 'multipart/form-data']
        ]);
        ?>
        <div id='edit-cw-loader'></div>
        <input type="hidden" readonly="" name="TYPE" id="TYPE" value="<?= $type; ?>">
        <?= $form->field($cwModel, 'ASSESSMENT_ID')->hiddenInput()->label(false); ?>
        <?= $form->field($assessmentTypeModel, 'ASSESSMENT_TYPE_ID')->hiddenInput()->label(false); ?>
        <div class="form-group">
            <?= $form->field($courseModel, 'COURSE_CODE')->textInput(['id' => 'course-code-edit', 'readonly' => true])->label('COURSE CODE'); ?>
        </div>
        <div class="form-group">
            <?= $form->field($courseModel, 'COURSE_NAME')->textInput(['id' => 'course-name-edit', 'readonly' => true])->label('COURSE NAME'); ?>
        </div>
        <div class="form-group">
            <label for="assessment-name" class="form-label">ASSESSMENT NAME</label>
            <input type="text" name="ASSESSMENT_NAME" id="assessment-name" class="form-control"
                value="<?= str_replace('EXAM_COMPONENT ', '', $assessmentTypeModel->ASSESSMENT_NAME); ?>">
        </div>
        <div class="form-group">
            <?= $form->field($cwModel, 'WEIGHT')->label('WEIGHT'); ?>
        </div>
        <div class="form-group">
            <?= $form->field($cwModel, 'DIVIDER')->label('MARKS OUT OF'); ?>
        </div>
        <div class="form-group">
            <?= $form->field($cwModel, 'RESULT_DUE_DATE')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'result due date..'],
                'pluginOptions' => [
                    'autoclose' => true
                ]
            ])->label('Result due date'); ?>
        </div>
        <div class="form-group">
            <?= Html::submitButton('Update', ['id' => 'edit-cw-btn', 'class' => 'btn form-control']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<style>
    .form-label,
    .control-label {
        font-weight: 600;
        font-size: 0.875rem;
        color: #374151;
        margin-bottom: 0.5rem;
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .form-control {
        width: 100%;
        padding: 0.5rem 1rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #374151;
        background-color: #ffffff;
        background-clip: padding-box;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .form-control:focus {
        border-color: linear-gradient(#455492, #304186, #455492) !important;
        outline: 0;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        background-color: #ffffff;
    }

    .form-control:hover {
        border-color: #9ca3af;
    }

    .form-control[readonly] {
        background-color: #f9fafb;
        color: #6b7280;
        cursor: not-allowed;
        border-color: #d1d5db;
    }

    .form-control[readonly]:focus {
        box-shadow: none;
        border-color: #d1d5db;
    }

    #edit-cw-btn {
        background: linear-gradient(#455492, #304186, #455492) !important;
        border: none;
        color: white;
        font-weight: 600;
        font-size: 1rem;
        padding: 0.5rem 2rem;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 1rem;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    #edit-cw-btn:hover {
        background: linear-gradient(#455492, #304186, #455492) !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
    }

    #edit-cw-btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }

    #edit-cw-btn:disabled {
        background: #9ca3af;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .kv-plugin-loading {
        background-color: transparent !important;
    }

    .input-group .form-control {
        border-right: none;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .input-group-addon {
        background-color: #f8f9fa;
        border: 2px solid #e5e7eb;
        border-left: none;
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
        padding: 0.75rem 1rem;
        color: #6b7280;
    }

    .help-block {
        font-size: 0.75rem;
        color: #ef4444;
        margin-top: 0.25rem;
        font-weight: 500;
    }

    .has-error .form-control {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .has-error .control-label {
        color: #ef4444;
    }

    .has-success .form-control {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }
    @media (max-width: 768px) {
        .lecturer-create-cw-form {
            margin: 1rem auto;
            padding: 0 0.5rem;
        }

        .form-border {
            padding: 1.5rem;
            margin: 0 0.5rem;
        }

        .form-control {
            font-size: 16px;
            /* Prevents zoom on iOS */
        }
    }

    .form-control::placeholder {
        color: #9ca3af;
        opacity: 1;
    }

    .form-group:focus-within .control-label {
        color: #3b82f6;
    }
    .form-group:last-of-type {
        margin-bottom: 0;
    }
</style>
<!-- START PAGE CSS AND JS-->
<?php
$editCourseworkScript = <<< JS
    $('#edit-cw-btn').click(function(e){
        $('#edit-cw-loader').html('<h1 class="text-center text-primary" style="font-size: 100px;"><i class="fas fa-spinner fa-pulse"></i></h1>');
    });   
JS;
$this->registerJs($editCourseworkScript, yii\web\View::POS_END);
