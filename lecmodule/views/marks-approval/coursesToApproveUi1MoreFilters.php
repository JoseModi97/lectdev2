<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var app\models\MarksApprovalFilter $filter
 * @var string $filtersInterface
 */

use yii\helpers\Url;
?>

<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <i class="fa fa-filter" aria-hidden="true"></i> <b>More filters</b>
            </div>
            <div class="panel-body">
                <form action="<?=Url::to(['/marks-approval/courses-to-approve'])?>" class="form-inline">
                    <input type="hidden" name="_csrf" value="<?=Yii::$app->request->csrfToken?>">
                    <input type="hidden" name="filtersInterface" value="<?=$filtersInterface?>">
                    <input type="hidden" name="MarksApprovalFilter[academicYear]" value="<?=$filter->academicYear?>">
                    <input type="hidden" name="MarksApprovalFilter[degreeCode]" value="<?=$filter->degreeCode?>">
                    <input type="hidden" name="MarksApprovalFilter[group]" value="<?=$filter->group?>">
                    <input type="hidden" name="MarksApprovalFilter[levelOfStudy]" value="<?=$filter->levelOfStudy?>">
                    <input type="hidden" name="MarksApprovalFilter[semester]" value="<?=$filter->semester?>">
                    <input type="hidden" name="MarksApprovalFilter[approvalLevel]" value="<?=$filter->approvalLevel?>">
                    <div class="form-group">
                        <label for="course-code">Course code</label>
                        <input type="text" name="MarksApprovalFilter[courseCode]" class="form-control" id="course-code"
                               value="<?=$filter->courseCode?>">
                    </div>
                    <button type="submit" class="btn">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>


