<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 11-01-2021 14:45:37 
 * @modify date 11-01-2021 14:45:37 
 * @desc [description]
 */

/* @var yii\web\View $this */
/* @var app\models\AllocationRequest $model */
/* @var yii\data\ActiveDataProvider $dataProvider */
/* @var app\models\search\AllocationRequestsSearch $searchModel */
/* @var string $deptCode */
/* @var string $deptName */
/* @var string $gridId */

use app\models\AllocationStatus;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

$status = AllocationStatus::find()->all();
$statusList = ArrayHelper::map($status, 'STATUS_NAME', function($sta){
    return $sta->STATUS_NAME;
});

$heading = 'Requests for lecturers from this department to other departments';

if($gridId === 'service-courses-grid'){
    $heading = 'Requests for lecturers from other departments to this department';
}

$courseCodeColumn = [
    'attribute' => 'marksheet.course.COURSE_CODE',
    'label' => 'COURSE CODE'
];
$courseNameColumn = [
    'attribute' => 'marksheet.course.COURSE_NAME',
    'label' => 'COURSE NAME'
];
$degreeNameColumn = [
	'attribute' => 'marksheet.semester.degreeProgramme.DEGREE_NAME',
    'label' => 'DEGREE NAME',
	'value' => function($d){
		$sem = $d->marksheet->semester;
		return $sem->degreeProgramme->DEGREE_NAME . ' ('.$sem->ACADEMIC_YEAR.')';
	},
	'width' => '310px',
	'group' => true,
	'groupedRow' => true,
	'groupOddCssClass' => 'kv-grouped-row',
	'groupEvenCssClass' => 'kv-grouped-row',
];
$requestingDeptColumn = [
    'attribute' => 'requestingDept.DEPT_NAME',
    'label' => 'REQUESTING DEPT'
];
$servicingDeptColumn = [
    'attribute' => 'servicingDept.DEPT_NAME',
    'label' => 'SERVICING DEPT'
];
$requestStatusColumn = [
    'label' => 'STATUS',
    'attribute' => 'status.STATUS_NAME',
    'filterType' => GridView::FILTER_SELECT2,
    'filter' => $statusList, 
    'vAlign' => 'middle',
    'format' => 'raw',
    'filterWidgetOptions' => [
        'options'=>['id'=>$gridId.'-status'],
        'pluginOptions' => ['allowClear' => true],
    ],
    'filterInputOptions' => ['placeholder' => '-- ALL --'],
    'value' => function($model){
        $status = $model->status->STATUS_NAME;
        if($status === 'APPROVED'){
            return '<div class="text-center status status-success">
                <i class="fa fa-check" aria-hidden="true"></i> APPROVED
            </div>';
        } elseif($status === 'PENDING'){
            return '<div class="text-center status status-warning">
                <i class="fa fa-clock" aria-hidden="true"></i> PENDING
            </div>';
        } else{
            return '<div class="text-center status status-danger">
                <i class="fa fa-ban" aria-hidden="true"></i> NOT APPROVED
            </div>';
        }
    }
];

$columns = [
    ['class' => 'yii\grid\SerialColumn'],
    $degreeNameColumn,
    $courseCodeColumn,
    $courseNameColumn,
    $servicingDeptColumn,
    $requestStatusColumn,
    $actionColumn = [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{view-request-render}',
        'contentOptions' => ['style'=>'white-space:nowrap;','class'=>'kartik-sheet-style kv-align-middle'],
        'buttons' => [
            'view-request-render' => function($url, $model){
                return Html::button('<i class="fas fa-eye"></i> Details', [
                    'title' => 'View lecturer request details',
                    'href' => Url::to(['/allocation/view-request-render', 'requestId' => $model->REQUEST_ID]),
                    'class' => 'btn btn-xs btn-spacer view-course-request'
                ]);
            }
        ]
    ]
];

if($gridId === 'service-courses-grid'){
    $columns = [
        ['class' => 'yii\grid\SerialColumn'],
        $degreeNameColumn,
        $courseCodeColumn,
        $courseNameColumn,
        $requestingDeptColumn,
        $requestStatusColumn,
        $actionColumn = [
            'class' => 'kartik\grid\ActionColumn',
            'template' => '{assign-course-render}',
            'contentOptions' => ['style'=>'white-space:nowrap;','class'=>'kartik-sheet-style kv-align-middle'],
            'buttons' => [
                'assign-course-render' => function($url, $model){ 
                    $status = AllocationStatus::findOne($model->STATUS_ID);
                    if($status->STATUS_NAME === 'PENDING'){
                        return Html::button('<i class="fa fa-user-plus"></i> Allocate', [
                            'title' => 'Allocate lecturer',
                            'href' => Url::to(['/allocation/assign-course-render', 'id' => $model->REQUEST_ID,
                                'marksheetId' => $model->MARKSHEET_ID, 'type'=>'service']),
                            'class' => 'btn  btn-xs btn-spacer assign-external-lecturer',
                            'data-id' => $model->REQUEST_ID,
                            'data-marksheetId' => $model->MARKSHEET_ID,
                            'data-type'=>'service'
                        ]);
                    }else{
                        return Html::button('<i class="fas fa-eye"></i> Details', [
                            'title' => 'View lecturer request details',
                            'href' => Url::to(['/allocation/view-request-render', 'requestId' => $model->REQUEST_ID]),
                            'class' => 'btn btn-xs btn-spacer view-course-request'
                        ]);
                    }
                },
            ]
        ]
    ];
}

try {
    echo GridView::widget([
        'id' => $gridId,
        'dataProvider' => $dataProvider,
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
            'heading' => '<h3 class="panel-title">' . $heading . '</h3>',
        ],
        'persistResize' => false,
        'toggleDataContainer' => [
            'class' => 'btn-group mr-2'
        ],
        'toggleDataOptions' => [
            'minCount' => 20
        ],
        'itemLabelSingle' => 'course',
        'itemLabelPlural' => 'courses',
        'columns' => $columns
    ]);
} catch (Exception $ex) {
    $message = 'Failed to create grid for department courses.';
    if(YII_ENV_DEV){
        $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}



