<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 28-01-2021 15:16:04 
 * @modify date 28-01-2021 15:16:04 
 * @desc [description]
 */

/* @var $this yii\web\View */
/* @var $cwModel app\models\StudentCoursework */
/* @var $examUpdateModel app\models\ExamUpdateApproval */
/* @var $title string */ 
/* @var $studentCourseworkId string */
/* @var $level string */ 
/* @var $maximumMarks string */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = $title;
?>

<div class="edit-marks">
    <div class="form-border">
        <?php
            $form = ActiveForm::begin([
                'id' => 'edit-marks-form',
                'action' => Url::to(['/marks-approval/update-marks']),
                'enableAjaxValidation' => false,
                'options' => ['enctype'=>'multipart/form-data']
            ]);
        ?>

        <div class="row">
            <div id="edit-marks-loader"></div>
        </div>

        <input type="text" name="LEVEL" value="<?=$level;?>" hidden>

        <?=$form->field($cwModel, 'COURSE_WORK_ID')->hiddenInput()->label(false);?>

        <div class="form-group">
            <label for="edit-marks-CURRENT-MARKS" class="form-label mt-4">Current marks</label>
            <?=$form->field($cwModel, 'RAW_MARK')
                ->textInput([
                    'id' =>'edit-marks-CURRENT-MARKS', 
                    'class'=>'form-control',
                    'readonly'=>true
                ])
                ->label(false);?>
            <small id="currentmarksHelp" class="form-text text-muted">
                For marksheets with single exams, this field contains the Marks value<br/>
                For marksheets with multiple exam components, this field contains the Raw marks value
            </small>
        </div>

        <div class="form-group">
            <label for="edit-marks-RECOMMENDED-MARKS" class="form-label mt-4">New recommended marks</label>
            <?=$form->field($examUpdateModel, 'RECOMMENDED_MARKS')
                ->textInput([
                    'type' => 'number',
                    'id' =>'edit-marks-RECOMMENDED-MARKS', 
                    'class'=>'form-control',
                    'min' => '0',
                    'step' => '0.01',
                ])
                ->label(false);?>
        </div>

        <div class="form-group">
            <label for="edit-marks-MARK" class="form-label mt-4">New marks</label>
            <?=$form->field($examUpdateModel, 'CHANGD_TO')
                ->textInput([
                    'type' => 'number',
                    'id' =>'edit-marks-MARK',
                    'class'=>'form-control',
                    'min' => '0',
                    'max' => $maximumMarks,
                    'step' => '0.01',
                    'oninput' => "validity.valid||(value='')"
                ])
                ->label(false);?>
            <small id="rawmarksHelp" class="form-text text-muted">
                The new marks must not exceed the maximum: <?=$maximumMarks;?>
            </small>
        </div>

        <div class="form-group">
            <label for="edit-marks-REMARKS" class="form-label mt-4">Remarks</label>
            <?=$form->field($cwModel, 'REMARKS')
                ->textInput([
                    'id' =>'edit-marks-REMARKS',
                    'class'=>'form-control'
                ])
                ->label(false);?>
        </div>

        <div class="form-group">
            <?= Html::submitButton('Update', ['id' => 'update-marks-btn', 'class'=>'btn form-control'])?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<!-- START PAGE CSS AND JS-->
<?php
$editMarksScript = <<< JS
    $('#update-marks-btn').click(function(e){
        $('#edit-marks-loader')
            .html('<h1 class="text-center text-primary" style="font-size: 100px;"><i class="fas fa-spinner fa-pulse"></i></h1>');
    });   
JS;
$this->registerJs($editMarksScript, yii\web\View::POS_END);