<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc Select filters for the marks' approval original process.
 */

/**
 * @var yii\web\View $this
 * @var app\models\MarksApprovalFilter $filter
 * @var string $title
 */

use yii\helpers\Url;
use yii\web\View;

$this->title = $title;
$this->params['breadcrumbs'][] = [
    'label' => 'Programmes in the faculty',
    'url' => ['/marks-approval/programmes-in-faculty', 'level' => $filter->approvalLevel,]
];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12">
        <div class="panel panel-primary">
            <div class="panel-heading"><i class="fa fa-filter" aria-hidden="true"></i> <b>Filter courses</b></div>
            <div class="panel-body">
                <form id="course-filters-form" action="<?=Url::to(['/marks-approval/courses-with-marks'])?>" class="form-horizontal">
                    <input type="hidden" name="_csrf" value="<?=Yii::$app->request->csrfToken?>">

                    <input type="hidden" id="approval-level" name="MarksApprovalFilter[approvalLevel]" value="<?=$filter->approvalLevel?>">

                    <input type="hidden" id="programme-code" name="MarksApprovalFilter[degreeCode]" value="<?=$filter->degreeCode?>">

                    <div class="form-group has-feedback">
                        <label class="control-label col-sm-2 required-control-label" for="academic-year">Academic year</label>
                        <div class="col-sm-10">
                            <select id="academic-year" name="MarksApprovalFilter[academicYear]" class="form-control" required>
                                <option value="">Select</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group has-feedback">
                        <label class="control-label col-sm-2 required-control-label" for="semester">Semester</label>
                        <div class="col-sm-10">
                            <select id="semester" name="MarksApprovalFilter[semester]" class="form-control" required>
                                <option value="">Select</option>
                            </select>
                        </div>
                    </div>

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
$getAcademicYearsUrl = Url::to(['/marks-approval/get-academic-years']);
$getSemesterUrl = Url::to(['/marks-approval/get-semesters']);

$courseFiltersScript = <<< JS
var academicYearsUrl = '$getAcademicYearsUrl';
var semestersUrl = '$getSemesterUrl';
var academicYear = '';
var programmeCode = $('#programme-code').val();
var approvalLevel = $('#approval-level').val();
 
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

// Read seleceted academic year
$('#academic-year').on('change', function(e) {
    academicYear = $(this).val();
    if(academicYear !== ''){
        getSemesters();
    }
});

// Get semesters 
getSemesters = function (){
    $('#semester').find('option').not(':first').remove();
    axios.get(semestersUrl, {
        params: {
            year: academicYear,
            degreeCode: programmeCode,
            approvalLevel: approvalLevel
        }
    })
    .then(response => {
        var semesters = response.data.semesters;
        console.log(semesters);
        semesters.forEach(semester => {
            $('#semester').append($('<option>', {
                value: semester.SEMESTER_CODE,
                text: semester.SEMESTER_CODE + ' - ' + semester.semesterDescription.SEMESTER_DESC
            })); 
        })
    })
    .catch(error => console.error(error));
}

JS;
$this->registerJs($courseFiltersScript, View::POS_READY);



