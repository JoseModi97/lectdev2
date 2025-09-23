<?php
/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @desc Manage lecturer courses
 */

/* @var yii\web\View $this */
/* @var app\models\CourseAssignment $model */
/* @var suppCoursesProvider $dataProvider */
/* @var courseAssignmentSearch $searchModel */
/* @var string $facCode */
/* @var string $gridId */
/* @var string $degreeNameColumn */
/* @var string $courseCodeColumn */
/* @var string $courseNameColumn */
/* @var string $levelOfStudyColumn */
/* @var string $semesterCodeColumn */
/* @var string $groupNameColumn */
/* @var string $academicYear */
/* @var string $academicHoursColumn */

use app\components\SmisHelper;
use app\models\CourseWorkAssessment;
use app\models\StudentCoursework;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

include(Yii::getAlias('@views') . '/allocated-courses/_coursesColumns.php');

$suppExamActionColumn = [
    'class' => 'kartik\grid\ActionColumn',
    'header' => 'ACTIONS',
    'template' => '{enter-exam-marks} {edit-exam-marks} {delete-marks} {exam-components} {exam-list}',
    'contentOptions' => ['style'=>'white-space:nowrap;','class'=>'kartik-sheet-style kv-align-middle'],
    'buttons' => [

        /**
         * Link to enter exam marks for this course
         * Only displayed for courses under programmes that don't belong to faculties with multiple exam components.
         */
        'enter-exam-marks' => function($url, $model) {
            if(!SmisHelper::hasMultipleExamComponents($model->MRKSHEET_ID)) {
                return Html::a('<i class="fas fa-plus"></i> marks',
                    Url::to([
                        '/marks/create-exam',
                        'marksheetId' => $model->MRKSHEET_ID
                    ]),
                    [
                        'title' => 'Enter exam marks',
                        'class' => 'btn btn-xs btn-create btn-spacer'
                    ]
                );
            }

            return '';
        },

        /**
         * Link to edit exam marks for this course.
         * Only displayed for courses under programmes that don't belong to faculties with multiple exam components.
         */
        'edit-exam-marks' => function($url, $model){
            if(!SmisHelper::hasMultipleExamComponents($model->MRKSHEET_ID)) {
                $examAssessment = SmisHelper::getExamAssessment($model->MRKSHEET_ID);

                if($examAssessment) {
                    $examAssessmentId = $examAssessment->ASSESSMENT_ID;
                    $studentMarksCount = StudentCoursework::find()
                        ->where(['ASSESSMENT_ID' => $examAssessmentId])
                        ->count();

                    if ($studentMarksCount > 0) {
                        return Html::a('<i class="fas fa-edit"></i> marks',
                            Url::to([
                                '/marks/edit-all-exam',
                                'marksheetId' => $model->MRKSHEET_ID
                            ]),
                            [
                                'title' => 'Edit exam marks',
                                'class' => 'btn btn-xs btn-spacer',
                            ]
                        );
                    }
                }
            }

            return '';
        },

        /**
         * Link to delete exam marks for this course.
         * Only displayed for courses under programmes that don't belong to faculties with multiple exam components.
         */
        'delete-marks' =>  function($url, $model){
            if(!SmisHelper::hasMultipleExamComponents($model->MRKSHEET_ID)) {
                $examAssessment = SmisHelper::getExamAssessment($model->MRKSHEET_ID);

                if($examAssessment) {
                    $examAssessmentId = $examAssessment->ASSESSMENT_ID;
                    $studentMarksCount = StudentCoursework::find()
                        ->where(['ASSESSMENT_ID' => $examAssessmentId])
                        ->count();

                    if ($studentMarksCount > 0) {
                        return Html::a('<i class="fas fa-trash"></i> marks',
                            Url::to([
                                '/marks/marks-to-delete',
                                'assessmentId' => null,
                                'type' => null,
                                'marksheetId' => $model->MRKSHEET_ID
                            ]),
                            [
                                'title' => 'Delete marks',
                                'class' => 'btn btn-delete btn-xs'
                            ]
                        );
                    }
                }
            }

            return '';
        },

        /**
         * Link to create/get exam components created for this course
         * Only displayed for courses under programmes that belong to faculties with multiple exam components.
         */
        'exam-components' => function($url, $model){
            if(SmisHelper::hasMultipleExamComponents($model->MRKSHEET_ID)) {
                // Look for any created exam components for this marksheet and display the correct link
                $marksheetId = $model->MRKSHEET_ID;
                $cwModel = CourseWorkAssessment::find()->alias('CW')
                    ->joinWith(['assessmentType AT'])
                    ->where(['CW.MARKSHEET_ID' => $marksheetId])
                    ->andWhere(['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT'])
                    ->one();
                if (is_null($cwModel)) {
                    $linkName = '<i class="fas fa-plus"></i> exam component';
                    $linkTitle = 'Create exam component';
                    $linkClass = 'btn btn-xs btn-spacer btn-create';
                } else {
                    $linkName = '<i class="fas fa-eye"></i> exam components';
                    $linkTitle = 'View created exam components';
                    $linkClass = 'btn btn-xs btn-spacer';
                }
                return Html::a($linkName,
                    Url::to([
                        '/assessments',
                        'marksheetId' => $marksheetId,
                        'type' => 'component'
                    ]),
                    [
                        'title' => $linkTitle,
                        'class' => $linkClass
                    ]
                );
            }

            return '';
        },

        /**
         * Link to get students registered for this course.
         * This is displayed for all courses.
         */
        'exam-list' => function($url, $model){
            return Html::a('<i class="fas fa-eye"></i> exam list',
                Url::to([
                    '/marksheet-students',
                    'assignmentId' => $model->LEC_ASSIGNMENT_ID,
                    'type' => 'examList'
                ]),
                [
                    'title' => 'View exam list',
                    'class' => 'btn btn-xs btn-spacer'
                ]
            );
        }
    ]
];
try {
    echo GridView::widget([
        'id' => $gridId,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'headerRowOptions' => ['class' => 'kartik-sheet-style'],
        'filterRowOptions' => ['class' => 'kartik-sheet-style'],
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
            'heading' => '<h3 class="panel-title">My supplementary course allocations for the academic year '
                . $academicYear . ' | exam management</h3>',
        ],
        'persistResize' => false,
        'toggleDataContainer' => ['class' => 'btn-group mr-2'],
        'toggleDataOptions' => ['minCount' => 20],
        'itemLabelSingle' => 'course',
        'itemLabelPlural' => 'courses',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            $degreeNameColumn,
            $courseCodeColumn,
            $courseNameColumn,
            $levelOfStudyColumn,
            $semesterCodeColumn,
            $groupNameColumn,
            $suppExamActionColumn
        ]
    ]);
} catch (Exception $ex) {
    $message = 'Failed to create grid for allocated courses.';
    if(YII_ENV_DEV){
        $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}