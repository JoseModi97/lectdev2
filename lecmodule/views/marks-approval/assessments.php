<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var app\models\CourseWorkAssessment $model
 * @var app\models\search\MarksApprovalAssessmentSearch $searchModel
 * @var yii\data\ActiveDataProvider $assessmentsDataProvider
 * @var string $title
 * @var string $deptCode
 * @var string $deptName
 * @var string $facName
 * @var string $facCode
 * @var string $panelHeading
 * @var string $level
 * @var string $degreeCode
 */

use app\models\StudentCoursework;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

$this->title = $title;
$this->params['breadcrumbs'][] = [
    'label' => 'Courses with marks',
    'url' => ['/marks-approval/courses-with-marks']
];
$this->params['breadcrumbs'][] = $this->title;

// Build grid columns
$gridId = 'courses-assessments';

$assessmentNameColumn = [
    'attribute' => 'assessmentType.ASSESSMENT_NAME',
    'label' => 'ASSESSMENT NAME',
    'value' => function($model){
        $assessmentName = $model['assessmentType']['ASSESSMENT_NAME'];
        if(strpos($assessmentName, 'EXAM_COMPONENT') !== false){
            return str_replace('EXAM_COMPONENT','', $assessmentName);
        }else{
            return $assessmentName;
        }
    }
];
$weightColumn = [
    'attribute' => 'WEIGHT',
    'label' => 'WEIGHT'
];
$dividerColumn = [
    'attribute' => 'DIVIDER',
    'label' => 'MARKED OUT OF'
];
$resultDueDateColumn = [
    'label' => 'RESULT DUE DATE',
    'value' => function($model){
        $duedate = $model['RESULT_DUE_DATE'];
        return is_null( $duedate) ? 'NOT SET' : $duedate;
    }
];
$actionColumn = [
    'class' => 'kartik\grid\ActionColumn',
    'header' => 'ACTIONS',
    'template' => '{view-marks}',
    'contentOptions' => ['style'=>'white-space:nowrap;','class'=>'kartik-sheet-style kv-align-middle'],
    'buttons' => [
        'view-marks' => function($url, $model) use ($level, $degreeCode){
            $assessmentId = $model['ASSESSMENT_ID'];

            $assessmentName = $model['assessmentType']['ASSESSMENT_NAME'];
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
                $cwMark = StudentCoursework::find()
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
                $cwMark = StudentCoursework::find()
                    ->where(['ASSESSMENT_ID' => $assessmentId, $statusToCheck => 'APPROVED'])
                    ->one();
            }

            // Show links if there is at least one entry of approved marks at a certain level
            if(!is_null($cwMark)){
                return Html::a('<i class="fas fa-eye"></i> uploaded marks', Url::to([
                    '/marks-approval/marks-to-approve', 'id' => $assessmentId, 'level' => $level, 'degreeCode' => $degreeCode
                ]), [
                    'title' => 'View uploaded marks',
                    'class' => 'btn btn-xs btn-spacer'
                ]);
            }
            else{
                return Html::button('<i class="fas fa-ban"></i> Marks not submitted', [
                    'title' => 'Marks have not been submitted',
                    'class' => 'btn btn-disabled btn-danger',
                    'disabled' => 'disabled'
                ]);
            }
        }
    ]
];

// Render programmes table grid
try {
    echo GridView::widget([
        'id' => $gridId,
        'dataProvider' => $assessmentsDataProvider,
        'filterModel' => $searchModel,
        'headerRowOptions' => [
            'class' => 'kartik-sheet-style'
        ],
        'filterRowOptions' => [
            'class' => 'kartik-sheet-style'
        ],
        'pjax' => true,
        'pjaxSettings' => [
            'options' => [
                'id' => $gridId . '-pjax'
            ]
        ],
        'toolbar' => [
            '{toggleData}'
        ],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<h3 class="panel-title">' . $panelHeading . '</h3>',
        ],
        'persistResize' => false,
        'toggleDataContainer' => [
            'class' => 'btn-group mr-2'
        ],
        'toggleDataOptions' => [
            'minCount' => 50
        ],
        'itemLabelSingle' => 'course',
        'itemLabelPlural' => 'courses',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            $assessmentNameColumn,
            $weightColumn,
            $dividerColumn,
            $resultDueDateColumn,
            $actionColumn
        ]
    ]);
} catch (Exception $ex) {
    $message = 'An error occurred while trying to display marksheet assessments for approvals';
    if (YII_ENV_DEV) {
        $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}