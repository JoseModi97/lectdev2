<?php

/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc This file builds the UI to enter assessment and exam components marks
 */

/**
 * @var yii\web\View $this
 * @var app\models\Marksheet $model
 * @var app\models\MarksUpload $marksUploadModel
 * @var app\models\search\CreateMarksSearch $searchModel
 * @var yii\data\ActiveDataProvider $studentMarksheetProvider
 * @var string $title
 * @var string $marksheetId
 * @var string $cwWeight
 * @var string $courseCode
 * @var string $courseName
 * @var string $assessmentId
 * @var string $assessmentName
 * @var string $assessmentWeight
 * @var string $maximumMarks
 * @var string $type
 */

use app\components\GridExport;
use app\components\BreadcrumbHelper;
use app\models\CourseAssignment;
use app\models\ExamType;
use app\models\StudentCoursework;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

$this->title = $title;


echo BreadcrumbHelper::generate([
    ['label' => 'My Course Allocations', 'url' => ['/allocated-courses']],
    ['label' => 'Coursework Marks', 'url' => ['/assessments', 'marksheetId' => $marksheetId, 'type' => $type, 'section' => 'manage-marks']],
    ['label' => 'Missing Marks']
]);
$this->params['breadcrumbs'][] = [
    'label' => 'MY COURSE ALLOCATIONS',
    'url' => [
        '/allocated-courses'
    ]
];

$labelName = ($type === 'component') ? 'EXAM COMPONENTS' : 'COURSEWORK';
$this->params['breadcrumbs'][] = [
    'label' => $labelName,
    'url' => [
        '/assessments',
        'marksheetId' => $marksheetId,
        'type' => $type
    ]
];

$this->params['breadcrumbs'][] = $this->title;

$assignmentModel = CourseAssignment::find()
    ->where(['MRKSHEET_ID' => $marksheetId, 'PAYROLL_NO' => Yii::$app->user->identity->PAYROLL_NO])
    ->one();
?>

<!-- Build grid columns -->
<?php
$examTypes = ExamType::find()->select(['EXAMTYPE_CODE', 'DESCRIPTION'])->asArray()->all();
$examTypesList = ArrayHelper::map($examTypes, 'EXAMTYPE_CODE', function ($examType) {
    return $examType['DESCRIPTION'];
});

$scModelFound = false;
$scModelRemarks = '';
$scModelRawMark = '';

$regNumColumn = [
    'attribute' => 'REGISTRATION_NUMBER',
    'label' => 'REGISTRATION NUMBER',
    'width' => '7%',
    'hAlign' => 'left',
];
$surnameColumn = [
    'attribute' => 'student.SURNAME',
    'label' => 'SURNAME',
    'width' => '10%',
    'hAlign' => 'left',
    'value' => function ($model) {
        return $model['student']['SURNAME'];
    }
];
$otherNamesColumn = [
    'attribute' => 'student.OTHER_NAMES',
    'label' => 'OTHER NAMES',
    'width' => '18%',
    'hAlign' => 'left',
    'value' => function ($model) {
        return $model['student']['OTHER_NAMES'];
    }
];
$examDescriptionColumn = [
    'attribute' => 'examType.EXAMTYPE_CODE',
    'label' => 'EXAM TYPE',
    'width' => '15%',
    'hAlign' => 'left',
    'vAlign' => 'middle',
    'contentOptions' => [
        'class' => 'exam-description'
    ],
    'filterType' => GridView::FILTER_SELECT2,
    'filter' => $examTypesList,
    'filterWidgetOptions' => [
        'options' => [
            'id' => 'insert-exam-marks-type'
        ],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ],
    'filterInputOptions' => [
        'placeholder' => '-- ALL --'
    ],
    'value' => function ($model) {
        return $model['examType']['DESCRIPTION'];
    }
];
$weightedMarksColumn = [
    'label' => 'WEIGHTED MARKS',
    'contentOptions' => [
        'class' => 'skip-export-xls'
    ],
    'headerOptions' => [
        'class' => 'skip-export-xls'
    ],
    'width' => '5%',
    'value' => function ($model) use ($assessmentId, &$scModelFound, &$scModelRemarks, &$scModelRawMark) {
        $scModel = StudentCoursework::find()
            ->select(['COURSE_WORK_ID', 'MARK', 'RAW_MARK', 'REMARKS'])
            ->where([
                'REGISTRATION_NUMBER' => $model['REGISTRATION_NUMBER'],
                'ASSESSMENT_ID' => $assessmentId
            ])
            ->one();

        if (is_null($scModel)) {
            $scModelFound = false;
            $scModelRawMark = '';
            $scModelRemarks = '';
            return '';
        } else {
            $scModelFound = true;
            $scModelRawMark = $scModel->RAW_MARK;
            if (is_null($scModel->REMARKS)) {
                $scModelRemarks = '';
            } else {
                $scModelRemarks = $scModel->REMARKS;
            }
            return $scModel->MARK;
        }
    }
];
$rawMarksColumn = [
    'label' => 'RAW MARKS',
    'contentOptions' => [
        'class' => 'skip-export-xls'
    ],
    'headerOptions' => [
        'class' => 'skip-export-xls'
    ],
    'width' => '5%',
    'value' => function ($model) use (&$scModelRawMark) {
        return $scModelRawMark;
    }
];
$inputMarksColumn = [
    'label' => 'INPUT MARKS',
    'contentOptions' => [
        'class' => 'skip-export-pdf'
    ],
    'headerOptions' => [
        'class' => 'skip-export-pdf'
    ],
    'hAlign' => 'left',
    'width' => '10%',
    'format' => 'raw',
    'value' => function ($model) use ($maximumMarks, &$scModelFound) {
        $marksInput = '<input 
            name="' . $model['REGISTRATION_NUMBER'] . '" type="number" step="0.01" class="form-control marks" 
            min="0" max="' . $maximumMarks . '" oninput="validity.valid||(value=\'\');"  />';
        if ($scModelFound) {
            return '';
        } else {
            return $marksInput;
        }
    }
];
$remarksColumn = [
    'label' => 'REMARKS',
    'hAlign' => 'left',
    'width' => '30%',
    'format' => 'raw',
    'value' => function ($model) use (&$scModelFound, &$scModelRemarks) {
        $remarksInput = '<input name="REMARKS[' . $model['REGISTRATION_NUMBER'] . ']" type="text" 
            class="form-control remarks"/>';
        if ($scModelFound) {
            return $scModelRemarks;
        } else {
            return $remarksInput;
        }
    }
];
$gridColumns = [
    ['class' => 'kartik\grid\SerialColumn'],
    $regNumColumn,
    $surnameColumn,
    $otherNamesColumn,
    $examDescriptionColumn,
    $weightedMarksColumn,
    $rawMarksColumn,
    $inputMarksColumn,
    $remarksColumn
];
?>
<!-- End build grid columns -->

<!-- Display grid and columns -->
<div class="scrollable-content" style="overflow-x:hidden !important">
    <div class="row">
        <div class="col-md-12 col-lg-12">
            <div class="panel panel-primary">
                <div class="panel-heading"><b>Please Note the following:</b></div>
                <div class="panel-body">
                    <P>1. The Excel file used to upload marks must be in the format Excel Workbook.</P>
                    <p>2. Click on the View consolidated marks button to see the class performance before submitting marks as final.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="insert-assessment-marks-index">
        <?php
        $title = $courseName . ' (' . $courseCode . ') | ' . strtoupper($assessmentName) . ' | ASSESSMENT WEIGHT: ' . $assessmentWeight . ' | MARKS OUT OF: ' . $maximumMarks;
        $fileName = $courseCode . '_' . $assessmentName;
        $toolbar = [
            [
                'content' =>
                Html::Button('Save Entered Marks', [
                    'title' => 'Save Entered Marks',
                    'id' => 'submit-cw-marks-btn',
                    'class' => 'btn btn-spacer',
                ]) .
                    Html::button('Excel Marks Upload', [
                        'title' => 'Upload Marks From Excel File',
                        'id' => 'marks-upload-btn',
                        'class' => 'btn btn-spacer',
                    ]) .
                    Html::a(
                        'View consolidated marks',
                        Url::to(['/marks/consolidate', 'marksheetId' => $marksheetId]),
                        ['title' => 'View consolidated marks', 'class' => 'btn btn-spacer']
                    ),
                'options' => ['class' => 'btn-group mr-2']
            ],
            '{export}',
            '{toggleData}',
        ];

        try {
            echo GridView::widget([
                'id' => 'insert-cw-marks-gridview',
                'dataProvider' => $studentMarksheetProvider,
                'filterModel' => $searchModel,
                'columns' => $gridColumns,
                'headerRowOptions' => ['class' => 'kartik-sheet-style'],
                'filterRowOptions' => ['class' => 'kartik-sheet-style'],
                'pjax' => true,
                'toolbar' => $toolbar,
                'toggleDataContainer' => ['class' => 'btn-group mr-2'],
                'export' => [
                    'fontAwesome' => false,
                    'label' => 'Export Excel|Pdf file'
                ],
                'panel' => [
                    'type' => GridView::TYPE_PRIMARY,
                    'heading' => '<h5 class="panel-title text-dark">' . $title . '</h5>',
                ],
                'persistResize' => false,
                'toggleDataOptions' => ['minCount' => 50],
                'exportConfig' => [
                    GridView::EXCEL => GridExport::exportExcel([
                        'filename' => $fileName,
                        'worksheet' => 'marks'
                    ]),
                    GridView::PDF => GridExport::exportPdf([
                        'filename' => $fileName,
                        'title' => $title,
                        'subject' => 'students marks',
                        'keywords' => 'students marks',
                        'contentBefore' => '',
                        'contentAfter' => '',
                        'centerContent' => $courseCode . ' ' . $assessmentName,
                    ]),
                ],
                'itemLabelSingle' => 'student',
                'itemLabelPlural' => 'students',
            ]);
        } catch (Exception $ex) {
            $message = 'An error occurred while attempting to create the table grid.';
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
        ?>
    </div>
</div>

<!-- End display grid and columns -->

<!-- START MARKS UPLOAD MODAL-->
<?php
echo $this->render('_excelMarksUpload', [
    'modalId' => 'marks-upload-modal',
    'formId' => 'marks-upload-form',
    'formAction' => Url::to(['/marks/upload']),
    'marksheetId' => $marksheetId,
    'marksUploadModel' => $marksUploadModel,
    'assessementId' => $assessmentId,
    'assessmentName' => $assessmentName,
    'marksType' => 'ASSESSMENT',
    'weight' => $assessmentWeight,
    'maximumMarks' => $maximumMarks
]);
echo $this->render('_appIsLoading');
?>
<!-- END MARKS UPLOAD MODAL-->

<?php
$urlStoreMarks = Url::to(['/marks/save']);
$urlSubmitMarks = Url::to(['/marks/submit']);
$loader = '<h1 class="text-center text-primary" style="font-size: 100px;"><i class="fas fa-spinner fa-pulse"></i></h1>';

$insertCwMarksJs = <<< JS
    $(document).ready(function(){
        $('#app-is-loading-modal-title').html('');
        $('#pp-is-loading-modal').modal('hide');

        const urlStoreMarks = '$urlStoreMarks';
        const urlSubmitMarks = '$urlSubmitMarks';
        const loader = '$loader';
        const marksheetId = '$marksheetId';
        const marksType = 'ASSESSMENT';
        const assessmentWeight = '$assessmentWeight'; 
        const assessmentId = '$assessmentId';
        const maximumMarks = '$maximumMarks';

        $('#insert-cw-marks-gridview-pjax').on('click', '#submit-cw-marks-btn', function(e){ 
            let marks = [];
            
            // Read entered marks and remarks from the grid 
            $('table > tbody').find('tr').each(function(e) {
                let marksInput = $(this).find('.marks');
                if(typeof marksInput.val() === 'undefined' || marksInput.val() === '');
                else{
                    let markEntry = {
                        'examDescription' : $(this).find('.exam-description').html(),
                        'registrationNumber' : marksInput.attr('name'),
                        'marks' : marksInput.val(),
                        'remarks' : $(this).find('.remarks').val()
                    };
                    marks.push(markEntry);
                }
            });
            
            if(marks.length === 0){
                krajeeDialog.alert('No marks have been entered.');
            }else{
                krajeeDialog.confirm('Save entered marks?', function (result) {
                    if (result) {
                        let postData = {
                            'assessmentId'  :   assessmentId,
                            'marksheetId'   :   marksheetId,
                            'marksType'     :   marksType,
                            'weight'        :   assessmentWeight,
                            'maximumMarks'  :   maximumMarks,
                            'students'      :   marks
                        };

                        $('#app-is-loading-modal-title').html('<b class="text-center">Saving Entered Marks</b>');
                        $('#app-is-loading-modal').modal('show');

                        $.ajax({
                            type        :   'POST',
                            url         :   urlStoreMarks,
                            data        :   postData,
                            dataType    :   'json',
                            encode      :   true             
                        })
                        .done(function(data){
                            if(!data.success){
                                $('#app-is-loading-message').html('<p>Marks not saved. </p><br/><p class="text-danger">' + data.message + '</p>');
                            }
                        })
                        .fail(function(data){});
                    } else {
                        krajeeDialog.alert('Marks submission cancelled.');
                    }
                });
            }
        });

        // Insert assessment marks in bulk with an excel file 
        $('#insert-cw-marks-gridview-pjax').on('click', '#marks-upload-btn', function(e){ 
            e.preventDefault();
            $('#excel-marks-file').val();
            $('#upload-marks-loader').html('');
            $('#marks-upload-modal').modal('show');
        });

        /** Submit assessment marks as final */
        $('#insert-cw-marks-gridview-pjax').on('click', '#submit-final-cw-marks-btn', function(e){ 
            e.preventDefault();
            let confirmMsg = "If submitted as final, you will be unable to edit these marks at this level. Are you sure you want to proceed?";
            krajeeDialog.confirm(confirmMsg, function (result) {
                if (result) {
                    let postData = {
                        'assessmentId': assessmentId
                    };

                    $('#app-is-loading-modal-title').html('<b class="text-center">Submitting Marks As Final</b>');
                    $('#app-is-loading-modal').modal('show');

                    $.ajax({
                        type        :   'POST',
                        url         :   urlSubmitMarks,
                        data        :   postData,
                        dataType    :   'json',
                        encode      :   true             
                    })
                    .done(function(data){
                        if(!data.success){
                            $('#app-is-loading-message').html('<p>Marks not submitted. </p><br/><p class="text-danger">' + data.message + '</p>');
                        }
                    })
                    .fail(function(data){});
                } else {
                    krajeeDialog.alert('Marks submission as final cancelled.');
                }
            });
        });
    });
JS;
$this->registerJs($insertCwMarksJs, yii\web\View::POS_END);
