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
                <form action="<?=Url::to(['/allocation/give'])?>" class="form-inline">
                    <input type="hidden" name="_csrf" value="<?=Yii::$app->request->csrfToken?>">
                    <input type="hidden" name="CourseAllocationFilter[academicYear]" value="<?=$filter->academicYear?>">
                    <input type="hidden" name="CourseAllocationFilter[degreeCode]" value="<?=$filter->degreeCode?>">
                    <input type="hidden" name="CourseAllocationFilter[group]" value="<?=$filter->group?>">
                    <input type="hidden" name="CourseAllocationFilter[levelOfStudy]" value="<?=$filter->levelOfStudy?>">
                    <input type="hidden" name="CourseAllocationFilter[semester]" value="<?=$filter->semester?>">
                    <input type="hidden" name="CourseAllocationFilter[purpose]" value="<?=$filter->purpose?>">
                    <div class="form-group">
                        <label for="course-code">Course code</label>
                        <input type="text" name="CourseAllocationFilter[courseCode]" class="form-control" id="course-code"
                               value="<?=$filter->courseCode?>">
                    </div>
                    <div class="form-group">
                        <label for="course-name">Course name</label>
                        <input type="text" name="CourseAllocationFilter[courseName]" class="form-control" id="course-name"
                               value="<?=$filter->courseName?>">
                    </div>
                    <button type="submit" class="btn">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
