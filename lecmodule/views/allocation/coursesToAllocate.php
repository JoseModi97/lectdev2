<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $coursesProvider
 * @var app\models\search\MarksheetDefAllocationSearch $coursesSearch
 * @var app\models\CourseAllocationFilter $filter
 * @var string $title
 * @var string $deptCode
 * @var string $deptName
 * @var string $panelHeading
 */

use app\models\CourseAssignment;
use app\models\EmpVerifyView;
use app\models\MarksheetDef;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

$this->title = $title;
$this->params['breadcrumbs'][] = [
    'label' => 'Lecturer allocation filters',
    'url' => ['/allocation/filters', 'filtersFor' => $filter->purpose]
];
$this->params['breadcrumbs'][] = $this->title;
$semesterIdColumn = [
    'attribute' => 'SEMESTER_ID',
    'label' => 'SEMESTER_ID ID'
];
$marksheetIdColumn = [
    'attribute' => 'MRKSHEET_ID',
    'label' => 'MARKSHEET ID'
];
$courseCodeColumn = [
    'attribute' => 'course.COURSE_CODE',
    'label' => 'COURSE CODE'
];

$courseNameColumn = [
    'attribute' => 'course.COURSE_NAME',
    'label' => 'COURSE NAME',
    'width' => '40%'
];

$lecturerColumn = [
    'label' => 'ASSIGNED LECTURER',
    'format' => 'raw',
    'hAlign' => 'middle',
    'width' => '20%',
    'value' => function($d){
        $assignments = CourseAssignment::find()->select(['PAYROLL_NO'])
            ->where(['MRKSHEET_ID' => $d->MRKSHEET_ID])->all();
        $leadLecturer = '';
        $otherLecturers = '';
        $courseLeaderFound = false;
        $courseLeader = null;
        if(!empty($assignments)){
            foreach($assignments as $assignment){
                $lecturer = EmpVerifyView::find()
                    ->select(['PAYROLL_NO', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE'])
                    ->where(['PAYROLL_NO' => $assignment->PAYROLL_NO])
                    ->one();

                if(is_null($lecturer)){
                    continue;
                }

                $lecturerName = '';
                if(!empty($lecturer->EMP_TITLE)){
                    $lecturerName .= $lecturer->EMP_TITLE;
                }

                if(!empty($lecturer->OTHER_NAMES)){
                    $lecturerName .= $lecturer->OTHER_NAMES;
                }

                if(empty($lecturerName)){
                    continue;
                }

                if(!$courseLeaderFound){
                    $courseLeader = MarksheetDef::find()
                        ->select(['PAYROLL_NO'])
                        ->where(['MRKSHEET_ID' => $d->MRKSHEET_ID, 'PAYROLL_NO' => $lecturer->PAYROLL_NO])->one();
                }

                if(!$courseLeaderFound && $courseLeader){
                    $courseLeaderFound = true;
                    $leadLecturer = '<div class="p-3 mb-2 lecturers course-leader">' . $lecturerName . '</div>';
                }else{
                    $otherLecturers .= '<div class="p-3 mb-2 lecturers not-course-leader">' . $lecturerName . '</div>';
                }
            }
        }
        return $leadLecturer . $otherLecturers;
    }
];

$actionColumn = [
    'class' => 'kartik\grid\ActionColumn',
    'template' => '{assign-course-render}{manage-course-lecturer}{remove-course-lecturer}',
    'contentOptions' => ['style'=>'white-space:nowrap;','class'=>'kartik-sheet-style kv-align-middle'],
    'buttons' => [
        'assign-course-render' => function($url, $model){
            return Html::button('<i class="fa fa-user-plus"></i> Allocate', [
                'title' => 'Allocate lecturer',
                'href' => Url::to(['/allocation/assign-course-render', 'id' => 'NULL',
                    'marksheetId' => $model->MRKSHEET_ID, 'type'=>'departmental']),
                'class' => 'btn  btn-xs btn-spacer assign-lecturer',
                'data-id' => 'NULL',
                'data-marksheetId' => $model->MRKSHEET_ID,
                'data-type'=>'departmental'
            ]);
        },
        'manage-course-lecturer' => function($url, $model){
            return Html::button('<i class="fa fa-tasks"></i> Manage', [
                'title' => 'Manage allocated lecturer(s)',
                'href' => Url::to(['/allocation/allocated-lecturer-render', 'marksheetId' => $model->MRKSHEET_ID,
                    'purpose' => 'manage']),
                'class' => 'btn  btn-xs btn-spacer manage-lecturer'
            ]);
        },
        'remove-course-lecturer' => function($url, $model){
            return Html::button('<i class="fa fa-trash"></i> Remove', [
                'title' => 'Remove allocated lecturer(s)',
                'href' => Url::to(['/allocation/allocated-lecturer-render', 'marksheetId' => $model->MRKSHEET_ID,
                    'purpose' => 'remove']),
                'class' => 'btn  btn-xs btn-spacer remove-lecturer'
            ]);
        }
    ]
];
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading"><b>Please note the following</b></div>
            <div class="panel-body">
                <p>1. Every course must have a course leader who is responsible for defining examination assessments and uploading marks.</p>
                <p>2. Courses taught by  part-time lecturers (Non - UON Staff) MUST be assigned a course leader who is a UON staff member.</p>
                <p>3. Click on the <i class="fa fa-tasks" style="color: #008cba;"></i> manage button to set a lead lecturer. &nbsp;
                    <span style="height: 15px; width: 30px; border-radius: 20%; background-color: green;
                display: inline-block;"></span> Color indicates a lead lecturer.
                </p>
            </div>
        </div>
    </div>
</div>

<?php
echo $this->render('activeFilters', ['filter' => $filter]);
echo $this->render('moreFilters', ['filter' => $filter]);
?>

<div class="allocate-courses">
    <?php
    $gridId = 'non-supp-courses-grid';
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
                [
                    'content' =>
                        Html::button('Click on the <i class="fa fa-tasks"></i> Manage button to set a lead lecturer.',
                            [
                            'class' => 'btn indicator-btn',
                            ]
                        ) . '&nbsp;' .
                        Html::button('<span style="height: 15px; width: 30px; border-radius: 20%; background-color: green; 
                                display: inline-block;"></span> Green indicates a lead lecturer',
                            [
                            'class' => 'btn indicator-btn pull-right',
                            ]
                        ),
                    'options' => ['class' => 'btn-group mr-2']
                ],
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
                'minCount' => 50
            ],
            'itemLabelSingle' => 'course',
            'itemLabelPlural' => 'courses',
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                $courseCodeColumn,
                $courseNameColumn,
                $lecturerColumn,
                $actionColumn
            ]
        ]);
    } catch (Exception $ex) {
        $message = 'Failed to create grid for department courses.';
        if(YII_ENV_DEV){
            $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
        }
        throw new ServerErrorHttpException($message, 500);
    }?>
</div>

<?php
echo $this->render('allocationHelpers', ['deptCode' => $deptCode]);




