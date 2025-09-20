<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 27-01-2021 14:39:11 
 * @modify date 27-01-2021 14:39:11 
 * @desc [description]
 */

/**
 * @var $this yii\web\View
 * @var $marksheetId string
 * @var $courseName string
 * @var $courseCode string
 * @var $level string
 * @var $degreeCode string
 */ 

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use app\models\CourseWorkAssessment;
use yii\db\ActiveQuery;

$query = CourseWorkAssessment::find()->alias('CW')
    ->select([
        'CW.ASSESSMENT_ID',
        'CW.WEIGHT',
        'CW.MARKSHEET_ID',
        'CW.RESULT_DUE_DATE',
        'CW.DIVIDER',
        'CW.ASSESSMENT_TYPE_ID'
    ])
    ->where(['CW.MARKSHEET_ID' => $marksheetId])
    ->joinWith(['assessmentType AT' => function(ActiveQuery $q){
            $q->select([
                'AT.ASSESSMENT_TYPE_ID',
                'AT.ASSESSMENT_NAME',
                'AT.ASSESSMENT_DESCRIPTION'
            ]);
        }
    ], true, 'LEFT JOIN')
    ->orderBy(['CW.ASSESSMENT_ID' => SORT_ASC]);

$dataProvider = new ActiveDataProvider([
    'query' => $query,
    'sort' => false,
    'pagination' => false
]);

$gridColumns = [
    [
        'attribute' => 'assessmentType.ASSESSMENT_NAME',
        'label' => 'ASSESSMENT NAME',
        'value' => function($model){
            $assessmentName = $model->assessmentType->ASSESSMENT_NAME;
            if(strpos($assessmentName, 'EXAM_COMPONENT') !== false){
                return str_replace('EXAM_COMPONENT','', $assessmentName );
            }else{
                return $assessmentName;
            } 
        }
    ],
    [
        'attribute' => 'WEIGHT', 
        'label' => 'WEIGHT'
    ],
    [
        'attribute' => 'DIVIDER',
        'label' => 'MARKED OUT OF'
    ],
    [
        'label' => 'RESULT DUE DATE',
        'value' => function($model){
            $duedate = $model->RESULT_DUE_DATE;
            return is_null( $duedate) ? 'NOT SET' : $duedate;
        }
    ],
    [
        'class' => 'kartik\grid\ActionColumn', 
        'header' => 'ACTIONS',
        'template' => '{view-marks}',
        'contentOptions' => ['style'=>'white-space:nowrap;','class'=>'kartik-sheet-style kv-align-middle'],
        'buttons' => [
            'view-marks' => function($url, $model) use ($level, $degreeCode){
                $assessmentId = $model->ASSESSMENT_ID;

                $assessmentName = $model->assessmentType->ASSESSMENT_NAME;
                if(strpos($assessmentName, 'EXAM_COMPONENT') !== false){
                    $isExamComponent = true;
                }else{
                    $isExamComponent = false;
                }  

                if($assessmentName !== 'EXAM' && !$isExamComponent){
                    /**
                     * For assignements and CATs, if marks have been approved at the Lecturer level, 
                     * the higher levels can only view
                     */
                    $statusToCheck = 'LECTURER_APPROVAL_STATUS';
                    $cwMark = \app\models\StudentCoursework::find()
                        ->where(['ASSESSMENT_ID' => $assessmentId,  $statusToCheck => 'APPROVED'])
                        ->one();
                }else{
                    /**
                     * Deans look for exam marks approved at the HOD level
                     * HODs look for exam marks approved at the lecturer level
                     */
                    if($level == 'dean'){
                        $statusToCheck = 'HOD_APPROVAL_STATUS';
                    }
                    elseif($level == 'hod'){
                        $statusToCheck = 'LECTURER_APPROVAL_STATUS';
                    }else{
                        throw new Exception('You must give the correct approval level.'); 
                    }
                    $cwMark = \app\models\StudentCoursework::find()
                        ->where(['ASSESSMENT_ID' => $assessmentId, $statusToCheck => 'APPROVED'])
                        ->one();
                }

                // Show links if there is atleast one entry of approved marks at a certain level
                if(!is_null($cwMark)){
                    return Html::a('<i class="fas fa-eye"></i> View uploaded marks', Url::to([
                            '/marks-approval', 'id' => $assessmentId, 'level' => $level, 'degreeCode' => $degreeCode
                        ]), [
                        'title' => 'View uploaded marks',
                        'class' => 'btn btn-xs btn-spacer' 
                    ]);
                }
                else{
                    return 'No marks to view';
                }
            }
        ]
    ]
];
?>

<div class="course-assessments">
    <div class="course-assessments-info">
        <div class="card" style="padding:10px; margin-bottom:10px; border: 1px solid #008cba;border-radius: 5px;">  
            <div class="card-body">
                <h5><strong>Assesment and Exam details for marksheet ID: <?=$marksheetId;?></strong></h5>
                <div class="row">
                    <div class="col-md-8 col-lg-8">
                        <p class="card-text">
                            <span class="text-primary"> COURSE NAME: </span> 
                            <span id="course-name"> <?=$courseName;?> </span>
                        </p>
                    </div>
                    <div class="col-md-4 col-lg-4">
                        <p class="card-text text-right">
                            <span class="text-primary"> COURSE CODE: </span> 
                            <span id="course-code"> <?=$courseCode;?> </span>
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?= GridView::widget([
                                'dataProvider' => $dataProvider,
                                'summary' => '<div class="ficha clearfix">
                                    <span style="margin-bottom:2px;" class="pull-right btn btn-xs">Total <strong>{totalCount, number}</strong> {totalCount, plural, one{item} other{items}}.</span>
                                    </div>',
                                'summaryOptions' => ['class' => 'text-right'],
                                'columns' => $gridColumns,
                            ]); 
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>