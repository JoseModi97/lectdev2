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
<!-- contextual header (match Assign modal layout) -->
<div class="card" style="padding:10px; margin-bottom:10px; border: 1px solid #008cba;border-radius: 5px;">
    <div class="card-body">
        <div class="row"><div class="col-md-6"><p class="card-text"><span class="text-primary"> ACADEMIC YEAR: </span> <span class="lecturer-allocation-academic-year"></span></p></div><div class="col-md-6"><p class="card-text"><span class="text-primary"> DEGREE PROGRAMME: </span> <span class="lecturer-allocation-degree-name"></span></p></div></div>
        <div class="row"><div class="col-md-6"><p class="card-text"><span class="text-primary"> COURSE CODE: </span> <span class="lecturer-allocation-course-code"></span></p></div><div class="col-md-6"><p class="card-text"><span class="text-primary"> COURSE NAME: </span> <span class="lecturer-allocation-course-name"></span></p></div></div>
        <div class="row"><div class="col-md-6"><p class="card-text"><span class="text-primary"> LEVEL OF STUDY: </span> <span class="lecturer-allocation-level-of-study"></span></p></div><div class="col-md-6"><p class="card-text"><span class="text-primary"> SEMESTER: </span> <span class="lecturer-allocation-description-full"></span></p></div></div>
        <div class="row"><div class="col-md-6"><p class="card-text"><span class="text-primary"> GROUP: </span> <span class="lecturer-allocation-group"></span></p></div><div class="col-md-6"><p class="card-text"><span class="text-primary"> SEMESTER TYPE: </span> <span class="lecturer-allocation-semester-type"></span></p></div></div>
    </div>
</div>

<!-- code from: https://codepen.io/lehonti/pen/OzoXVa -->
<div class="lec-assign-form-fields form-border">
    <?php
    $form = ActiveForm::begin([
        'id' => 'remove-allocated-lecturers',
        'action' => Url::to(['/allocation/remove-allocated-lecturer']),
        'method' => 'POST',
        'enableAjaxValidation' => false,
        'options' => ['enctype' => 'multipart/form-data']
    ]);
    ?>
    <div class="form-group">
        <div id="remove-lecturer-loader"></div>
        <div class="allocated-lectures-highlight">Select lecturers to remove them from this course.</div>
        <br>
        <input type="hidden" name="remove-lecturer-marksheet-id" value="<?php echo $marksheetId ?>" id="remove-lecturer-marksheet-id">
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
                <input type="checkbox" name="<?php echo $lec->PAYROLL_NO ?>" class="remove-lecturer-checkbox" value="<?php echo $lec->PAYROLL_NO ?>" id="<?php echo $lec->PAYROLL_NO . '-checkbox' ?>" />
                <label class="list-group-item" for="<?php echo $lec->PAYROLL_NO . '-checkbox' ?>"><i class="fas fa-user"></i> <?php echo $name ?></label>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo Html::submitButton('submit', [
            'id' => 'remove-allocated-lecturers-btn',
            'class' => 'btn text-white my-2',
            'style' => "background-image: linear-gradient(#455492, #304186, #455492)"
        ]) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>


<?php
$removeLecturersCSS = <<< CSS
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
$this->registerCss($removeLecturersCSS);

/** PHP to JS variables */
$removeLecturerAction = Url::to(['/allocation/remove-allocated-lecturer']);
$courseDetailsAction = Url::to(['/allocation/course-details']);

$removeLecturersScript = <<< JS
    // Populate contextual header using marksheetId
    (function(){
        var marksheetId = $('#remove-lecturer-marksheet-id').val();
        if(marksheetId){
            $('#modal .shimmer-overlay').removeClass('d-none');
            $.ajax({
                type: 'POST',
                url: '$courseDetailsAction',
                data: { 'marksheetId': marksheetId },
                dataType: 'json'
            }).done(function(resp){
                if(resp && resp.status === 200){
                    $('.lecturer-allocation-marksheet-id').html(resp.data.marksheetId);
                    $('.lecturer-allocation-course-name').html(resp.data.courseName);
                    $('.lecturer-allocation-course-code').html(resp.data.courseCode);
                    $('.lecturer-allocation-academic-year').html(resp.data.academicYear || '');
                    $('.lecturer-allocation-level-of-study').html(resp.data.levelOfStudyName || '');
                    $('.lecturer-allocation-semester-desc').html(resp.data.semesterDescription || '');
                    $('.lecturer-allocation-group').html(resp.data.groupName || '');
                    $('.lecturer-allocation-semester-type').html(resp.data.semesterType || '');
                    $('.lecturer-allocation-degree-name').html(resp.data.degreeName || '');
                    var desc = (resp.data.semesterCode ? resp.data.semesterCode : '') + (resp.data.semesterDescription ? ' - ' + resp.data.semesterDescription : '');
                    $('.lecturer-allocation-description-full').html(desc);
                }
            }).always(function(){
                $('#modal .shimmer-overlay').addClass('d-none');
            });
        }
    })();

  if(typeof lecturerVals == 'undefined') {
        var lecturerVals = [];
    }else{
        lecturerVals = [];
    }

    $('#remove-lecturer-loader').html('');

    /** Track the lecturers for removal */
    $('input[type="checkbox"]').click(function(){
        if($(this).prop("checked") == true){
            lecturerVals.push($(this).val()); 
        }
        else if($(this).prop("checked") == false){
            let index = lecturerVals.indexOf($(this).val());
            if (index > -1) {
                lecturerVals.splice(index, 1);
            }
        }
    });
    /** Submit lecturers to remove from the course */
    $('#remove-allocated-lecturers-btn').click(function(e) {
    e.preventDefault();
    let _csrf = $('input[type=hidden][name=_csrf]').val();
    let marksheetId = $('input[type=hidden][name=remove-lecturer-marksheet-id]').val();
    let removeLecturerAction = '$removeLecturerAction';

    if (lecturerVals === undefined || lecturerVals.length == 0) {
        alert('No lecturer has been selected for removal');
    } else {
        let formData = {
            '_csrf': _csrf,
            'marksheetId': marksheetId,
            'lecturers': lecturerVals,
        };

        if (confirm('Are you sure you want to remove the selected lecturers from this course?')) {
            $('#remove-lecturer-loader').html('<h5 class="text-center text-primary" style="font-size: 100px;"><i class="fas fa-spinner fa-pulse"></i></h5>');
            $.ajax({
                type: 'POST',
                url: removeLecturerAction,
                data: formData,
                dataType: 'json',
                encode: true
            })
            .done(function(data) {
                // handle success
            })
            .fail(function(data) {
                // handle error
            });
        } else {
            alert('Operation was cancelled.');
        }
    }
});

JS;
$this->registerJs($removeLecturersScript, \yii\web\View::POS_READY);
