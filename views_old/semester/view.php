<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Semester $model */

$this->title = $model->SEMESTER_ID;
$this->params['breadcrumbs'][] = ['label' => 'Semesters', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="semester-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'SEMESTER_ID' => $model->SEMESTER_ID], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'SEMESTER_ID' => $model->SEMESTER_ID], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'SEMESTER_ID',
            'ACADEMIC_YEAR',
            'DEGREE_CODE',
            'LEVEL_OF_STUDY',
            'SEMESTER_CODE',
            'INTAKE_CODE',
            'START_DATE',
            'END_DATE',
            'FIRST_SEMESTER',
            'SEMESTER_NAME',
            'CLOSING_DATE',
            'ADMIN_USER',
            'GROUP_CODE',
            'REGISTRATION_DEADLINE',
            'DESCRIPTION_CODE',
            'SESSION_TYPE',
            'DISPLAY_DATE',
            'REGISTRATION_DATE',
            'SEMESTER_TYPE',
        ],
    ]) ?>

</div>
