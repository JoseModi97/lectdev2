<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 15-12-2020 20:50:22 
 * @modify date 15-12-2020 20:50:22 
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
                'options' => ['enctype'=>'multipart/form-data']
            ]);
        ?>
        <div id='edit-cw-loader'></div>
        <input type="hidden" readonly="" name="TYPE" id="TYPE" value="<?=$type;?>">
        <?=$form->field($cwModel, 'ASSESSMENT_ID')->hiddenInput()->label(false);?>
        <?=$form->field($assessmentTypeModel, 'ASSESSMENT_TYPE_ID')->hiddenInput()->label(false);?>
        <div class="form-group">
            <?=$form->field($courseModel, 'COURSE_CODE')->textInput(['id' =>'course-code-edit', 'readonly'=>true])->label('COURSE CODE');?>
        </div>
        <div class="form-group">
            <?=$form->field($courseModel, 'COURSE_NAME')->textInput(['id' =>'course-name-edit', 'readonly'=>true])->label('COURSE NAME');?>
        </div>
        <div class="form-group">
            <label for="assessment-name" class="form-label">ASSESSMENT NAME</label>
            <input type="text" name="ASSESSMENT_NAME" id="assessment-name" class="form-control" 
                value="<?=str_replace('EXAM_COMPONENT ','', $assessmentTypeModel->ASSESSMENT_NAME);?>">
        </div>
        <div class="form-group">
            <?=$form->field($cwModel, 'WEIGHT')->label('WEIGHT');?>
        </div>
        <div class="form-group">
            <?=$form->field($cwModel, 'DIVIDER')->label('MARKS OUT OF');?>
        </div>
        <div class="form-group">
            <?=$form->field($cwModel, 'RESULT_DUE_DATE')->widget(DatePicker::classname(),[
                'options' => ['placeholder' => 'result due date..'],
                'pluginOptions' => [
                    'autoclose'=>true
                ]
            ])->label('Result due date');?>
        </div>
        <div class="form-group">
            <?= Html::submitButton('Update', ['id' => 'edit-cw-btn','class'=>'btn form-control'])?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<!-- START PAGE CSS AND JS-->
<?php
$editCourseworkScript = <<< JS
    $('#edit-cw-btn').click(function(e){
        $('#edit-cw-loader').html('<h1 class="text-center text-primary" style="font-size: 100px;"><i class="fas fa-spinner fa-pulse"></i></h1>');
    });   
JS;
$this->registerJs($editCourseworkScript, yii\web\View::POS_END);