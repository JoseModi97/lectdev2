<?php

/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var app\models\CourseAllocationFilter $filter
 * @var string $title
 * @var array $academicYears
 * @var array $programmesFilter
 * @var array $groupsFilter
 * @var array $levelsFilter
 * @var array $semestersFilter
 * @var string $filtersFor
 */

use yii\helpers\Url;
use yii\web\View;
use app\models\Semester;

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;


?>

<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12">
        <div class="panel panel-success">
            <div class="panel-heading"><i class="fa fa-filter" aria-hidden="true"></i> <b>Filter courses</b></div>
            <div class="panel-body">
                <form id="course-filters-form" action="<?= Url::to(['/allocation/give']) ?>" class="form-horizontal">
                    <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">

                    <input type="hidden" name="CourseAllocationFilter[purpose]" value="<?= $filter->purpose ?>">

                    <div class="form-group has-feedback">
                        <label class="control-label col-sm-2 required-control-label" for="academic-year">Academic year</label>
                        <div class="col-sm-10">
                            <select id="academic-year" name="CourseAllocationFilter[academicYear]" class="form-control" required>
                                <option value="">Select</option>
                            </select>
                        </div>
                    </div>

                    <?php if ($filter->purpose === 'nonSuppCourses' || $filter->purpose === 'suppCourses'): ?>
                        <div class="form-group has-feedback">
                            <label class="control-label col-sm-2 required-control-label" for="programme">Programme</label>
                            <div class="col-sm-10">
                                <select id="programme" name="CourseAllocationFilter[degreeCode]" class="form-control" required>
                                    <option value="">Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group has-feedback">
                            <label class="control-label col-sm-2 required-control-label" for="level-of-study">Level of study</label>
                            <div class="col-sm-10">
                                <select id="level-of-study" name="CourseAllocationFilter[levelOfStudy]" class="form-control" required>
                                    <option value="">Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group has-feedback">
                            <label class="control-label col-sm-2 required-control-label" for="group-code">Group</label>
                            <div class="col-sm-10">
                                <select id="group" name="CourseAllocationFilter[group]" class="form-control" required>
                                    <option value="">Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group has-feedback">
                            <label class="control-label col-sm-2 required-control-label" for="semester">Semester</label>
                            <div class="col-sm-10">
                                <select id="semester" name="CourseAllocationFilter[semester]" class="form-control" required>
                                    <option value="">Select</option>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-group has-feedback">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-10">
                            <button type="submit" class="btn">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$getAcademicYearsUrl = Url::to(['/allocation/get-academic-years']);
$getProgrammesUrl = Url::to(['/allocation/get-programmes']);
$getLevelsOfStudyUrl = Url::to(['/allocation/get-levels-of-study']);
$getSemesterUrl = Url::to(['/allocation/get-semesters']);
$getGroupsUrl = Url::to(['/allocation/get-groups']);

$courseFiltersScript = <<< JS
var academicYearsUrl = '$getAcademicYearsUrl';
var programmesUrl = '$getProgrammesUrl';
var levelsUrl = '$getLevelsOfStudyUrl';
var semestersUrl = '$getSemesterUrl';
var groupsUrl = '$getGroupsUrl';
var academicYear = '';
var programmeCode = '';
var levelOfStudy = '';
var group = '';

// https://stackoverflow.com/questions/18754020/bootstrap-with-jquery-validation-plugin
$("#course-filters-form").validate({
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
    }
});

// Get academic years
axios.get(academicYearsUrl)
.then(function (response) {
    var academicYears = response.data.academicYears;
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
    if(academicYear !== '' && programmeCode !== ''){
        getLevelsOfStudy();
    }
});

// Read selected programme
$('#programme').on('change', function (e){
    programmeCode = $(this).val();
    if(academicYear !== '' && programmeCode !== ''){
        getLevelsOfStudy();
    }
});

// Read selected study level
$('#level-of-study').on('change', function (e){
    levelOfStudy = $(this).val();
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
    axios.get(levelsUrl, {
        params: {
            year: academicYear,
            degreeCode: programmeCode
        }
    })
    .then(response => {
        var levels = response.data.levels;
        levels.forEach(level => {
            $('#level-of-study').append($('<option>', {
                value: level.LEVEL_OF_STUDY,
                text: level.levelOfStudy.NAME.toUpperCase()
            }));
        })
    })
    .catch(error => console.error(error));
}

// Get students groups 
getGroups = function (){
    $('#group').find('option').not(':first').remove();
    axios.get(groupsUrl, {
        params: {
            year: academicYear,
            degreeCode: programmeCode,
            level: levelOfStudy
        }
    })
    .then(response => {
        var groups = response.data.groups;
        groups.forEach(group => {
            $('#group').append($('<option>', {
                value: group.GROUP_CODE,
                text: group.group.GROUP_NAME
            })); 
        });
    })
    .catch(error => console.error(error));
}

// Get semesters 
getSemesters = function (){
    $('#semester').find('option').not(':first').remove();
    axios.get(semestersUrl, {
        params: {
            year: academicYear,
            degreeCode: programmeCode,
            level: levelOfStudy,
            group: group
        }
    })
    .then(response => {
        var semesters = response.data.semesters;
        semesters.forEach(semester => {
            $('#semester').append($('<option>', {
                value: semester.SEMESTER_CODE,
                text: semester.SEMESTER_CODE + ' - ' + semester.semesterDescription.SEMESTER_DESC
            })); 
        })
    })
    .catch(error => console.error(error));
}

// Initialize Select2 on the dropdowns
$('#academic-year').select2();
$('#programme').select2();
$('#level-of-study').select2();
$('#group').select2();
$('#semester').select2();
JS;
$this->registerJs($courseFiltersScript, View::POS_READY);
