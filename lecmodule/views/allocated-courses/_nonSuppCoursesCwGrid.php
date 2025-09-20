<?php
/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 15-12-2020 20:50:22
 * @modify date 15-12-2020 20:50:22
 * @desc Manage lecturer courses
 */

/* @var yii\web\View $this */
/* @var app\models\CourseAssignment $model */
/* @var nonSuppCoursesProvider $dataProvider */
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

use app\models\Course;
use app\models\CourseWorkAssessment;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

include(Yii::getAlias('@views') . '/allocated-courses/_coursesColumns.php');

$courseworkActionColumn = [
    'class' => 'kartik\grid\ActionColumn',
    'header' => 'ACTIONS',
    'template' => '{coursework} {class-list}',
    'contentOptions' => [
        'style'=>'white-space:nowrap;',
        'class'=>'kartik-sheet-style kv-align-middle'
    ],
    'buttons' => [
        // Links to create/get course works created for this course ie neither exam nor exam components
        'coursework' => function($url, $model){
            /**
             * For courses that don't require coursework marks, we don't need to create coursework assessments for them.
             * Instead, we show the "coursework not needed" message to let the users know.
             * Otherwise, we try to find the coursework assessments defined for a given course. If one or more is found,
             * we let the user view and manage them. Else the user creates atleast one.
             */
            $course = Course::find()->select(['HAS_COURSE_WORK'])
                ->where(['COURSE_ID' => $model->marksheetDef->COURSE_ID])->one();

            if($course->HAS_COURSE_WORK === 1) {
                $marksheetId = $model->MRKSHEET_ID;
                $cwModel = CourseWorkAssessment::find()->alias('CW')
                    ->joinWith(['assessmentType AT'])
                    ->where(['CW.MARKSHEET_ID' => $marksheetId])
                    ->andWhere(['NOT', ['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT']])
                    ->andWhere(['NOT', ['AT.ASSESSMENT_NAME' => 'EXAM']])
                    ->one();
                if (is_null($cwModel)) {
                    $linkName = '<i class="fas fa-plus"></i> coursework';
                    $linkTitle = 'Create coursework';
                    $linkClass = 'btn btn-xs btn-spacer btn-create';
                } else {
                    $linkName = '<i class="fas fa-eye"></i> coursework';
                    $linkTitle = 'View created coursework';
                    $linkClass = 'btn btn-xs btn-spacer';
                }
                return Html::a($linkName,
                    Url::to([
                        '/assessments',
                        'marksheetId' => $marksheetId,
                        'type' => 'coursework'
                    ]),
                    [
                        'title' => $linkTitle,
                        'class' => $linkClass
                    ]
                );
            }else{
                return Html::button('<i class="fas fa-ban"></i> cw not required', [
                    'class' => 'btn btn-disabled btn-danger',
                    'disabled' => 'disabled'
                ]);
            }
        },
        // Link to get students registered for this course
        'class-list' => function($url, $model){
            return Html::a('<i class="fas fa-eye"></i> class list',
                Url::to([
                    '/marksheet-students',
                    'assignmentId' => $model->LEC_ASSIGNMENT_ID,
                    'type' => 'classList'
                ]),
                [
                    'title' => 'View class list',
                    'class' => 'btn btn-xs btn-spacer'
                ]
            );
        },
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
            'heading' => '<h3 class="panel-title">My course allocations for the academic year '
                . $academicYear . ' | course work management</h3>',
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
            $courseworkActionColumn,
        ]
    ]);
} catch (Exception $ex) {
    $message = 'Failed to create grid for allocated courses.';
    if(YII_ENV_DEV){
        $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}
