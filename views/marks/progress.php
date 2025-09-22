<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @date: 2/22/2023
 * @time: 12:53 PM
 */

/**
 * @var yii\web\View $this
 * @var string $title
 * @var yii\data\ArrayDataProvider $marksheetProgressProvider
 * @var string $courseCode
 * @var string $courseName
 * @var string $courseLead
 * @var string $email
 * @var string $department
 */

use app\components\GridExport;
use kartik\grid\GridView;
use yii\web\ServerErrorHttpException;

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="allocated-courses-index">
    <div class="row">
        <div class="col-md-12 col-lg-12">
            <?php
            $assessmentNameCol = [
                'label' => 'ASSESSMENT NAME',
                'attribute' => 'assessmentName',
            ];
            $totalMarksEntered = [
                'label' => 'TOTAL MARKS ENTERED',
                'attribute' => 'totalMarksEntered',
            ];
            $ApprovedAtLecCol = [
                'label' => 'SUBMITTED BY LECTURER',
                'attribute' => 'totalMarksApprovedAtLec',
            ];
            $ApprovedAtHodCol = [
                'label' => 'APPROVED BY HOD',
                'attribute' => 'totalMarksApprovedAtHod',
            ];
            $ApprovedAtDeanCol = [
                'label' => 'APPROVED BY DEAN',
                'attribute' => 'totalMarksApprovedAtDean',
            ];

            $gridId = 'marksheet-progress';
            $title = 'Marksheet progress';
            $fileName = 'marksheet_progress';

            $contentBefore = '<br><br>';
            $contentBefore .= '<p style="color:#333333; font-weight: bold;">Course code: ' . $courseCode . '</p>';
            $contentBefore .= '<p style="color:#333333; font-weight: bold;">Course name: ' . $courseName . '</p>';
            $contentBefore .= '<p style="color:#333333; font-weight: bold;">Department : ' . $department . '</p>';
            $contentBefore .= '<p style="color:#333333; font-weight: bold;">Course lead: ' . $courseLead . '</p>';
            $contentBefore .= '<p style="color:#333333; font-weight: bold;">Email : ' . $email . '</p>';

            try {
                echo GridView::widget([
                    'id' => $gridId,
                    'dataProvider' => $marksheetProgressProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        $assessmentNameCol,
                        $totalMarksEntered,
                        $ApprovedAtLecCol,
                        $ApprovedAtHodCol,
                        $ApprovedAtDeanCol
                    ],
                    'headerRowOptions' => ['class' => 'kartik-sheet-style'],
                    'filterRowOptions' => ['class' => 'kartik-sheet-style'],
                    'pjax' => true,
                    'pjaxSettings'=>[
                        'options' => [
                            'id' => $gridId . '-pjax'
                        ]
                    ],
                    'toolbar' => [
                        '{export}'
                    ],
                    'panel' => [
                        'type' => GridView::TYPE_PRIMARY,
                        'heading'=>'<h5 class="panel-title text-dark">' . $title . '</h5>',
                        'before'=> $contentBefore
                    ],
                    'toggleDataContainer' => ['class' => 'btn-group mr-2'],
                    'toggleDataOptions' => ['minCount' => 20],
                    'exportConfig' => [
                        GridView::EXCEL => GridExport::exportExcel([
                            'filename' => $fileName,
                            'worksheet' => 'marksheets'
                        ]),
                        GridView::PDF => GridExport::exportPdf([
                            'filename' => $fileName,
                            'title' => $title,
                            'subject' => 'marksheets',
                            'keywords' => 'marksheets',
                            'contentBefore'=> $contentBefore,
                            'contentAfter'=> '',
                            'centerContent' => $title,
                        ]),
                    ],
                    'itemLabelSingle' => 'marksheet',
                    'itemLabelPlural' => 'marksheets',
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
    </div>
</div>
