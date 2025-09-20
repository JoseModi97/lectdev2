<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc Shows current applied filters for courses with awaiting dean/hod approvals and courses whose marks have been
 * approved.
 */

/**
 * @var yii\web\View $this
 * @var app\models\MarksApprovalFilter $filter
 */

use app\models\DegreeProgramme;
use app\models\Group;
use app\models\LevelOfStudy;
?>

<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <i class="fa fa-filter" aria-hidden="true"></i> <b>Active filters</b>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <div class="row">
                            <div class="col-md-4"><span class="pull-left">Academic year:</span></div>
                            <div class="col-md-8"><span class="pull-left"><?= $filter->academicYear?></span></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4"><span class="pull-left">Degree:</span></div>
                            <div class="col-md-8">
                                <span class="pull-left">
                                    <?php
                                    $degree = DegreeProgramme::find()->select(['DEGREE_NAME'])
                                        ->where(['DEGREE_CODE' => $filter->degreeCode])->asArray()->one();
                                    echo $degree['DEGREE_NAME'] . ' (' . $filter->degreeCode . ')';
                                    ?>
                                </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4"><span class="pull-left">Level of study:</span></div>
                            <div class="col-md-8">
                                <span class="pull-left">
                                    <?php
                                    $levelName = '';
                                    if(!empty($filter->levelOfStudy)){
                                        $level = LevelOfStudy::find()->select(['NAME'])
                                            ->where(['LEVEL_OF_STUDY' => $filter->levelOfStudy])->asArray()->one();
                                        $levelName =  strtoupper($level['NAME']);
                                    }
                                    echo $levelName;
                                    ?>
                                </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4"><span class="pull-left">Group:</span></div>
                            <div class="col-md-8">
                                <span class="pull-left">
                                    <?php
                                    $groupName = '';
                                    if(!empty($filter->group)){
                                        $group = Group::find()->select(['GROUP_NAME'])->where(['GROUP_CODE' => $filter->group])
                                            ->asArray()->one();
                                        $groupName = strtoupper($group['GROUP_NAME']);
                                    }
                                    echo $groupName;
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

