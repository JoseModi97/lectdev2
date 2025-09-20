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
/* @var app\models\search\CourseAssignmentSearch $courseAssignmentSearch */
/* @var yii\data\ActiveDataProvider $nonSuppCoursesProvider */
/* @var yii\data\ActiveDataProvider $suppCoursesProvider */
/* @var yii\data\ActiveDataProvider $allCoursesProvider */
/* @var string $title */
/* @var string $academicYear */
/* @var string $facCode */

use kartik\tabs\TabsX;
use yii\web\ServerErrorHttpException;


$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;
$nonSuppCoursesCw = $this->render('_nonSuppCoursesCwGrid', [
    'gridId' => 'non-supp-courses-cw-grid',
    'dataProvider' => $nonSuppCoursesProvider,
    'searchModel' => $courseAssignmentSearch,
    'academicYear' => $academicYear,
    'facCode' => $facCode
]);

$nonSuppCoursesExam = $this->render('_nonSuppCoursesExamGrid', [
    'gridId' => 'non-supp-courses-exam-grid',
    'dataProvider' => $nonSuppCoursesProvider,
    'searchModel' => $courseAssignmentSearch,
    'academicYear' => $academicYear,
    'facCode' => $facCode
]);

$suppCoursesExam = $this->render('_suppCoursesExamGrid', [
    'gridId' => 'supp-courses-exams-grid',
    'dataProvider' => $suppCoursesProvider,
    'searchModel' => $courseAssignmentSearch,
    'academicYear' => $academicYear,
    'facCode' => $facCode
]);

$allCourses = $this->render('_allCoursesGrid', [
    'gridId' => 'all-courses-grid',
    'dataProvider' => $allCoursesProvider,
    'searchModel' => $courseAssignmentSearch,
    'facCode' => $facCode
]);

$items = [
    [
        'label' => '<i class="glyphicon glyphicon-menu-down"></i> <strong>Coursework management</strong>',
        'content' => $nonSuppCoursesCw,
        'options' => [
            'id' => 'non-supp-courses-cw-tab'
        ]
    ],
    [
        'label' => '<i class="glyphicon glyphicon-menu-down"></i> <strong>Exam management</strong>',
        'content' => $nonSuppCoursesExam,
        'options' => [
            'id' => 'non-supp-courses-exam-tab'
        ]
    ],
    [
        'label' => '<i class="glyphicon glyphicon-menu-down"></i> <strong>Supplementary management</strong>',
        'content' => $suppCoursesExam,
        'options' => [
            'id' => 'sup-courses-exam'
        ]
    ],
    [
        'label' => '<i class="glyphicon glyphicon-menu-down"></i> <strong>All courses</strong>',
        'content' => $allCourses,
        'options' => [
            'id' => 'tab-all-courses'
        ]
    ]
];
?>

<div class="allocated-courses-index">
    <div class="row">
        <div class="col-md-12 col-lg-12">
            <?php
            try {
                echo TabsX::widget([
                    'items' => $items,
                    'position' => TabsX::POS_ABOVE,
                    'encodeLabels' => false,
                    'options' => ['class' => 'nav nav-pills'],
                    'pluginOptions' => ['class' => 'nav nav-invert']
                ]);
            } catch (Exception $ex) {
                $message = 'Failed to create grid tabs for department courses.';
                if (YII_ENV_DEV) {
                    $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
                }
                throw new ServerErrorHttpException($message, 500);
            }
            ?>
        </div>
    </div>
</div>