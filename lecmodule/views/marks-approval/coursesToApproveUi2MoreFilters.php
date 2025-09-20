<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var app\models\MarksApprovalFilter $filter
 * @var string $filtersInterface
 */

use app\models\Semester;
use yii\db\ActiveQuery;
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
                    <input type="hidden" name="MarksApprovalFilter[approvalLevel]" value="<?=$filter->approvalLevel?>">
                    <div class="form-group">
                        <label for="level">Level</label>
                        <select name="MarksApprovalFilter[levelOfStudy]" class="form-control" id="level">
                            <option value="">-Select-</option>
                            <?php
                            $semesters = Semester::find()->alias('SM')->select(['SM.LEVEL_OF_STUDY'])->distinct()
                                ->where([
                                    'SM.ACADEMIC_YEAR' => $filter->academicYear,
                                    'SM.DEGREE_CODE' => $filter->degreeCode
                                ])
                                ->joinWith(['levelOfStudy LVL' => function(ActiveQuery $q){
                                    $q->select([
                                        'LVL.LEVEL_OF_STUDY',
                                        'LVL.NAME'
                                    ]);
                                }], true, 'INNER JOIN')
                                ->orderBy(['SM.LEVEL_OF_STUDY' => SORT_ASC])->asArray()->all();
                                foreach ($semesters as $semester):
                            ?>
                                <option value="<?=$semester['levelOfStudy']['LEVEL_OF_STUDY']?>">
                                    <?= $semester['levelOfStudy']['NAME']?>
                                </option>
                            <?php endforeach;?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="group">Group</label>
                        <select name="MarksApprovalFilter[group]" class="form-control" id="group">
                            <option value="">-Select-</option>
                            <?php
                            $semesters = Semester::find()->alias('SM')
                                ->select(['SM.GROUP_CODE'])
                                ->joinWith(['group GR' => function($q){
                                    $q->select([
                                        'GR.GROUP_CODE',
                                        'GR.GROUP_NAME'
                                    ]);
                                }], true, 'INNER JOIN')
                                ->where([
                                    'SM.ACADEMIC_YEAR' => $filter->academicYear,
                                    'SM.DEGREE_CODE' => $filter->degreeCode,
                                ])
                                ->distinct()
                                ->orderBy(['SM.GROUP_CODE' => SORT_ASC])
                                ->asArray()->all();

                            foreach ($semesters as $semester):
                                ?>
                                <option value="<?=$semester['group']['GROUP_CODE']?>">
                                    <?= $semester['group']['GROUP_NAME']?>
                                </option>
                            <?php endforeach;?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <select name="MarksApprovalFilter[semester]" class="form-control" id="semester">
                            <option value="">-Select-</option>
                            <?php
                            $semesters = Semester::find()
                                ->select(['SEMESTER_CODE', 'DESCRIPTION_CODE'])
                                ->where([
                                    'ACADEMIC_YEAR' => $filter->academicYear,
                                    'DEGREE_CODE' => $filter->degreeCode
                                ])
                                ->distinct()
                                ->orderBy(['SEMESTER_CODE' => SORT_ASC])
                                ->asArray()->all();

                            foreach ($semesters as $semester):
                                ?>
                                <option value="<?=$semester['SEMESTER_CODE']?>">
                                    <?=$semester['SEMESTER_CODE'] . ' (' . $semester['DESCRIPTION_CODE'] . ')'?>
                                </option>
                            <?php endforeach;?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="course-code">Course code</label>
                        <input type="text" name="MarksApprovalFilter[courseCode]" class="form-control" id="course-code"
                               value="<?=$filter->courseCode?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

