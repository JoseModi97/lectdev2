<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Semester */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="semester-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'SEMESTER_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ACADEMIC_YEAR')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'DEGREE_CODE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'LEVEL_OF_STUDY')->textInput() ?>

    <?= $form->field($model, 'SEMESTER_CODE')->textInput() ?>

    <?= $form->field($model, 'INTAKE_CODE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'START_DATE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'END_DATE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'FIRST_SEMESTER')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'SEMESTER_NAME')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'CLOSING_DATE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ADMIN_USER')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'GROUP_CODE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'REGISTRATION_DEADLINE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'DESCRIPTION_CODE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'SESSION_TYPE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'DISPLAY_DATE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'REGISTRATION_DATE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'SEMESTER_TYPE')->textInput(['maxlength' => true]) ?>

  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
