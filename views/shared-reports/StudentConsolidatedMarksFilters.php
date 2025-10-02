<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var app\models\StudentConsolidatedMarksFilter $filter
 * @var string $title
 */

use kartik\select2\Select2;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12">
        <div class="panel panel-primary">
            <div class="panel-heading"><i class="fa fa-filter" aria-hidden="true"></i> <b>Filter courses</b></div>
            <div class="panel-body">
                <?php $form = ActiveForm::begin([
                    'id' => 'course-analysis-filters-form',
                    'options' => ['class' => 'form-horizontal']
                ]); ?>

                <?= $form->field($filter, 'approvalLevel')->hiddenInput(['value' => $filter->approvalLevel])->label(false) ?>

                <div class="form-group has-feedback">
                    <label class="control-label col-sm-2 required-control-label" for="academic-year">Academic year</label>
                    <div class="col-sm-10">
                        <?= Select2::widget([
                            'name' => 'StudentConsolidatedMarksFilter[academicYear]',
                            'id' => 'academic-year',
                            'options' => ['placeholder' => 'Select'],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]); ?>
                    </div>
                </div>

                <div class="form-group has-feedback">
                    <label class="control-label col-sm-2 required-control-label" for="programme">Programme</label>
                    <div class="col-sm-10">
                        <?= Select2::widget([
                            'name' => 'StudentConsolidatedMarksFilter[degreeCode]',
                            'id' => 'programme',
                            'options' => ['placeholder' => 'Select'],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]); ?>
                    </div>
                </div>

                <div class="form-group has-feedback">
                    <label class="control-label col-sm-2 required-control-label" for="level-of-study">Level of study</label>
                    <div class="col-sm-10">
                        <?= Select2::widget([
                            'name' => 'StudentConsolidatedMarksFilter[levelOfStudy]',
                            'id' => 'level-of-study',
                            'options' => ['placeholder' => 'Select', 'disabled' => true],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]); ?>
                    </div>
                </div>

                <div class="form-group has-feedback">
                    <label class="control-label col-sm-2 required-control-label" for="group">Group</label>
                    <div class="col-sm-10">
                        <?= Select2::widget([
                            'name' => 'StudentConsolidatedMarksFilter[group]',
                            'id' => 'group',
                            'options' => ['placeholder' => 'Select', 'disabled' => true],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]); ?>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<div id="consolidated-marks-container"></div>

<?php
$getAcademicYearsUrl = Url::to(['/shared-reports/get-academic-years']);
$getProgrammesUrl = Url::to(['/shared-reports/get-programmes']);
$getLevelsOfStudyUrl = Url::to(['/shared-reports/get-levels-of-study']);
$getGroupsUrl = Url::to(['/shared-reports/get-groups']);
$consolidatedMarksUrl = Url::to(['/shared-reports/consolidated-marks-per-student']);

$studentConsolidatedMarksScript = <<< JS
var academicYearsUrl = '$getAcademicYearsUrl';
var programmesUrl = '$getProgrammesUrl';
var levelsUrl = '$getLevelsOfStudyUrl';
var groupsUrl = '$getGroupsUrl';
var academicYear = '';
var programmeCode = '';
var levelOfStudy = '';
var group = '';

// https://stackoverflow.com/questions/18754020/bootstrap-with-jquery-validation-plugin
$("#course-analysis-filters-form").validate({
    errorElement: "span",
    errorClass: "help-block",
    highlight: function (element, errorClass, validClass) {
        // Only validation controls
        if (!$(element).hasClass('novalidation')) {
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
        }
    },
    unhighlight: function (element, errorClass, validClass) {
        // Only validation controls
        if (!$(element).hasClass('novalidation')) {
            $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
        }
    },
    errorPlacement: function (error, element) {
        if (element.parent('.input-group').length) {
            error.insertAfter(element.parent());
        }
        else if (element.prop('type') === 'radio' && element.parent('.radio-inline').length) {
            error.insertAfter(element.parent().parent());
        }
        else if (element.prop('type') === 'checkbox' || element.prop('type') === 'radio') {
            error.appendTo(element.parent().parent());
        }
        else {
            error.insertAfter(element);
        }
    },
    submitHandler: function(form) {
        var url = '$consolidatedMarksUrl';
        var data = $(form).serialize();

        $.ajax({
            url: url,
            type: 'get',
            data: data,
            beforeSend: function() {
                $('#consolidated-marks-container').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i></div>');
            },
            success: function(response) {
                $('#consolidated-marks-container').html(response);
            },
            error: function() {
                $('#consolidated-marks-container').html('<div class="alert alert-danger">An error occurred.</div>');
            }
        });
        return false;
    }
});

// Get academic years
axios.get(academicYearsUrl)
.then(function (response) {
    var academicYears = response.data.academicYears;
    $('#academic-year').append($('<option>'));
    Object.keys(academicYears).forEach(function(key) {
        $('#academic-year').append($('<option>', { 
            value: key,
            text : academicYears[key]
        }));
    });
})
.catch(error => console.error(error));

// Get programmes
axios.get(programmesUrl)
.then(function (response){
    var programmes = response.data.programmes;
    $('#programme').append($('<option>'));
    programmes.forEach(programme => {
        $('#programme').append($('<option>', {
            value: programme.DEGREE_CODE,
            text: programme.DEGREE_CODE + ' - ' + programme.DEGREE_NAME
        }));
    });
})
.catch(error => console.error(error));

// Read seleceted academic year
$('#academic-year').on('change', function(e) {
    academicYear = $(this).val();
    $('#level-of-study').val(null).trigger('change').prop('disabled', true);
    $('#group').val(null).trigger('change').prop('disabled', true);
    if(academicYear !== '' && programmeCode !== ''){
        getLevelsOfStudy();
    }
});

// Read selected programme
$('#programme').on('change', function (e){
    programmeCode = $(this).val();
    $('#level-of-study').val(null).trigger('change').prop('disabled', true);
    $('#group').val(null).trigger('change').prop('disabled', true);
    if(academicYear !== '' && programmeCode !== ''){
        getLevelsOfStudy();
    }
});

// Read selected study level
$('#level-of-study').on('change', function (e){
    levelOfStudy = $(this).val();
    $('#group').val(null).trigger('change').prop('disabled', true);
     if(academicYear !== '' && programmeCode !== '' && levelOfStudy !== ''){
        getGroups();
    }
});

// Read selected group
$('#group').on('change', function (e){
    group = $(this).val();
     if(academicYear !== '' && programmeCode !== '' && levelOfStudy !== '' && group !== ''){
        getSemesters();
    }
});

// Get levels of study 
getLevelsOfStudy = function (){
    $('#level-of-study').find('option').not(':first').remove();
    $('#level-of-study').prop('disabled', true).select2({placeholder: 'Loading...'});
    axios.get(levelsUrl, {
        params: {
            year: academicYear,
            degreeCode: programmeCode
        }
    })
    .then(response => {
        var levels = response.data.levels;
        $('#level-of-study').append($('<option>'));
        levels.forEach(level => {
            $('#level-of-study').append($('<option>', {
                value: level.LEVEL_OF_STUDY,
                text: level.levelOfStudy.NAME.toUpperCase()
            }));
        });
        $('#level-of-study').prop('disabled', false).select2({placeholder: 'Select'});
    })
    .catch(error => {
        console.error(error);
        $('#level-of-study').prop('disabled', false).select2({placeholder: 'Error loading data'});
    });
}

// Get students groups 
getGroups = function (){
    $('#group').find('option').not(':first').remove();
    $('#group').prop('disabled', true).select2({placeholder: 'Loading...'});
    axios.get(groupsUrl, {
        params: {
            year: academicYear,
            degreeCode: programmeCode,
            level: levelOfStudy
        }
    })
    .then(response => {
        var groups = response.data.groups;
        $('#group').append($('<option>'));
        groups.forEach(group => {
            $('#group').append($('<option>', {
                value: group.GROUP_CODE,
                text: group.group.GROUP_NAME
            })); 
        });
        $('#group').prop('disabled', false).select2({placeholder: 'Select'});
    })
    .catch(error => {
        console.error(error);
        $('#group').prop('disabled', false).select2({placeholder: 'Error loading data'});
    });
}
JS;
$this->registerJs($studentConsolidatedMarksScript, View::POS_READY);