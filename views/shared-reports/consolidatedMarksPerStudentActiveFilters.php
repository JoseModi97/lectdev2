<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var app\models\StudentConsolidatedMarksFilter $filter
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
                                    $level = LevelOfStudy::find()->select(['NAME'])
                                        ->where(['LEVEL_OF_STUDY' => $filter->levelOfStudy])->asArray()->one();
                                    echo strtoupper($level['NAME']);
                                    ?>
                                </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4"><span class="pull-left">Group:</span></div>
                            <div class="col-md-8">
                                <span class="pull-left">
                                    <?php
                                    $group = Group::find()->select(['GROUP_NAME'])->where(['GROUP_CODE' => $filter->group])
                                        ->asArray()->one();
                                    echo strtoupper($group['GROUP_NAME']);
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

