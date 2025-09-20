<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 05-07-2021 11:15:56 
 * @modify date 05-07-2021 11:15:56 
 * @desc [description]
 */

/* @var $this yii\web\View */
/* @var $model app\models\Marksheet */
/* @var $searchModel app\models\search\MarksheetSearch */
/* @var $studentMarksheetProvider yii\data\ActiveDataProvider */
/* @var $marksheetId string */
/* @var $courseCode string */
/* @var $courseName string */
/* @var $date string */
/* @var $deptCode string */
/* @var $academicYear string */

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use kartik\export\ExportMenu;

use app\models\StudentCoursework;
use app\models\CourseWorkAssessment;
use app\models\UonStudent;
use app\components\GridExport;

$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => 'MY COURSE ALLOCATIONS', 'url' => ['/allocated-courses']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="examlist-index">
    <?php
        $title = $courseName.' ('.$courseCode.') EXAM REGISTER';
        $fileName = $courseCode.'_exam_register';
        $centerContent = 'EXAM REGISTER';

        $contentBefore = '<p style="color:#333333; font-weight: bold;">Academic year: ' . $academicYear . '</p>';
        $contentBefore .= '<p style="color:#333333; font-weight: bold;">Course: '.$courseName.' ('.$courseCode.')</p>';
        $contentBefore .= '<p style="color:#333333; font-weight: bold;">Department: '.$deptName.'</p>';
        $contentBefore .= '<p style="color:#333333; font-weight: bold;">Date: ........................ Time: ........................</p>';

        $contentAfter = '<p style="color:#333333; font-weight: bold;">Room: ........................</p>';
        $contentAfter .= '<p style="color:#333333; font-weight: bold;">Chief invigilator: ............................................... Sign: ........................ Date ........................</p>';
        $contentAfter .= '<p style="color:#333333; font-weight: bold;">No. of candidates: ............ ';
        $contentAfter .= 'No. of scripts returned: ............ ';
        $contentAfter .= 'No. of invigilators: ............</p>';

        $gridColumns = [
            [
                'class' => 'kartik\grid\SerialColumn',
                'width' => '5%'
            ],  
            [
                'attribute' => 'REGISTRATION_NUMBER',
                'label' => 'NUMBER',
                'width' => '15%',
            ],
            [
                'attribute' => 'student.SURNAME',
                'label' => 'SURNAME',
                'width' => '15%',
                'value' => function($model){
                    return ucwords($model['student']['SURNAME']);
                }
            ],
            [
                'attribute' => 'student.OTHER_NAMES',
                'label' => 'OTHER NAMES',
                'width' => '25%',
                'value' => function($model){
                    return ucwords($model['student']['OTHER_NAMES']);
                }
            ],
            [
                'label' => 'EMAIL',
                'width' => '20%',
                'value' => function($model){
                    return UonStudent::validateEmail($model['student']['EMAIL']);
                }
            ],
            [
                'label' => 'TELEPHONE',
                'width' => '10%',
                'value' => function($model){
                    return UonStudent::formatTelephone($model['student']['TELEPHONE']);
                }
            ],
            [
                'label' => 'SIGN',
                'width' => '10%',
                'value' => function($model){
                    return '';
                }
            ],   
        ];
    
        echo GridView::widget([
            'id' => 'classListGridView',
            'dataProvider' => $studentMarksheetProvider,
            'filterModel' => $searchModel,
            'columns' => $gridColumns,
            'headerRowOptions' => ['class' => 'kartik-sheet-style'],
            'filterRowOptions' => ['class' => 'kartik-sheet-style'],
            'pjax' => true, 
            'toolbar' =>  [
                '{export}',
                '{toggleData}',
            ],
            'toggleDataContainer' => ['class' => 'btn-group mr-2'],
            'export' => [
                'fontAwesome' => false,
            ],
            'panel' => [
                'type' => GridView::TYPE_PRIMARY,
                'heading'=>'<h3 class="panel-title">'.$title,
            ],
            'persistResize' => false,
            'toggleDataOptions' => ['minCount' => 20],
            'exportConfig' => [
                GridView::PDF => GridExport::exportPdf([
                    'filename' => $fileName, 
                    'title' => $title,
                    'subject' => 'exam list',
                    'keywords' => 'exam list',
                    'contentBefore'=> $contentBefore,
                    'contentAfter'=> $contentAfter,
                    'centerContent' => $centerContent 
                ])
            ],
            'itemLabelSingle' => 'student',
            'itemLabelPlural' => 'students',
        ]);
    ?>  
</div>

