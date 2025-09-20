<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc Select filters for the marks' approval process with pending/approved tabs. In this new the HOD don't edit.
 */

/**
 * @var yii\web\View $this
 * @var app\models\MarksApprovalFilter $filter
 * @var string $title
 * @var string $filtersInterface
 * @var string $resultsType
 */

use yii\helpers\Url;
use yii\web\View;

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12">
            <div class="panel panel-primary">
                <div class="panel-heading"><i class="fa fa-filter" aria-hidden="true"></i> <b>Filter courses</b></div>
                <div class="panel-body">
                    <form id="course-filters-form" class="form-horizontal jquery-validated-form"
                          action="<?=Url::to(['/marks-approval/courses-to-approve'])?>">

                        <input type="hidden" name="_csrf" value="<?=Yii::$app->request->csrfToken?>">

                        <input type="hidden" name="filtersInterface" value="<?=$filtersInterface?>">

                        <input type="hidden" name="resultsType" value="<?=$resultsType?>">

                        <input type="hidden" id="approval-level" name="MarksApprovalFilter[approvalLevel]" value="<?=$filter->approvalLevel?>">

                        <div class="form-group has-feedback">
                            <label class="control-label col-sm-2 required-control-label" for="academic-year">Academic year</label>
                            <div class="col-sm-10">
                                <select id="academic-year" name="MarksApprovalFilter[academicYear]" class="form-control" required>
                                    <option value="">Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group has-feedback">
                            <label class="control-label col-sm-2 required-control-label" for="programme">Programme</label>
                            <div class="col-sm-10">
                                <select id="programme" name="MarksApprovalFilter[degreeCode]" class="form-control" required>
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
$getAcademicYearsUrl = Url::to(['/shared-reports/get-academic-years']);
$getProgrammesUrl = Url::to(['/shared-reports/get-programmes']);

$interfaceTwoScript = <<< JS
var academicYearsUrl = '$getAcademicYearsUrl';
var programmesUrl = '$getProgrammesUrl';

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

JS;
$this->registerJs($interfaceTwoScript, View::POS_READY);
