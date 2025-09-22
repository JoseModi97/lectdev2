<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Semester */
?>
<div class="semester-view">
 
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
