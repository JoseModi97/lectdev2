<?php

use yii\helpers\Url;

return [
    [
        'class' => 'kartik\grid\CheckboxColumn',
        'width' => '20px',
    ],
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'SEMESTER_ID',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'ACADEMIC_YEAR',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'DEGREE_CODE',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'LEVEL_OF_STUDY',
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'SEMESTER_CODE',
    ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'INTAKE_CODE',
    // ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'START_DATE',
    // ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'END_DATE',
    // ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'FIRST_SEMESTER',
    // ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'SEMESTER_NAME',
    // ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'CLOSING_DATE',
    // ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'ADMIN_USER',
    // ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'GROUP_CODE',
    // ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'REGISTRATION_DEADLINE',
    // ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'DESCRIPTION_CODE',
    // ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'SESSION_TYPE',
    // ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'DISPLAY_DATE',
    // ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'REGISTRATION_DATE',
    // ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'SEMESTER_TYPE',
    // ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'dropdown' => false,
        'noWrap' => 'true',
        'template' => '{view} {update} {delete}',
        'vAlign' => 'middle',
        'urlCreator' => function ($action, $model, $key, $index) {
            return Url::to([$action, 'id' => $key]);
        },
        'viewOptions' => [
            'role' => 'modal-remote',
            'title' => Yii::t('yii2-ajaxcrud', 'View'),
            'data-toggle' => 'tooltip',
            'class' => 'btn btn-sm btn-outline-success'
        ],
        'updateOptions' => [
            'role' => 'modal-remote',
            'title' => Yii::t('yii2-ajaxcrud', 'Update'),
            'data-toggle' => 'tooltip',
            'class' => 'btn btn-sm btn-outline-primary'
        ],
        'deleteOptions' => [
            'role' => 'modal-remote',
            'title' => Yii::t('yii2-ajaxcrud', 'Delete'),
            'class' => 'btn btn-sm btn-outline-danger',
            'data-confirm' => false,
            'data-method' => false, // for overide yii data api
            'data-request-method' => 'post',
            'data-toggle' => 'tooltip',
            'data-confirm-title' => Yii::t('yii2-ajaxcrud', 'Delete'),
            'data-confirm-message' => Yii::t('yii2-ajaxcrud', 'Delete Confirm')
        ],
    ],

];
