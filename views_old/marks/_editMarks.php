<?php
/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @desc UI for editing marks of an assessment for a marksheet.
 */

/**
 * @var yii\web\View $this
 * @var string $title
 * @var string $maximumMarks
 * @var string $marksheetId
 * @var string $assessmentName
 * @var app\models\StudentCoursework $scModel
 * @var string $courseId
 */

use app\components\SmisHelper;
use app\models\ProjectDescription;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = $title;
$isAProjectCourse = false;
$projectTitle = '';
$projectHours = '';
try {
    if (SmisHelper::isAProjectCourse($marksheetId)) {
        $isAProjectCourse = true;
        $project = ProjectDescription::find()->where([
                'REGISTRATION_NUMBER' => $scModel->REGISTRATION_NUMBER,
                'PROJECT_CODE' => $courseId
        ])->one();

        if(!empty($project->PROJECT_TITLE)){
            $projectTitle = $project->PROJECT_TITLE;
        }

        if(!empty($project->HOURS)){
            $projectHours = $project->HOURS;
        }
    }
} catch (Exception $e) {
}
?>

<div class="edit-marks">
  <div class="form-border">
        <?php
            $form = ActiveForm::begin([
                'id' => 'edit-marks-form',
                'action' => Url::to(['/marks/update']),
                'enableAjaxValidation' => false,
                'options' => ['enctype'=>'multipart/form-data']
            ]);
        ?>
        <div class="row">
          <div id="edit-marks-loader"></div>
        </div>

        <input name="COURSE-WORK-ID" type="hidden" readonly="" class="form-control-plaintext"
          value="<?=$scModel->COURSE_WORK_ID;?>"/>

        <div class="form-group">
        <label for="current-marks" class="form-label mt-4">Current Marks</label>
        <input name="CURRENT-MARKS" type="text" readonly="" class="form-control" id="current-marks"
        value="<?=$scModel->RAW_MARK;?>"/>
        </div>

        <div class="form-group">
            <label for="rawmarks" class="form-label mt-4">New Marks</label>
            <input name="MARKS" type="number" class="form-control" id="rawmarks"
              value="<?=$scModel->RAW_MARK;?>" step="0.01" min="0" max="<?=$maximumMarks;?>"
              aria-describedby="rawmarksHelp" oninput="validity.valid||(value='');" />
            <small id="rawmarksHelp" class="form-text text-muted">Marks must not exceed the maximum: <?=$maximumMarks;?></small>
        </div>

        <?php if($assessmentName === 'EXAM'):
            if ($isAProjectCourse):?>
                <div class="form-group">
                    <label for="project-hours" class="form-label mt-4">Project Hours</label>
                    <input name="HOURS" type="number" class="form-control" id="project-hours"
                        value="<?=$projectHours;?>" step="1" min="0" oninput="validity.valid||(value='');" />
                </div>
                <div class="form-group">
                    <label for="project-title" class="form-label mt-4">Project Title</label>
                    <input name="TITLE" type="text" class="form-control" id="project-title" value="<?=$projectTitle;?>"/>
                </div>
        <?php endif;
        endif;?>

        <div class="form-group">
            <label for="remarks" class="form-label mt-4">Remarks</label>
            <input name="REMARKS" type="text" class="form-control" id="remarks" value="<?=$scModel->REMARKS;?>"/>
        </div>

        <div class="form-group">
          <?= Html::submitButton('Update', ['id' => 'edit-marks-btn','class'=>'btn form-control'])?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<!-- START PAGE CSS AND JS-->
<?php
$editassessmentMarkScript = <<< JS
    $('#edit-marks-btn').click(function(e){
        $('#edit-marks-loader')
            .html('<h1 class="text-center text-primary" style="font-size: 100px;"><i class="fas fa-spinner fa-pulse"></i></h1>');
    });   
JS;
$this->registerJs($editassessmentMarkScript, yii\web\View::POS_END);


