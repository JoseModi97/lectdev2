<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 13-01-2021 11:22:48 
 * @modify date 13-01-2021 11:22:48 
 * @desc [description]
 */

/* @var $this yii\web\View */
/* @var $allocationReqModel app\models\AllocationRequest */ 
/* @var $title string */
/* @var $deptCode string */

use app\models\EmpVerifyView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = $title;
?>

<div class="course-request">
    <div class="card form-border">
        <div class="card-body">
            <p class="card-text"><span class="text-primary"> REQUEST MADE BY: </span> 
                <?php
                    $lecturer = EmpVerifyView::find()
                       ->select(['PAYROLL_NO', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE'])
                       ->where(['PAYROLL_NO' => $allocationReqModel->REQUEST_BY])
                       ->one();
                   $name =  $lecturer->EMP_TITLE.' '.$lecturer->SURNAME.' '.$lecturer->OTHER_NAMES;
                   echo $name;
                ?> 
            </p>
            <p class="card-text"><span class="text-primary"> REQUEST MADE ON: </span> <?= $allocationReqModel->REQUEST_DATE; ?> </p>
            <p class="card-text"><span class="text-primary"> REQUEST ATTENDED BY: </span>
                <?php
                    if(is_null($allocationReqModel->ATTENDED_BY)) echo 'NOT ATTENDED TO';
                    else {
                        $lecturer = EmpVerifyView::find()
                            ->select(['PAYROLL_NO', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE'])
                            ->where(['PAYROLL_NO' => $allocationReqModel->ATTENDED_BY])
                            ->one();
                        $name =  $lecturer->EMP_TITLE.' '.$lecturer->SURNAME.' '.$lecturer->OTHER_NAMES;
                        echo $name;
                    }
                ?> 
            </p>
            <p class="card-text "><span class="text-primary"> REQUEST ATTENDED ON: </span> 
                <?php
                    if(is_null($allocationReqModel->ATTENDED_DATE)) echo 'NOT ATTENDED TO';
                    else echo $allocationReqModel->ATTENDED_DATE;
                ?> 
            </p>
            <p class="card-text"><span class="text-primary">REMARKS: </span> 
                <?php 
                    if(is_null($allocationReqModel->REMARKS)) echo 'NO REMARKS';
                    else echo $allocationReqModel->REMARKS;
                ?>
            </p>
        </div>
    </div>
</div>