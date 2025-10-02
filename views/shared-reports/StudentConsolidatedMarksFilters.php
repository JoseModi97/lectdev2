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

<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div id="student-consolidated-marks-card" class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <div class="d-flex align-items-center">
                    <i class="fa fa-filter me-2" aria-hidden="true"></i>
                    <h2 class="h5 mb-0">Filter courses</h2>
                </div>
            </div>
            <div class="card-body">
                <?php $form = ActiveForm::begin([
                    'id' => 'course-analysis-filters-form',
                    'options' => ['class' => 'row g-3'],
                ]); ?>

                <?= $form->field($filter, 'approvalLevel')->hiddenInput(['value' => $filter->approvalLevel])->label(false) ?>

                <div class="col-12 col-md-6 form-group">
                    <label class="form-label fw-semibold" for="academic-year">Academic year <span class="text-danger">*</span></label>
                    <?= Select2::widget([
                        'name' => 'StudentConsolidatedMarksFilter[academicYear]',
                        'id' => 'academic-year',
                        'bsVersion' => '5.x',
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => 'Select'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'dropdownParent' => '#student-consolidated-marks-card',
                        ],
                    ]); ?>
                </div>

                <div class="col-12 col-md-6 form-group">
                    <label class="form-label fw-semibold" for="programme">Programme <span class="text-danger">*</span></label>
                    <?= Select2::widget([
                        'name' => 'StudentConsolidatedMarksFilter[degreeCode]',
                        'id' => 'programme',
                        'bsVersion' => '5.x',
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => 'Select'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'dropdownParent' => '#student-consolidated-marks-card',
                        ],
                    ]); ?>
                </div>

                <div class="col-12 col-md-6 form-group">
                    <label class="form-label fw-semibold" for="level-of-study">Level of study <span class="text-danger">*</span></label>
                    <?= Select2::widget([
                        'name' => 'StudentConsolidatedMarksFilter[levelOfStudy]',
                        'id' => 'level-of-study',
                        'bsVersion' => '5.x',
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => 'Select', 'disabled' => true],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'dropdownParent' => '#student-consolidated-marks-card',
                        ],
                    ]); ?>
                </div>

                <div class="col-12 col-md-6 form-group">
                    <label class="form-label fw-semibold" for="group">Group <span class="text-danger">*</span></label>
                    <?= Select2::widget([
                        'name' => 'StudentConsolidatedMarksFilter[group]',
                        'id' => 'group',
                        'bsVersion' => '5.x',
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => 'Select', 'disabled' => true],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'dropdownParent' => '#student-consolidated-marks-card',
                        ],
                    ]); ?>
                </div>

                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa fa-search me-2" aria-hidden="true"></i>Submit
                    </button>
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
    errorElement: "div",
    errorClass: "invalid-feedback d-block",
    highlight: function (element) {
        var $element = $(element);
        if ($element.hasClass('novalidation')) {
            return;
        }

        var $group = $element.closest('.form-group');
        $group.addClass('has-error');

        if ($element.hasClass('select2-hidden-accessible')) {
            $element.next('.select2-container').find('.select2-selection').addClass('is-invalid');
        } else {
            $element.addClass('is-invalid');
        }
    },
    unhighlight: function (element) {
        var $element = $(element);
        if ($element.hasClass('novalidation')) {
            return;
        }

        var $group = $element.closest('.form-group');
        $group.removeClass('has-error');

        if ($element.hasClass('select2-hidden-accessible')) {
            $element.next('.select2-container').find('.select2-selection').removeClass('is-invalid');
        } else {
            $element.removeClass('is-invalid');
        }
    },
    errorPlacement: function (error, element) {
        var $element = $(element);
        if ($element.parent('.input-group').length) {
            error.insertAfter($element.parent());
        }
        else if ($element.prop('type') === 'radio' && $element.parent('.radio-inline').length) {
            error.insertAfter($element.parent().parent());
        }
        else if ($element.prop('type') === 'checkbox' || $element.prop('type') === 'radio') {
            error.appendTo($element.parent().parent());
        }
        else if ($element.hasClass('select2-hidden-accessible')) {
            error.appendTo($element.closest('.form-group'));
        }
        else {
            error.insertAfter($element);
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
                $('#consolidated-marks-container').html('<div class="d-flex justify-content-center py-5"><div class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i><p class="mt-3 mb-0 text-muted">Loading consolidated marks…</p></div></div>');
            },
            success: function(response) {
                $('#consolidated-marks-container').html(response);
            },
            error: function() {
                $('#consolidated-marks-container').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
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
var dropdownParent = $('#student-consolidated-marks-card');

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
});

// Get levels of study 
getLevelsOfStudy = function (){
    var $levelSelect = $('#level-of-study');
    if ($levelSelect.data('select2')) {
        $levelSelect.select2('destroy');
    }

    $levelSelect.find('option').not(':first').remove();
    $levelSelect.prop('disabled', true).select2({
        placeholder: 'Loading...',
        dropdownParent: dropdownParent,
        allowClear: true
    });
    axios.get(levelsUrl, {
        params: {
            year: academicYear,
            degreeCode: programmeCode
        }
    })
    .then(response => {
        var levels = response.data.levels;
        $levelSelect.append($('<option>'));
        levels.forEach(level => {
            $levelSelect.append($('<option>', {
                value: level.LEVEL_OF_STUDY,
                text: level.levelOfStudy.NAME.toUpperCase()
            }));
        });
        if ($levelSelect.data('select2')) {
            $levelSelect.select2('destroy');
        }
        $levelSelect.prop('disabled', false).select2({
            placeholder: 'Select',
            dropdownParent: dropdownParent,
            allowClear: true
        });
    })
    .catch(error => {
        console.error(error);
        if ($levelSelect.data('select2')) {
            $levelSelect.select2('destroy');
        }
        $levelSelect.prop('disabled', false).select2({
            placeholder: 'Error loading data',
            dropdownParent: dropdownParent,
            allowClear: true
        });
    });
}

// Get students groups
getGroups = function (){
    var $groupSelect = $('#group');
    if ($groupSelect.data('select2')) {
        $groupSelect.select2('destroy');
    }

    $groupSelect.find('option').not(':first').remove();
    $groupSelect.prop('disabled', true).select2({
        placeholder: 'Loading...',
        dropdownParent: dropdownParent,
        allowClear: true
    });
    axios.get(groupsUrl, {
        params: {
            year: academicYear,
            degreeCode: programmeCode,
            level: levelOfStudy
        }
    })
    .then(response => {
        var groups = response.data.groups;
        $groupSelect.append($('<option>'));
        groups.forEach(group => {
            $groupSelect.append($('<option>', {
                value: group.GROUP_CODE,
                text: group.group.GROUP_NAME
            }));
        });
        if ($groupSelect.data('select2')) {
            $groupSelect.select2('destroy');
        }
        $groupSelect.prop('disabled', false).select2({
            placeholder: 'Select',
            dropdownParent: dropdownParent,
            allowClear: true
        });
    })
    .catch(error => {
        console.error(error);
        if ($groupSelect.data('select2')) {
            $groupSelect.select2('destroy');
        }
        $groupSelect.prop('disabled', false).select2({
            placeholder: 'Error loading data',
            dropdownParent: dropdownParent,
            allowClear: true
        });
    });
}
JS;
$this->registerJs($studentConsolidatedMarksScript, View::POS_READY);