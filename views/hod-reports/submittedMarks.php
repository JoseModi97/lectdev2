<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 08-03-2021 11:59:48 
 * @modify date 08-03-2021 11:59:48 
 * @desc [description]
 */

/**
 * @var $this yii\web\View
 * @var $title string
 * @var $model app\models\StudentCoursework
 * @var $searchModel app\models\search\SubmittedMarksSearch
 * @var $submittedMarkskProvider yii\data\ActiveDataProvider
 * @var $marksheetId string
 * @var $courseCode string
 * @var $courseName string
 * @var $level string
 * @var $deptCode string
 * @var $depName string
 * @var $type string 
 * @var $facName string
 */

use app\components\GridExport;
use app\models\EmpVerifyView;
use kartik\grid\GridView;

$this->title = $title;

$this->params['breadcrumbs'][] = [
    'label' => 'courses in the department of ' . $deptName, 
    'url' => [
        '/courses/in-department', 
        'level' => $level, 
        'deptCode' => $deptCode,
        'type' => $type
    ]
];

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="hod-reports-submitted-marks">
    <h3><?= $facName;?></h3>
    <?php
        $gridId = 'hod-reports-submitted-marks-grid';
        $title = $courseName .' ('.$courseCode.') EXAM MARKS';
        $fileName = $courseCode.'_submitted_exam_marks';
        $contentBefore = '';
        $contentAfter = '';
        $centerContent = $courseCode.' SUBMITTED EXAM MARKS | ACADEMIC YEAR '.$academicYear;
        $registrationNoColumn = [
            'attribute' => 'REGISTRATION_NUMBER',
            'label' => 'REGISTRATION NUMBER',
            'hAlign' => 'left',
        ];
        $marksColumn = [
            'attribute' => 'MARK',
            'label' => 'MARKS',
            'hAlign' => 'left'
        ];
        $userColumn = [
            'attribute' => 'USER_ID',
            'label' => 'ENTERED BY',
            'hAlign' => 'left',
            'value' => function($model){
                $lecturer = EmpVerifyView::find()
                    ->select(['PAYROLL_NO', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE'])
                    ->where(['PAYROLL_NO' => $model['USER_ID']])
                    ->one();
                return $lecturer->EMP_TITLE.' '.$lecturer->SURNAME.' '.$lecturer->OTHER_NAMES;
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
                'options'=>['id'=>'exam-marks-date-entered'],
                'pluginOptions' => ['autoclose'=>true,'allowClear' => true,'format' => 'dd-M-yyyy',],
            ],
            'filterInputOptions' => ['placeholder' => 'Date Entered'],
            'value' => function($model){
                return $model['DATE_ENTERED'];
                $fullDate = Yii::$app->formatter->asDate($model['DATE_ENTERED'], 'full');
                $relativeTime = Yii::$app->formatter->format($model['DATE_ENTERED'], 'relativeTime');
                $relativeTime = "<b class='text-primary'>$relativeTime</b>";
                return $fullDate;
            }
        ];
        $remarksColumn = [
            'label' => 'REMARKS',
            'value' => function($model){
                return is_null($model['REMARKS']) ? '' : $model['REMARKS']; 
            },
            'width' => '20%',
            'hAlign' => 'left'
        ];

        echo GridView::widget([
            'id' => $gridId,
            'dataProvider' => $submittedMarkskProvider,
            'filterModel' => $searchModel,
            'columns' =>  [
                ['class'=>'kartik\grid\SerialColumn'],
                $registrationNoColumn,
                $marksColumn,
                $remarksColumn,
                $userColumn,
                $dateColumn
            ],
            'headerRowOptions' => ['class' => 'kartik-sheet-style'],
            'filterRowOptions' => ['class' => 'kartik-sheet-style'],
            'pjax' => true, 
            'toolbar' => [
                '{export}',
                '{toggleData}'
            ],
            'toggleDataContainer' => ['class' => 'btn-group mr-2'],
            'export' => [
                'fontAwesome' => false,
            ],
            'panel' => [
                'type' => GridView::TYPE_PRIMARY,
                'heading'=>'<h3 class="panel-title">'.$title.'</h3>',
            ],
            'persistResize' => false,
            'toggleDataOptions' => ['minCount' => 20],
            'exportConfig' => [
                GridView::PDF => GridExport::exportPdf([
                    'filename' => $fileName, 
                    'title' => $title,
                    'subject' => 'submitted marks',
                    'keywords' => 'submitted marks',
                    'contentBefore'=> $contentBefore,
                    'contentAfter'=> $contentAfter,
                    'centerContent' => $centerContent
                ])
            ],
            'itemLabelSingle' => 'mark',
            'itemLabelPlural' => 'marks',
        ]);
    ?>
</div>
