<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 15-12-2020 20:50:22 
 * @modify date 22-11-2021 20:50:22
 * @desc UI for marks upload with Excel file
 */

/* @var yii\web\View $this */
/* @var app\models\MarksUpload $marksUploadModel */
/* @var string $marksheetId */
/* @var string $assessementId  */
/* @var string $assessmentName  */
/* @var string $modalId  */
/* @var string $formId  */
/* @var string $formAction */
/* @var string $purpose */
/* @var string $marksType */
/* @var string $weight */
/* @var string $maximumMarks */

use kartik\widgets\FileInput;
use yii\bootstrap5\Modal;
use yii\web\ServerErrorHttpException;
use yii\widgets\ActiveForm;

Modal::begin([
    'title' => '<b class="text-primary">Upload marks</b>',
    'id' => $modalId,
    'size' => 'modal-md',
    'options' => ['data-backdrop' => "static", 'data-keyboard' => "false"],
]);
?>

<div class="form-border">

    <?php
    $form = ActiveForm::begin([
        'id' => $formId,
        'action' => $formAction,
        'enableAjaxValidation' => false,
        'options' => ['enctype' => 'multipart/form-data']
    ]);
    ?>

    <div class="row">
        <div id="upload-marks-loader"></div>
        <input type="text" name="MARKS-TYPE" value="<?= $marksType; ?>" hidden readonly>
        <input type="text" name="MARKSHEET-ID" value="<?= $marksheetId; ?>" hidden readonly>
        <input type="text" name="ASSESSMENT-ID" value="<?= $assessementId; ?>" hidden readonly>
        <input type="text" name="ASSESSMENT-NAME" value="<?= $assessmentName; ?>" hidden readonly>
        <input type="text" name="WEIGHT" value="<?= $weight; ?>" hidden readonly>
        <input type="text" name="MAXIMUM-MARKS" value="<?= $maximumMarks; ?>" hidden readonly>
    </div>

    <?php
    try {
        echo $form->field($marksUploadModel, 'marksFile')->widget(FileInput::classname(), [
            'pluginOptions' => [
                'showCaption' => false,
                'showRemove' => false,
                'showUpload' => false,
                'showCancel' => false,
                'browseClass' => 'btn',
                'browseIcon' => '<i class="fas fa-file"></i> ',
                'browseLabel' =>  'Select file',
                'allowedFileExtensions' => ['xlsx'],
            ]
        ]);
    } catch (Exception $ex) {
        $message = 'Failed to create file input.';
        if (YII_ENV_DEV) {
            $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
        }
        throw new ServerErrorHttpException($message, 500);
    }
    ?>

    <button type="submit" id="upload-marks-btn" class="btn"><i class="fas fa-upload"></i> Upload</button>

    <?php ActiveForm::end(); ?>
</div>

<?php Modal::end(); ?>

<?php
$uploadMarkScript = <<< JS
    $('#upload-marks-btn').click(function(e){
        $('#upload-marks-loader')
            .html('<h1 class="text-center text-primary" ' +
             'style="font-size: 100px;"><i class="fas fa-spinner fa-pulse"></i></h1>');
    });   
JS;
$this->registerJs($uploadMarkScript, yii\web\View::POS_READY);
