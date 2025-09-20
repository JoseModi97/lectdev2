<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 07-09-2021 11:32:35 
 * @modify date 07-09-2021 11:32:35 
 * @desc System start page
 */

/**
 * @var $this yii\web\View
 * @var $facCode string
 * @var $deptCode string
 */

use yii\helpers\Url;
use app\components\SmisHelper;

if(!Yii::$app->user->isGuest){
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


