<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/* @var $this yii\web\View */
/* @var $model app\models\CourseworkAssessment */
/* @var $searchModel app\models\search\CourseworkAssessmentSearch */
/* @var $cwProvider yii\data\ActiveDataProvider */
/* @var $title string */
/* @var $marksheetId string */
/* @var $cwWeight string */
/* @var $examWeight string */ 
/* @var $type string */

use app\models\MarksheetDef;
use app\models\StudentCoursework;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

$this->title = $title;

$this->params['breadcrumbs'][] = [
    'label' => 'MY COURSE ALLOCATIONS', 
    'url' => [
        '/allocated-courses'
    ]
];

$this->params['breadcrumbs'][] = $this->title;

$mkModel = MarksheetDef::findOne($marksheetId);
$courseCode = $mkModel->course->COURSE_CODE;
$courseName = $mkModel->course->COURSE_NAME;
?>

<div class="assessment-definition-index">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading"><b>Please Note the following:</b></div>
                <?php if($type === 'component'):?>
                    <div class="panel-body">
                        <P>1. An exam component has to be defined before any marks can be uploaded.</P>
                        <p>2. These include Oral Presentations, Term Papers etc that constitute the exam for a course and the associated weights.</p>
                        <p>3. The weights sum up to the total exam ratio which is defined for every programme.</p>
                    </div>
                <?php elseif($type === 'coursework'):?>
                    <div class="panel-body">
                        <P>1. Coursework assessment has to be defined before any marks can be uploaded.</P>
                        <p>2. These include CATS, Assignments, Oral Presentations, Term Papers etc that constitute coursework assessment for a course and the associated weights.</p>
                        <p>3. The weights sum up to the total coursework ratio which is defined for every programme.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php
    if($type === 'component'){
        $title = $courseName.' ('.$courseCode.') | EXAM RATIO: '.$examWeight;
        $createButtonText = 'exam component';
        $modalTitle = 'Exam component';
        $actionButtonText = 'component';
    }else{
        $title = $courseName.' ('.$courseCode.') | COURSE WORK RATIO: '.$cwWeight;
        $createButtonText = 'course work';
        $modalTitle = 'Course work';
        $actionButtonText = 'course work';
    }

    $gridColumns = [
        ['class' => 'kartik\grid\SerialColumn'],
        [
            'header'=> 'LOCK',
            'contentOptions'=>['class'=>'kv-row-select'],
            'content'=>function($model, $key){
                $checked = false;
                if($model->assessmentType->LOCKED === (int)1) {
                    $checked = true;
                }
                return Html::checkbox('selection[]', false, [
                    'class'=>'lock-checkbox',
                    'value'=>$key,
                    'checked' => $checked,
                ]);
            },
            'hAlign'=>'center',
            'vAlign'=>'middle',
            'hiddenFromExport'=>true,
            'mergeHeader'=>true,
        ],
        [
            'attribute' => 'assessmentType.ASSESSMENT_NAME',
            'label' => 'NAME',
            'value' => function($model){
                return str_replace('EXAM_COMPONENT ','', $model->assessmentType->ASSESSMENT_NAME);
            }
        ],
        [
            'attribute' => 'WEIGHT',
            'label' => 'WEIGHT'
        ],
        [
            'attribute' => 'DIVIDER',
            'label' => 'MARKED OUT OF'
        ],
        [
            'attribute' => 'RESULT_DUE_DATE',
            'label' => 'RESULT DUE DATE',
            'format' => 'raw',
            'contentOptions'=>['class'=>'kartik-sheet-style kv-align-middle'],
            'filterType' => GridView::FILTER_DATE,
            'filterWidgetOptions' => [
                'options'=>['id'=>'result-due-date'],
                'pluginOptions' => ['autoclose'=>true,'allowClear' => true,'format' => 'dd-M-yyyy',],
            ],
            'filterInputOptions' => ['placeholder' => 'Result Due Date']
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'header' => 'ACTIONS',
            'template' => '{edit-coursework} {delete-coursework} {input-assessment-marks} {edit-all-assessment-marks} 
                {delete-marks}',
            'contentOptions' => ['style'=>'white-space:nowrap;','class'=>'kartik-sheet-style kv-align-middle'],
            'buttons' => [
                'edit-coursework' => function ($url,$model) use($type, $actionButtonText) {
                    return Html::button('<i class="fas fa-edit"></i> ' . $actionButtonText,[
                        'title' => 'Edit ' . $actionButtonText,
                        'href' => Url::to([
                            '/assessments/edit',
                            'assessmentId' => $model->ASSESSMENT_ID,
                            'type' => $type
                        ]),
                        'class' => 'edit-assessment btn btn-xs'
                    ]);
                },
                'delete-coursework' => function($url, $model) use($type, $actionButtonText) {
                    return Html::button('<i class="fas fa-trash"></i> ' . $actionButtonText,[
                        'title' => 'Delete ' . $actionButtonText,
                        'href' => Url::to([
                            '/assessments/delete',
                            'assessmentId' => $model->ASSESSMENT_ID,
                            'type' => $type
                        ]),
                        'class' => 'delete-assessment btn btn-delete btn-xs'
                    ]);
                },
                'input-assessment-marks' => function ($url,$model) use($type) {
                    return Html::a('<i class="fas fa-plus"></i> marks',
                        Url::to([
                            '/marks/create-assessment',
                            'assessmentId' => $model->ASSESSMENT_ID,
                            'type' => $type
                        ]),
                        [
                            'title' => 'Enter marks',
                            'class' => 'btn btn-create btn-xs'
                        ]
                    );
                },
                'edit-all-assessment-marks' => function($url, $model) use($type) {
                    $studentMarksCount = StudentCoursework::find()
                        ->where(['ASSESSMENT_ID' => $model->ASSESSMENT_ID])
                        ->count();

                    if ($studentMarksCount > 0) {
                        return Html::a('<i class="fas fa-edit"></i> marks',
                            Url::to([
                                '/marks/edit-all-assessment',
                                'assessmentId' => $model->ASSESSMENT_ID,
                                'type' => $type
                            ]),
                            [
                                'title' => 'Edit marks',
                                'class' => 'btn btn-xs'
                            ]
                        );
                    }
                },
                'delete-marks' => function($url, $model) use($type) {
                    $studentMarksCount = StudentCoursework::find()
                        ->where(['ASSESSMENT_ID' => $model->ASSESSMENT_ID])
                        ->count();

                    if ($studentMarksCount > 0) {
                        return Html::a('<i class="fas fa-trash"></i> marks',
                            Url::to([
                                '/marks/marks-to-delete',
                                'assessmentId' => $model->ASSESSMENT_ID,
                                'type' => $type
                            ]),
                            [
                                'title' => 'Delete marks',
                                'class' => 'btn btn-delete btn-xs',
                            ]
                        );
                    }
                }
            ]
        ],
    ];

    try {
        echo GridView::widget([
            'id' => 'assessments-grid',
            'dataProvider' => $cwProvider,
            'filterModel' => $searchModel,
            'columns' => $gridColumns,
            'headerRowOptions' => ['class' => 'kartik-sheet-style'],
            'filterRowOptions' => ['class' => 'kartik-sheet-style'],
            'pjax' => true,
            'toolbar' => [
                [
                    'content' =>
                        Html::button('<i class="fas fa-save"></i> Save lock changes', [
                            'id' => 'lock-assessments',
                            'class' => 'btn',
                            'title' => 'Save lock changes'
                        ]) . '&nbsp;&nbsp;' .
                        Html::button('<i class="fas fa-plus"></i> ' . $createButtonText, [
                            'id' => 'new-assessment-btn',
                            'class' => 'btn btn-create pull-right',
                            'title' => 'Create ' . $createButtonText,
                            'href' => Url::to([
                                '/assessments/create',
                                'marksheetId' => $marksheetId,
                                'type' => $type
                            ])
                        ]),
                    'options' => ['class' => 'btn-group mr-2']
                ],
            ],
            'panel' => [
                'type' => GridView::TYPE_PRIMARY,
                'heading' => '<h3 class="panel-title">' . $title . '</h3>',
            ],
            'persistResize' => false,
            'toggleDataOptions' => ['minCount' => 20],
            'itemLabelSingle' => 'assessment',
            'itemLabelPlural' => 'assessments',
        ]);
    } catch (Exception $ex) {
        $message = $ex->getMessage();
        if(YII_ENV_DEV){
            $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
        }
        throw new ServerErrorHttpException($message, 500);
    }
    ?> 
</div>

<?php
$loadSpin = '<h1 class="text-center text-primary" style="font-size: 100px;"><i class="fas fa-spinner fa-pulse"></i></h1>';
Modal::begin([
    'header' => '<b>'.$modalTitle.'</b>',
    'id' => 'modal',
    'size' => 'modal-md',
    'options' => ['data-backdrop'=>"static", 'data-keyboard'=>"false"],
]);
echo "<div id='modalContent'></div>";
Modal::end();

echo $this->render('../marks/_appIsLoading');

$urlLockUnlock = Url::to(['/assessments/lock-and-unlock']);

$courseworkDefScript = <<< JS
    /** NOTE: Here assessments and coursework are used interchangably. They mean the same thing.*/
    const urlLockUnlock = '$urlLockUnlock';
    const loadDefault = '$loadSpin';
    /** Store the assessment IDs to be locked */
    let lockCheckboxVals = [];
    let unlockCheckboxVals = [];
    /** Inform if any lock assessment checkbox is clicked/status changed */
    let checkBoxTrigger = false;
    /** Inform if lock assessment changes have been submitted for processing */
    let checkBoxSaved = false;

    /** 
        Add IDs of locked assessments to checkboxVals array
        Remove the IDs of unlocked assessments from the array
     */
    $('#assessments-grid-pjax').on('click', '.lock-checkbox', function(e){
        checkBoxTrigger = true;
        if($(this).prop("checked") == true){
            // add checkbox value to locked IDs array
            lockCheckboxVals.push($(this).val()); 
            // remove checkbox value from unlocked IDs array
            let index = unlockCheckboxVals.indexOf($(this).val());
            if (index > -1) {
                unlockCheckboxVals.splice(index, 1);
            }
        }
        else{
            // add checkbox value to unlocked IDs array
            unlockCheckboxVals.push($(this).val()); 
            // remove checkbox value to from locked IDs array
            let index = lockCheckboxVals.indexOf($(this).val());
            if (index > -1) {
                lockCheckboxVals.splice(index, 1);
            }
        }
    });

    $('#assessments-grid-pjax').on('click', '#lock-assessments', function(e){
        e.preventDefault();
        if(checkBoxTrigger) {
            krajeeDialog.confirm("Are you sure you want to LOCK/UNLOCK the selected assessments?", function (result){
                if (result) {
                    checkBoxSaved = true;
                    let postData = {
                        'lockedIds': lockCheckboxVals,
                        'unlockedIds' : unlockCheckboxVals
                    };

                    $('#app-is-loading-modal-title').html('<b class="text-center">Updating assessment lock changes</b>');
                    $('#app-is-loading-modal').modal('show'); 

                    $.ajax({
                        type        :   'POST',
                        url         :   urlLockUnlock,
                        data        :   postData,
                        dataType    :   'json',
                        encode      :   true             
                    })
                    .done(function(data){
                        if(!data.success){
                            $('#app-is-loading-message').html('<p>Assessment lock changes not saved. </p><br/><p class="text-danger">' + data.message + '</p>');
                        }
                    })
                    .fail(function(data){});
                } else {
                    checkBoxSaved = false;
                    krajeeDialog.alert('Assessments lock changes cancelled.');
                }
            }); 
        }else{
            krajeeDialog.alert("No new lock assesments changes detected.");
        }
    });

    $('#assessments-grid-pjax').on('click', '#new-assessment-btn', function(e){
        e.preventDefault();
        if(checkBoxTrigger){
            if(!checkBoxSaved){
                krajeeDialog.alert("Please save the Lock assesments changes inorder to proceed!")
            }
        }
        else{
            $('#modalContent').html(loadDefault);
            $('#modal').modal('show')
                .find('#modalContent')
                .load($(this).attr('href'), function(e){});
        }
    });  

    $('#assessments-grid-pjax').on('click', '.edit-assessment', function(e){
        e.preventDefault();
        if(checkBoxTrigger) {
            if(!checkBoxSaved)
                krajeeDialog.alert("Please save the Lock assesments changes inorder to proceed!")
        }else{
            $('#modalContent').html(loadDefault);
            $('#modal').modal('show')
                .find('#modalContent')
                .load($(this).attr('href'), function(e){});
        }
    });

    $('#assessments-grid-pjax').on('click', '.delete-assessment', function(e){
        e.preventDefault();
        let deleteAssessmentUrl = $(this).attr('href');
        if(checkBoxTrigger) {
            if(!checkBoxSaved)
                krajeeDialog.alert("Please save the Lock assesments changes inorder to proceed!")
        }
        else{
            krajeeDialog.confirm("This will also delete any marks to this assessment. Are you sure you want to delete?", function (result) {
                if (result) {

                    $('#app-is-loading-modal-title').html('<b class="text-center">Deleting assessment</b>');
                    $('#app-is-loading-modal').modal('show'); 

                    let postData = {};
                    $.ajax({
                        type        :   'POST',
                        url         :   deleteAssessmentUrl,
                        data        :   postData,
                        dataType    :   'json',
                        encode      :   true             
                    })
                    .done(function(data){
                        if(!data.success){
                            $('#app-is-loading-message').html('<p>Assessment not deleted. </p><br/><p class="text-danger">' + data.message + '</p>');
                        }
                    })
                    .fail(function(data){});
                } else {
                    krajeeDialog.alert('Assessment deletion cancelled');
                }
            }); 
        }
    });
JS;
$this->registerJs($courseworkDefScript, yii\web\View::POS_END);
