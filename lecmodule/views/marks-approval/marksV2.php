<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/* @var yii\web\View $this  */
/* @var app\models\StudentCoursework $model*/
/* @var app\models\search\MarksToApproveSearch $searchModel */
/* @var app\models\Course $courseModel */
/* @var app\models\AssessmentType $assessmentTypeModel*/
/* @var app\models\CourseWorkAssessment $assessmentModel*/
/* @var yii\data\ActiveDataProvider $marksProvider*/
/* @var string $title*/
/* @var string $assessmentId*/
/* @var string $facCode*/
/* @var string $deptCode*/
/* @var string $level*/
/* @var string $degreeCode*/
/* @var bool $isExamComponent
 * @var string $marksheetId
 */

use app\components\SmisHelper;
use app\models\EmpVerifyView;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

/**
 * Track the HOD/Dean approval status of the marks
 * Should a record status change in the db table, we need the marks to be resubmitted again
 */
if($level === 'hod'){
    $statusToCheck = 'HOD_APPROVAL_STATUS';
}
else{
    $statusToCheck = 'DEAN_APPROVAL_STATUS';
}

$marksPending = SmisHelper::marksPending($assessmentId, $statusToCheck);
$assessmentTypeName = $assessmentTypeModel->ASSESSMENT_NAME;

$this->title = $title;
$this->params['breadcrumbs'][] = [
    'label' => 'Course assessments',
    'url' => [
        '/marks-approval/assessments-with-marks',
        'marksheetId' => $marksheetId,
        'level' => $level,
    ]
];
$this->params['breadcrumbs'][] = $this->title;
?>

    <div class="marks-approval">
        <?php
        $title = $courseModel->COURSE_NAME .' ('.$courseModel->COURSE_CODE.') | '.
            strtoupper(str_replace('EXAM_COMPONENT ','', $assessmentTypeName)).
            ' | WEIGHT: '.$assessmentModel->WEIGHT;

        if($assessmentTypeName !== 'EXAM'){
            $title .= '| MARKS OUT OF: '.$assessmentModel->DIVIDER;
        }

        $registrationNoColumn = [
            'attribute' => 'REGISTRATION_NUMBER',
            'label' => 'REGISTRATION NUMBER',
            'hAlign' => 'left',
        ];

        if($isExamComponent){
            $marksColumn = [
                'attribute' => 'MARK',
                'label' => 'WEIGHTED MARKS',
                'hAlign' => 'left'
            ];

            $rawmarksColumn = [
                'attribute' => 'RAW_MARK',
                'label' => 'RAW MARKS',
                'hAlign' => 'left'
            ];
        }else{
            $marksColumn = [
                'attribute' => 'MARK',
                'label' => 'MARKS',
                'hAlign' => 'left'
            ];
        }

        $userColumn = [
            'attribute' => 'USER_ID',
            'label' => 'ENTERED BY',
            'hAlign' => 'left',
            'width' => '25%',
            'value' => function($model){
                $lecturer = EmpVerifyView::find()
                    ->select(['PAYROLL_NO', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE'])
                    ->where(['PAYROLL_NO' => $model->USER_ID])
                    ->one();
                return $lecturer->EMP_TITLE . ' ' . $lecturer->SURNAME . ' ' . $lecturer->OTHER_NAMES;
            }
        ];

        $dateColumn = [
            'attribute' => 'DATE_ENTERED',
            'label' => 'DATE ENTERED',
            'hAlign' => 'left',
            'width' => '20%',
            'format' => 'raw',
            'contentOptions'=>['class'=>'kartik-sheet-style kv-align-middle'],
            'filterType' => GridView::FILTER_DATE,
            'filterWidgetOptions' => [
                'options'=>['id'=>'marks-date-entered'],
                'pluginOptions' => ['autoclose'=>true,'allowClear' => true,'format' => 'dd-M-yyyy',],
            ],
            'filterInputOptions' => ['placeholder' => 'Date Entered'],
            'value' => function($model){
                return Yii::$app->formatter->asDate($model->DATE_ENTERED, 'full');
            }
        ];

        $remarksColumn = [
            'label' => 'REMARKS',
            'value' => function($model){
                return is_null($model->REMARKS) ? '' : $model->REMARKS;
            },
            'width' => '30%',
            'hAlign' => 'left'
        ];

        if($assessmentTypeName === 'EXAM' || $isExamComponent){
//    if($marksPending){
            $toolbar = [
                [
                    'content' =>
                        Html::button('<i class="fa fa-check"></i> Approve marks', [
                            'title' => 'Approve Marks',
                            'id' => 'marks-approve-btn',
                            'class' => 'btn btn-spacer',
                            'data-assessment-id' => $assessmentId,
                            'data-level' => $level
                        ]).
                        Html::a('View consolidated marks',
                            Url::to(['/marks/consolidate', 'marksheetId' => $marksheetId]),
                            ['title' => 'View consolidated marks', 'class' => 'btn btn-spacer']
                        ),
                    'options' => ['class' => 'btn-group mr-2']
                ],
                '{toggleData}',
            ];

            $actionColumn = [
                'class' => 'kartik\grid\ActionColumn',
                'header' => 'ACTIONS',
                'template' => '{edit-marks}',
                'contentOptions' => ['style'=>'white-space:nowrap;','class'=>'kartik-sheet-style kv-align-middle'],
                'buttons' => [
                    'edit-marks' => function ($url, $model) use ($level){
                        $status = '';
                        if ($level === 'hod'){
                            $status = $model['HOD_APPROVAL_STATUS'];
                        }elseif ($level === 'dean'){
                            $status = $model['DEAN_APPROVAL_STATUS'];
                        }

                        if($status === 'PENDING') {
                            return Html::button('<i class="fas fa-edit"></i> marks', [
                                'title' => 'Edit Marks',
                                'href' => Url::to(['/marks-approval/edit-marks',
                                    'studentCourseworkId' => $model->COURSE_WORK_ID,
                                    'level' => $level
                                ]),
                                'data-pjax' => '0',
                                'class' => 'btn btn-link btn-xs edit-marks'

                            ]);
                        }else{
                            return Html::button('<i class="fas fa-check"></i> approved', [
                                'title' => 'Marks already approved can not be edited',
                                'class' => 'btn btn-xs btn-create',
                                'disabled' => 'disabled'
                            ]);
                        }
                    }
                ],
                'hAlign' => 'center',
            ];
//    }else{
//        $toolbar = [
//            [
//                'content' =>
//                    Html::a('View consolidated marks',
//                        Url::to(['/marks/consolidate', 'marksheetId' => $marksheetId]),
//                        ['title' => 'View consolidated marks', 'class' => 'btn btn-spacer']
//                    ),
//                'options' => ['class' => 'btn-group mr-2']
//            ],
//            '{toggleData}',
//        ];
//    }

            if($isExamComponent){
                $gridColumns = [
                    ['class'=>'kartik\grid\SerialColumn'],
                    $registrationNoColumn,
                    $marksColumn,
                    $rawmarksColumn,
                    $userColumn,
                    $dateColumn,
                    $remarksColumn,
                    $actionColumn
                ];

//        if($marksPending){
//            $gridColumns = [
//                ['class'=>'kartik\grid\SerialColumn'],
//                $registrationNoColumn,
//                $marksColumn,
//                $rawmarksColumn,
//                $userColumn,
//                $dateColumn,
//                $remarksColumn,
//                $actionColumn
//            ];
//        }else{
//            $gridColumns = [
//                ['class'=>'kartik\grid\SerialColumn'],
//                $registrationNoColumn,
//                $marksColumn,
//                $rawmarksColumn,
//                $userColumn,
//                $dateColumn,
//                $remarksColumn,
//            ];
//        }
            }else{
                $gridColumns = [
                    ['class'=>'kartik\grid\SerialColumn'],
                    $registrationNoColumn,
                    $marksColumn,
                    $userColumn,
                    $dateColumn,
                    $remarksColumn,
                    $actionColumn
                ];

//        if($marksPending){
//            $gridColumns = [
//                ['class'=>'kartik\grid\SerialColumn'],
//                $registrationNoColumn,
//                $marksColumn,
//                $userColumn,
//                $dateColumn,
//                $remarksColumn,
//                $actionColumn
//            ];
//        }else{
//            $gridColumns = [
//                ['class'=>'kartik\grid\SerialColumn'],
//                $registrationNoColumn,
//                $marksColumn,
//                $userColumn,
//                $dateColumn,
//                $remarksColumn,
//            ];
//        }
            }
        }else{
            $toolbar = [
                [
                    'content' =>
                        Html::a('View consolidated marks',
                            Url::to(['/marks/consolidate', 'marksheetId' => $marksheetId]),
                            ['title' => 'View consolidated marks', 'class' => 'btn btn-spacer']
                        ),
                    'options' => ['class' => 'btn-group mr-2']
                ],
                '{toggleData}',
            ];

            $gridColumns = [
                ['class'=>'kartik\grid\SerialColumn'],
                $registrationNoColumn,
                $marksColumn,
                $userColumn,
                $dateColumn,
                $remarksColumn
            ];
        }

        try {
            echo GridView::widget([
                'id' => 'approve-marks-grid',
                'dataProvider' => $marksProvider,
                'filterModel' => $searchModel,
                'columns' => $gridColumns,
                'headerRowOptions' => ['class' => 'kartik-sheet-style'],
                'filterRowOptions' => ['class' => 'kartik-sheet-style'],
                'pjax' => true,
                'toolbar' => $toolbar,
                'toggleDataContainer' => ['class' => 'btn-group mr-2'],
                'panel' => [
                    'type' => GridView::TYPE_PRIMARY,
                    'heading' => '<h3 class="panel-title">' . $title . '</h3>',
                ],
                'persistResize' => false,
                'toggleDataOptions' => ['minCount' => 50],
                'itemLabelSingle' => 'student',
                'itemLabelPlural' => 'students',
            ]);
        } catch (Exception $ex) {
            $message = 'Failed to create the table grid.';
            if(YII_ENV_DEV){
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
        ?>
    </div>

    <!-- START EDIT MARKS MODAL -->
<?php
if($level === 'hod') $header = 'HOD Marks Update';
else $header = 'Dean/Director Marks Update';
Modal::begin([
    'header' => '<b> Marks Update </b>',
    'id' => 'edit-marks-modal',
    'size' => 'modal-md',
    'options' => ['data-backdrop'=>"static", 'data-keyboard'=>"false"],
]);
echo "<div id='edit-marks-modal-content'></div>";
Modal::end();

echo $this->render('../marks/_appIsLoading');
?>
    <!-- END EDIT MARKS MODAL -->

<?php
$urlApproveMarks = Url::to(['marks-approval/approve-marks']);
$approveMarkJS = <<< JS
    $(document).ready(function(ev){
        // Edit individual marks modal
        $('#approve-marks-grid-pjax').on('click', '.edit-marks', function(e){ 
            e.preventDefault();
            const editMarksUrl = $(this).attr('href');
            let loadDefault = '<h1 class="text-center text-primary" style="font-size: 100px;"><i class="fas fa-spinner fa-pulse"></i></h1>';
            $('#edit-marks-modal-content').html(loadDefault);
            $('#edit-marks-modal')
                .modal('show')
                .find('#edit-marks-modal-content')
                .load(editMarksUrl, function(e){});
        });

        // Approve marks at HOD/Dean/Director level 
        $('#approve-marks-grid-pjax').on('click', '#marks-approve-btn', function(e){ 
            e.preventDefault();
            const approveMarksUrl = '$urlApproveMarks';
            const assessmentId = $(this).attr('data-assessment-id');
            const level = $(this).attr('data-level');
            let confirmMsg = "If submitted, you will be unable to edit these marks at this level. Are you sure you want to proceed?";
            krajeeDialog.confirm(confirmMsg, function (result) {
                if (result) {
                    
                    $('#app-is-loading-modal-title').html('<b class="text-center">Approving marks</b>');
                    $('#app-is-loading-modal').modal('show'); 

                    let postData = {
                        'assessmentId': assessmentId,
                        'level': level
                    };
                    console.log(postData);
                    $.ajax({
                        type        :   'POST',
                        url         :   approveMarksUrl,
                        data        :   postData,
                        dataType    :   'json',
                        encode      :   true             
                    })
                    .done(function(data){
                        if(!data.success){
                            $('#app-is-loading-message').html('<p>Marks not approved. </p><br/><p class="text-danger">' + data.message + '</p>');
                        }
                    })
                    .fail(function(data){});
                } else {
                    krajeeDialog.alert('Marks approval was cancelled');
                }
            });
        });
    });
JS;
$this->registerJs($approveMarkJS, yii\web\View::POS_END);

