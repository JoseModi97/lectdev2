<?php

/**
 * @var $this yii\web\View
 * @var $facCode string
 * @var $deptCode string
 */

use app\models\AcademicPeriod;



if (!Yii::$app->user->isGuest) {
    $this->title = Yii::$app->params['sitename'];
    $this->params['breadcrumbs'][] = $this->title;
}
?>

<div class="features text-left">
    <?php
    echo $this->render('_allFeatures', ['deptCode' => $deptCode]);
    // if(SmisHelper::hasOnlyLecturerRole()){
    //     echo $this->render('_lecturerFeaturesOnly',['deptCode' => $deptCode]); 
    // }elseif(SmisHelper::hasOnlyHodRole()){
    //     echo $this->render('_hodFeaturesOnly', ['deptCode' => $deptCode]); 
    // }elseif(SmisHelper::hasOnlyDeanRole()){
    //     echo $this->render('_deanFeaturesOnly', ['deptCode' => $deptCode]); 
    // }elseif(SmisHelper::hasOnlyFacAdminRole()){
    //     echo $this->render('_facAdminFeaturesOnly', ['deptCode' => $deptCode]); 
    // }elseif(SmisHelper::hasOnlyLecturerHodRoles()){
    //     echo $this->render('_lecturerHodFeaturesOnly',['deptCode' => $deptCode]); 
    // }elseif(SmisHelper::hasAllRoles()){
    //     echo $this->render('_allFeatures', ['deptCode' => $deptCode]); 
    // }
    ?>
</div>