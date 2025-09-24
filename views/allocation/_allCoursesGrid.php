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
/* @var allCoursesProvider $dataProvider */
/* @var courseAssignmentSearch $searchModel */
/* @var string $facCode */
/* @var string $gridId */
/* @var string $degreeNameColumn */
/* @var string $courseCodeColumn */
/* @var string $courseNameColumn */
/* @var string $levelOfStudyColumn */
/* @var string $semesterCodeColumn */
/* @var string $groupNameColumn */
/* @var string $academicHoursColumn */

use kartik\grid\GridView;
use yii\web\ServerErrorHttpException;

include(Yii::getAlias('@views') . '/allocated-courses/_coursesColumns.php');

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
            'heading' => '<h3 class="panel-title"> All my course allocations </h3>',
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
            $academicHoursColumn,
        ]
    ]);
} catch (Exception $ex) {
    $message = 'Failed to create grid for allocated courses.';
    if(YII_ENV_DEV){
        $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}