<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $coursesProvider
 * @var app\models\search\AllocationRequestsSearch $coursesSearch
 * @var app\models\CourseAllocationFilter $filter
 * @var string $title
 * @var string $deptCode
 * @var string $deptName
 * @var string $panelHeading
 */

use app\models\AllocationStatus;
use app\models\CourseAssignment;
use kartik\grid\GridView;
use yii\db\ActiveQuery;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

$this->title = $title;
$this->params['breadcrumbs'][] = [
    'label' => 'Lecturer allocation filters',
    'url' => ['/allocation/filters', 'filtersFor' => $filter->purpose]
];
$this->params['breadcrumbs'][] = $this->title;

$gridId = 'requested-courses-grid';
if($filter->purpose === 'serviceCourses'){
    $gridId = 'service-courses-grid';

    $departmentColumn = [
        'attribute' => 'requestingDept.DEPT_NAME',
        'label' => 'REQUESTING DEPARTMENT'
    ];
}else{
    $departmentColumn = [
        'attribute' => 'servicingDept.DEPT_NAME',
        'label' => 'SERVICING DEPARTMENT'
    ];
}

$courseCodeColumn = [
    'attribute' => 'marksheet.course.COURSE_CODE',
    'label' => 'COURSE CODE'
];

$courseNameColumn = [
    'attribute' => 'marksheet.course.COURSE_NAME',
    'label' => 'COURSE NAME'
];

$requestStatusColumn = [
    'label' => 'STATUS',
    'attribute' => 'status.STATUS_NAME',
    'format' => 'raw',
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

$allocatedLecturer = [
    'label' => 'ALLOCATED LECTURER(S)',
    'value' => function($model) use ($deptCode) {
        if($model->status->STATUS_NAME === 'APPROVED'){
            $assignments = CourseAssignment::find()->alias('CS')->select(['CS.PAYROLL_NO'])
                ->where(['CS.MRKSHEET_ID' => $model->MARKSHEET_ID])
                ->joinWith(['staff ST' => function(ActiveQuery $q){
                    $q->select([
                        'ST.PAYROLL_NO',
                        'ST.DEPT_CODE',
                        'ST.SURNAME',
                        'ST.OTHER_NAMES',
                        'ST.EMP_TITLE',
                        'ST.MOBILE',
                        'ST.EMAIL'
                    ]);
                }], true, 'INNER JOIN')
                ->asArray()->all();

            $lecturers = '';
            if(!empty($assignments)){
                foreach ($assignments as $assignment){
                    $lecturers .= $assignment['staff']['EMP_TITLE'] . ' ' . $assignment['staff']['SURNAME'] . ' ' .
                        $assignment['staff']['OTHER_NAMES'] . ' (' . $assignment['staff']['EMAIL'] . ' ' .
                        $assignment['staff']['MOBILE'] . '), ';
                }
            }

            return rtrim($lecturers, ', ');;
        }

        return '';
    }
];

if($gridId === 'service-courses-grid'){
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
    ];
}else{
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
    ];
}
?>

<?php
echo $this->render('activeFilters', ['filter' => $filter]);
echo $this->render('moreFilters', ['filter' => $filter]);
?>

<div class="requested-courses">
    <?php
    try {
        echo GridView::widget([
            'id' => $gridId,
            'dataProvider' => $coursesProvider,
            'filterModel' => $coursesSearch,
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
                'minCount' => 20
            ],
            'itemLabelSingle' => 'course',
            'itemLabelPlural' => 'courses',
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                $courseCodeColumn,
                $courseNameColumn,
                $departmentColumn,
                $requestStatusColumn,
                $allocatedLecturer,
                $actionColumn
            ]
        ]);
    } catch (Exception $ex) {
        $message = 'Failed to create grid for department courses.';
        if(YII_ENV_DEV){
            $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
        }
        throw new ServerErrorHttpException($message, 500);
    }
    ?>
</div>
<?php
echo $this->render('allocationHelpers', ['deptCode' => $deptCode]);