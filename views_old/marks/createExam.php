<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc This file builds the UI to enter exam marks for marksheet with single exam components
 */

/**
 * @var yii\web\View $this
 * @var app\models\Marksheet $model
 * @var app\models\MarksUpload $marksUploadModel
 * @var app\models\search\CreateMarksSearch $searchModel
 * @var yii\data\ActiveDataProvider $studentMarksheetProvider
 * @var string $title
 * @var string $marksheetId
 * @var string $examWeight
 * @var string $courseCode
 * @var string $courseName
 * @var string $assessementId
 * @var string $assessmentName
 * @var string $maximumMarks
 * @var string $assessmentId
 */

use app\components\GridExport;
use app\components\SmisHelper;
use app\models\ExamType;
use app\models\StudentCoursework;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
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

if(strpos($assessmentName, 'EXAM_COMPONENT') !== false){
    $this->params['breadcrumbs'][] = [
        'label' => 'COURSEWORK', 
        'url' => Url::to(['/assessments', 'marksheetId' => $marksheetId, 'type' => 'component'])
    ];
}

$this->params['breadcrumbs'][] = $title;
?>

<!-- Build grid columns -->
<?php
$examTypes = ExamType::find()->select(['EXAMTYPE_CODE', 'DESCRIPTION'])->asArray()->all();
$examTypesList = ArrayHelper::map($examTypes, 'EXAMTYPE_CODE', function($examType){
    return $examType['DESCRIPTION'];
});

$scModelFound = false;
$scModelRemarks = '';

$regNumColumn = [
    'attribute' => 'REGISTRATION_NUMBER',
    'label' => 'REGISTRATION NUMBER',
    'width' => '7%',
    'hAlign' => 'left',
];
$surnameColumn = [
    'attribute' => 'student.SURNAME',
    'label' => 'SURNAME',
    'width' => '8%',
    'hAlign' => 'left',
    'value' => function($model){
        return $model['student']['SURNAME'];
    }
];
$otherNamesColumn =[ 
    'attribute' => 'student.OTHER_NAMES',
    'label' => 'OTHER NAMES',
    'width' => '13%',
    'hAlign' => 'left',
    'value' => function($model){
        return $model['student']['OTHER_NAMES'];
    }
];
$examDescriptionColumn = [
    'attribute' => 'examType.EXAMTYPE_CODE',
    'label' => 'EXAM TYPE',
    'width' => '5%',
    'hAlign' => 'left',
    'vAlign' => 'middle',
    'contentOptions' => [
        'class'=>'exam-description'
    ],
    'filterType' => GridView::FILTER_SELECT2,
    'filter' => $examTypesList,
    'filterWidgetOptions' => [
        'options'=>[
            'id'=>'insert-exam-marks-type'
        ],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ],
    'filterInputOptions' => [
        'placeholder' => '-- ALL --'
    ],
    'value' => function($model){
        return $model['examType']['DESCRIPTION'];
    }
];
$marksColumn = [
    'label' => 'MARKS',
    'width' => '5%',
    'hAlign' => 'left',
    'contentOptions' => [
        'class'=>'skip-export-xls'
    ],
    'headerOptions' => [
        'class'=>'skip-export-xls'
    ],
    'value' => function($model) use ($assessmentId, &$scModelFound, &$scModelRemarks){
        $scModel = StudentCoursework::find()->select(['COURSE_WORK_ID', 'MARK', 'REMARKS'])
            ->where([
                'REGISTRATION_NUMBER' => $model['REGISTRATION_NUMBER'],
                'ASSESSMENT_ID' => $assessmentId])
            ->one();
        if(is_null($scModel)){
            $scModelFound = false;
            $scModelRemarks = '';
            return '';
        }else{
            $scModelFound = true;
            if(is_null($scModel->REMARKS)){
                $scModelRemarks = '';
            }else{
                $scModelRemarks = $scModel->REMARKS;
            }
            return $scModel->MARK;
        }
    }
]; 
$inputMarksColumn = [
    'label' => 'INPUT MARKS',
    'contentOptions'=>[
        'class'=>'skip-export-pdf'
    ],
    'headerOptions'=>[
        'class'=>'skip-export-pdf'
    ],
    'hAlign' => 'left',
    'width' => '9%',
    'format' => 'raw',
    'value' => function($model) use ($maximumMarks, &$scModelFound){
        $marksInput = '<input 
                name="'.$model['REGISTRATION_NUMBER'].'" type="number" step="0.01" class="form-control marks"  
                min="0" max="'.$maximumMarks.'" oninput="validity.valid||(value=\'\');"/>';
        if($scModelFound){
            return '';
        }else{
            return $marksInput;
        }
    }
];
$remarksColumn = [
    'label' => 'REMARKS',
    'hAlign' => 'left',
    'width' => '20%',
    'format' => 'raw', 
    'value' => function($model) use (&$scModelFound, &$scModelRemarks){
        $remarksInput = '<input name="REMARKS['.$model['REGISTRATION_NUMBER'].']" type="text" class="form-control remarks"/>';
        if($scModelFound){
            return $scModelRemarks;
        }else{
            return $remarksInput;
        }
    }
];

try {
    $isAProjectCourse = SmisHelper::isAProjectCourse($marksheetId);
    if ($isAProjectCourse) {
        $projectTitleColumn = [
            'label' => 'TITLE',
            'hAlign' => 'left',
            'width' => '18%',
            'format' => 'raw',
            'value' => function ($model) {
                return '<input name="TITLE[' . $model['REGISTRATION_NUMBER'] . ']" type="text" class="form-control project-title"/>';
            }
        ];

        $projectHoursColumn = [
            'label' => 'HOURS',
            'hAlign' => 'left',
            'width' => '8%',
            'format' => 'raw',
            'value' => function($model) {
                return '<input 
                name="' . $model['REGISTRATION_NUMBER'] . '" type="number" step="1" class="form-control project-hours"  
                min="0"  oninput="validity.valid||(value=\'\');"/>';
            }
        ];

        $gridColumns = [
            [
                'class'=>'kartik\grid\SerialColumn',
                'width' => '2%'
            ],
            $regNumColumn,
            $surnameColumn,
            $otherNamesColumn,
            $examDescriptionColumn,
            $marksColumn,
            $inputMarksColumn,
            $remarksColumn,
            $projectTitleColumn,
            $projectHoursColumn
        ];
    }else{
        $gridColumns = [
            [
                'class'=>'kartik\grid\SerialColumn',
                'width' => '2%'
            ],
            $regNumColumn,
            $surnameColumn,
            $otherNamesColumn,
            $examDescriptionColumn,
            $marksColumn,
            $inputMarksColumn,
            $remarksColumn
        ];
    }
} catch (Exception $ex) {
    $message = $ex->getMessage();
    if (YII_ENV_DEV) {
        $message = $message . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}
?>
<!-- End build grid columns -->

<div class="row">
    <div class="col-md-12 col-lg-12">
        <div class="panel panel-primary">
            <div class="panel-heading"><b>Please Note the following:</b></div>
            <div class="panel-body">
                <P>1. The Excel file to upload marks must be in the format Excel Workbook.</P>
                <p>2. Click on the View consolidated marks button to see the class performance before submitting marks as final.</p>
            </div>
        </div>
    </div>
</div>

<!-- Display grid and columns -->
<div class="insert-exam-marks-index">
<?php
$title = $courseName .' ('.$courseCode.') | '.$assessmentName.' | WEIGHT: '.$examWeight;
$fileName = $courseCode.'_EXAM';
$toolbar = [
    [
        'content' =>
            Html::button('Save Entered Marks', [
                'title' => 'Save Entered Marks',
                'id' => 'submit-exam-marks-btn',
                'class' => 'btn btn-spacer',
            ]).
            Html::button('Excel Marks Upload', [
                'title' => 'Upload Marks From Excel File',
                'id' => 'marks-upload-btn',
                'class'=>'btn btn-spacer',
            ]).
            Html::a('View consolidated marks',
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
        'id' => 'insert-exam-marks-gridview',
        'dataProvider' => $studentMarksheetProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumns,
        'headerRowOptions' => [
            'class' => 'kartik-sheet-style'
        ],
        'filterRowOptions' => [
            'class' => 'kartik-sheet-style'
        ],
        'pjax' => true,
        'toolbar' => $toolbar,
        'toggleDataContainer' => [
            'class' => 'btn-group mr-2'
        ],
        'export' => [
            'fontAwesome' => false,
            'label' => 'Export Excel|Pdf file'
        ],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<h5 class="panel-title text-dark">' . $title . '</h5>',
        ],
        'persistResize' => false,
        'toggleDataOptions' => [
            'minCount' => 50
        ],
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
                'centerContent' => $courseCode . ' Exam',
            ]),
        ],
        'itemLabelSingle' => 'student',
        'itemLabelPlural' => 'students',
    ]);
} catch (Exception $ex) {
    $message = $ex->getMessage();
    if (YII_ENV_DEV) {
        $message = $message . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}
?>
</div>
<!-- End display grid and columns -->

<!-- START MARKS UPLOAD MODAL-->
<?php
echo $this->render('_excelMarksUpload',[
    'modalId' => 'marks-upload-modal',
    'formId' => 'marks-upload-form',
    'formAction' => Url::to(['/marks/upload']),
    'marksheetId' => $marksheetId,
    'marksUploadModel' => $marksUploadModel,
    'assessementId' => $assessmentId,
    'assessmentName' => $assessmentName,
    'marksType' => 'EXAM',
    'weight' => $examWeight,
    'maximumMarks' => $maximumMarks
]);

echo $this->render('_appIsLoading');
?>
<!-- END MARKS UPLOAD MODAL-->

<!-- START PAGE CSS AND JS-->
<?php
$urlStoreMarks = Url::to(['/marks/save']);
$urlSubmitMarks = Url::to(['/marks/submit']);
$csrf = Yii::$app->request->csrfToken;

if($isAProjectCourse){
    $courseIsAProject = 'isAProject';
}else{
    $courseIsAProject = 'isNotAProject';
}

$insertExamMarksJs = <<< JS
    $('#app-is-loading-modal-title').html('');
    $('#pp-is-loading-modal').modal('hide');

    const csrf = '$csrf';
    const urlStoreMarks = '$urlStoreMarks';
    const urlSubmitMarks = '$urlSubmitMarks';
    const marksheetId = '$marksheetId';
    const marksType = 'EXAM';
    const examWeight = '$examWeight'; 
    const assessmentId = '$assessmentId';
    const maximumMarks = '$maximumMarks';
    const courseIsAProject = '$courseIsAProject';
    
    // Save input marks
    $('#insert-exam-marks-gridview-pjax').on('click', '#submit-exam-marks-btn', function(e){    
        let marks = [];

        // Read entered marks, remarks, project title and hours from the table 
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
                
                if(courseIsAProject === 'isAProject'){
                    markEntry.projectTitle = $(this).find('.project-title').val();
                    markEntry.projectHours = $(this).find('.project-hours').val();
                }
                
                marks.push(markEntry);
            }
        });

        if(marks.length === 0){
            krajeeDialog.alert('No marks have been entered.');
        }else{
            krajeeDialog.confirm('Save entered marks?', function (result) {
                if (result) {
                    $('#app-is-loading-modal-title').html('<b class="text-center">Saving Entered Marks</b>');
                    $('#app-is-loading-modal').modal('show');
                    
                   let postData = {
                        '_csrf'         :   csrf,
                        'assessmentId'  :   assessmentId,
                        'marksheetId'   :   marksheetId,
                        'marksType'     :   marksType,
                        'weight'        :   examWeight,
                        'maximumMarks'  :   maximumMarks,
                        'students'      :   marks
                    };
                       
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
                     .fail(function(data){
                         console.error(data);
                     });
                } else {
                    krajeeDialog.alert('Marks submission cancelled.');
                }
            });
        }
    });

    // Insert exam marks in bulk with an excel file 
    $('#insert-exam-marks-gridview-pjax').on('click', '#marks-upload-btn', function(e){ 
        e.preventDefault();
        $('#excel-marks-file').val();
        $('#upload-marks-loader').html('');
        $('#marks-upload-modal').modal('show');
    });

    // Submit exam marks as final
    $('#insert-exam-marks-gridview-pjax').on('click', '#submit-final-exam-marks-btn', function(e){ 
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
JS;
$this->registerJs($insertExamMarksJs, yii\web\View::POS_READY);