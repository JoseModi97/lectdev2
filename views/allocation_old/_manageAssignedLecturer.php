<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 13-01-2021 11:22:48 
 * @modify date 13-01-2021 11:22:48 
 * @desc [description]
 */

/* @var $this yii\web\View */
/* @var $lecturers app\models\CourseAssignment */
/* @var $title string */
/* @var $marksheetId string */

use app\models\EmpVerifyView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = $title;
?>
<!-- code from: https://codepen.io/lehonti/pen/OzoXVa -->
<div class="lec-assign-form-fields form-border">
    <?php
    $form = ActiveForm::begin([
        'id' => 'manage-allocated-lecturers',
        'action' => Url::to(['/allocation/manage-allocated-lecturer']),
        'method' => 'POST',
        'enableAjaxValidation' => false,
        'options' => ['enctype' => 'multipart/form-data']
    ]);
    ?>
    <div class="form-group">
        <div id="manage-lecturer-loader"></div>
        <div class="allocated-lectures-highlight">Select one lecturer to make them the course lead.</div>
        <br>
        <input type="hidden" name="manage-lecturer-marksheet-id" value="<?= $marksheetId; ?>" id="manage-lecturer-marksheet-id">
        <div class="list-group">
            <?php
            foreach ($lecturers as $lec):
                $lecturer = EmpVerifyView::find()->select(['PAYROLL_NO', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE'])
                    ->where(['PAYROLL_NO' => $lec->PAYROLL_NO])->one();

                if (is_null($lecturer)) {
                    continue;
                }

                $name =  $lecturer->EMP_TITLE . ' ' . $lecturer->SURNAME . ' ' . $lecturer->OTHER_NAMES;
            ?>
                <input type="radio" name="lecturer" value="<?= $lec->PAYROLL_NO; ?>" id="<?= $lec->PAYROLL_NO . '-radio'; ?>" />
                <label class="list-group-item" for="<?= $lec->PAYROLL_NO . '-radio'; ?>"><i class="fas fa-user"></i> <?= $name; ?></label>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton('submit', [
            'id' => 'manage-allocated-lecturers-btn',
            'class' => 'btn text-white my-2',
            'style' => "background-image: linear-gradient(#455492, #304186, #455492)"
        ]); ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>


<?php
$manageLecturersCSS = <<< CSS
    .allocated-lectures-highlight {
        background: #008cba;
        color:  #FFF;
        padding: 10px;
        margin-bottom: 10px;
    }
    .list-group-item {
        user-select: none;
        padding: 10px;
    }

    .list-group input[type="checkbox"] {
        display: none;
    }

    .list-group input[type="checkbox"] + .list-group-item {
        cursor: pointer;
    }

    .list-group input[type="checkbox"] + .list-group-item:before {
        color: transparent;
        font-weight: bold;
        margin-right: 1em;
    }

    .list-group input[type="checkbox"]:checked + .list-group-item {
        background-color: #008cba;
        color: #FFF;
    }

    .list-group input[type="checkbox"]:checked + .list-group-item:before {
        color: inherit;
    }

    .list-group input[type="radio"] {
        display: none;
    }

    .list-group input[type="radio"] + .list-group-item {
        cursor: pointer;
    }

    .list-group input[type="radio"] + .list-group-item:before {
        color: transparent;
        font-weight: bold;
        margin-right: 1em;
    }

    .list-group input[type="radio"]:checked + .list-group-item {
        background-color: #008cba;
        color: #FFF;
    }

    .list-group input[type="radio"]:checked + .list-group-item:before {
        color: inherit;
    }
CSS;
$this->registerCss($manageLecturersCSS);

/** PHP to JS variables */
$manageLecturerAction = Url::to(['/allocation/manage-allocated-lecturer']);

$manageLecturersScript = <<< JS
    $('#manage-lecturer-loader').html('');

    /** Submit course leader data */
    $('#manage-allocated-lecturers-btn').click(function(e){
        e.preventDefault();
        let _csrf = $('input[type=hidden][name=_csrf]').val();
        let marksheetId = $('input[type=hidden][name=manage-lecturer-marksheet-id]').val();
        let lecturer = $("input[type=radio][name='lecturer']:checked").val();
        let manageLecturerAction = '$manageLecturerAction';

        if (typeof lecturer === 'undefined')
            alert('Provide a lecturer to be a courseleader.');
        else{
            let formData = {
                '_csrf'             : _csrf,
                'marksheetId'       : marksheetId,
                'lecturer'         : lecturer,
            };
            if(confirm('Are you sure you want the selected lecturer as the course leader?')){
                $('#manage-lecturer-loader').html('<h5 class="text-center text-primary" style="font-size: 100px;"><i class="fas fa-spinner fa-pulse"></i></h5>');
                $.ajax({
                    type        :   'POST',
                    url         :   manageLecturerAction,
                    data        :   formData,
                    dataType    :   'json',
                    encode      :   true             
                })
                .done(function(data){})
                .fail(function(data){});
            }else{
                alert('Operation was cancelled.');
            }
        }
    });
JS;
$this->registerJs($manageLecturersScript, \yii\web\View::POS_READY);
