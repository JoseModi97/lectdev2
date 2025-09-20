<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var app\models\CourseAllocationFilter $filter
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
                <form action="<?=Url::to(['/marks-approval/courses-with-marks'])?>" class="form-inline">
                    <input type="hidden" name="MarksApprovalFilter[academicYear]" value="<?=$filter->academicYear?>">
                    <input type="hidden" name="MarksApprovalFilter[semester]" value="<?=$filter->semester?>">
                    <input type="hidden" name="MarksApprovalFilter[degreeCode]" value="<?=$filter->degreeCode?>">
                    <input type="hidden" name="MarksApprovalFilter[approvalLevel]" value="<?=$filter->approvalLevel?>">

                    <div class="form-group">
                        <label for="course-code">Course code</label>
                        <input type="text" name="CourseAllocationFilter[courseCode]" class="form-control" id="course-code"
                               value="<?=$filter->courseCode?>">
                    </div>
                    <div class="form-group">
                        <label for="course-name">Course name</label>
                        <input type="text" name="MarksApprovalFilter[courseName]" class="form-control" id="course-name"
                               value="<?=$filter->courseName?>">
                    </div>
                    <div class="form-group">
                        <label for="course-name">Level of study</label>
                        <input type="text" name="MarksApprovalFilter[levelOfStudy]" class="form-control" id="level-of-study"
                               value="<?=$filter->levelOfStudy?>">
                    </div>
                    <div class="form-group">
                        <label for="course-name">Group</label>
                        <input type="text" name="MarksApprovalFilter[group]" class="form-control" id="group"
                               value="<?=$filter->group?>">
                    </div>
                    <button type="submit" class="btn">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
