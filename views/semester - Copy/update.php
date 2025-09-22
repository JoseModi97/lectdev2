<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Semester $model */

$this->title = 'Update Semester: ' . $model->SEMESTER_ID;
$this->params['breadcrumbs'][] = ['label' => 'Semesters', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->SEMESTER_ID, 'url' => ['view', 'SEMESTER_ID' => $model->SEMESTER_ID]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="semester-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
