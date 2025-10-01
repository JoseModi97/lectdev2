<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Url;
use app\models\Semester;
use app\models\AllocationRequest;
use yii\db\ActiveQuery;

/**
 * @var yii\web\View $this
 * @var app\models\CourseAllocationFilter $filter
 * @var string|null $deptCode
 */


?>

<div class="semester-search container-fluid px-0">
    <?php $form = ActiveForm::begin([
        'action' => Url::to(['/allocation/give']),
        'method' => 'get',
    ]); ?>

    <?= $form->field($filter, 'degreeCode')->textInput()->label(false) ?>
    <?= $form->field($filter, 'group')->hiddenInput()->label(false) ?>
    <?= $form->field($filter, 'levelOfStudy')->hiddenInput()->label(false) ?>
    <?php /* semester now rendered visibly below; remove hidden duplicate */ ?>
    <?= $form->field($filter, 'purpose')->hiddenInput()->label(false) ?>

    <div class="card shadow-sm rounded-0">
        <div class="card-header" style="background-image: linear-gradient(#455492, #304186, #455492);">
            <h5 class="m-0 float-start text-white">More Filters</h5>
        </div>
        <div class="card-body row g-3">
            <div class="col-md-6">
                <?= $form->field($filter, 'academicYear')->widget(Select2::class, [
                    'data' => Yii::$app->params['academicYears'] ?? [],
                    'options' => ['placeholder' => 'Select Academic Year...'],
                    'pluginOptions' => ['allowClear' => true],
                ]) ?>
            </div>
            <div class="col-md-6">
                <?php
                $semesterOptions = [];
                if (!empty($filter->academicYear) && !empty($deptCode)) {
                    // Restrict semesters to those present in the current grid dataset
                    $query = AllocationRequest::find()->alias('AR')
                        ->select(['SM.SEMESTER_CODE AS SEMESTER_CODE', 'SD.SEMESTER_DESC AS SEMESTER_DESC'])
                        ->joinWith(['marksheet MD' => function (ActiveQuery $q) {
                            $q->select(['MD.MRKSHEET_ID', 'MD.SEMESTER_ID']);
                        }], true, 'INNER JOIN')
                        ->joinWith(['marksheet.semester SM' => function (ActiveQuery $q) {
                            $q->select(['SM.SEMESTER_ID', 'SM.SEMESTER_CODE', 'SM.ACADEMIC_YEAR', 'SM.DESCRIPTION_CODE']);
                        }], true, 'INNER JOIN')
                        ->joinWith(['marksheet.semester.semesterDescription SD' => function (ActiveQuery $q) {
                            $q->select(['SD.DESCRIPTION_CODE', 'SD.SEMESTER_DESC']);
                        }], true, 'INNER JOIN')
                        ->where(['SM.ACADEMIC_YEAR' => $filter->academicYear]);

                    if ($filter->purpose === 'requestedCourses') {
                        $query->andWhere(['AR.REQUESTING_DEPT' => $deptCode]);
                    } elseif ($filter->purpose === 'serviceCourses') {
                        $query->andWhere(['AR.SERVICING_DEPT' => $deptCode]);
                    }

                    $rows = $query->distinct()->orderBy(['SM.SEMESTER_CODE' => SORT_ASC])->asArray()->all();
                    foreach ($rows as $row) {
                        $code = $row['SEMESTER_CODE'] ?? '';
                        $desc = $row['SEMESTER_DESC'] ?? '';
                        if ($code) {
                            $semesterOptions[$code] = trim($code . ($desc ? ' - ' . strtoupper($desc) : ''));
                        }
                    }
                }
                echo $form->field($filter, 'semester')->widget(Select2::class, [
                    'data' => $semesterOptions,
                    'options' => ['placeholder' => 'Select Semester...'],
                    'pluginOptions' => ['allowClear' => true],
                ])->label('Semester');
                ?>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-start gap-2">
            <?= Html::submitButton('Search', [
                'class' => 'btn text-white px-4',
                'style' => "background-image: linear-gradient(#455492, #304186, #455492);",
            ]) ?>
            <?= Html::a('Reset', ['/allocation/give', 'CourseAllocationFilter' => [
                'academicYear' => $filter->academicYear,
                'degreeCode' => $filter->degreeCode,
                'group' => $filter->group,
                'levelOfStudy' => $filter->levelOfStudy,
                'semester' => '',
                'purpose' => $filter->purpose,
            ]], ['class' => 'btn btn-outline-secondary px-4']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>