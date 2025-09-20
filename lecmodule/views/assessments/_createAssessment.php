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
/* @var $marksheetDefModel app\models\MarksheetDef */
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

<div class="create-assessment-form">
    <div class="form-border">
        <?php
            $form = ActiveForm::begin([
                'id' => 'create-assessment-form',
                'action' => Url::to(['/assessments/save']),
                'enableAjaxValidation' => false,
                'options' => ['enctype'=>'multipart/form-data']
            ]);
        ?>
        <div id='create-assessment-loader'></div>
        <input type="hidden" readonly="" name="TYPE" id="TYPE" value="<?=$type;?>">
        <?=$form->field($marksheetDefModel, 'MRKSHEET_ID')->hiddenInput()->label(false);?>
        <div class="form-group">
            <?=$form->field($courseModel, 'COURSE_CODE')->textInput(['id' =>'course-code-create', 'readonly'=>true])->label('COURSE CODE');?>
        </div>
        <div class="form-group">    
            <?=$form->field($courseModel, 'COURSE_NAME')->textInput(['id' =>'course-name-create', 'readonly'=>true])->label('COURSE NAME');?>
        </div>
        <div class="form-group">   
            <?=$form->field($assessmentTypeModel, 'ASSESSMENT_NAME')->textInput(['id' =>'assessment-name-create'])->label('ASSESSMENT NAME');?>
        </div>
        <div class="form-group">   
            <?=$form->field($cwModel, 'DIVIDER')->label('MARKS OUT OF');?>
        </div>
        <div class="form-group"> 
            <?=$form->field($cwModel, 'RESULT_DUE_DATE')->widget(DatePicker::classname(),[
                'options' => ['placeholder' => 'result due date..'],
                'pluginOptions' => [
                    'autoclose'=>true,
                ]
            ])->label('RESULT DUE DATE');?>
        </div>
        <div class="form-group">
            <?= Html::submitButton('Save', ['id' => 'create-assessment-btn','class'=>'btn form-control'])?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<!-- START PAGE CSS AND JS-->
<?php
$createAssessmentJs = <<< JS
    $('#create-assessment-btn').click(function(e){
        $('#create-assessment-loader')
        .html('<h1 class="text-center text-primary" style="font-size: 100px;"><i class="fas fa-spinner fa-pulse"></i></h1>');
    });   
JS;
$this->registerJs($createAssessmentJs, yii\web\View::POS_END);


