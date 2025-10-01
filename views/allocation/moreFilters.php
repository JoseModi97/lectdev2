<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Url;
use app\models\Semester;
use app\models\LevelOfStudy;
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
    <?php /* levelOfStudy now rendered visibly below; remove hidden duplicate */ ?>
    <?php /* semester now rendered visibly below; remove hidden duplicate */ ?>
    <?= $form->field($filter, 'purpose')->hiddenInput()->label(false) ?>

    <div class="card shadow-sm rounded-0">
        <div class="card-header" style="background-image: linear-gradient(#455492, #304186, #455492);">
            <h5 class="m-0 float-start text-white">More Filters</h5>
        </div>
        <div class="card-body row g-3">
            <div class="col-md-4">
                <?= $form->field($filter, 'academicYear')->widget(Select2::class, [
                    'data' => Yii::$app->params['academicYears'] ?? [],
                    'options' => ['placeholder' => 'Select Academic Year...'],
                    'pluginOptions' => ['allowClear' => true],
                ]) ?>
            </div>
            <div class="col-md-4">
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
                    $semDescMap = []; 
                    foreach ($rows as $row) { 
                        $code = $row['SEMESTER_CODE'] ?? ''; 
                        $desc = $row['SEMESTER_DESC'] ?? ''; 
                        $descCode = $row['DESCRIPTION_CODE'] ?? ''; 
                        if ($code) { 
                            $semesterOptions[$code] = trim($code . ($desc ? ' - ' . strtoupper($desc) : '')); 
                            if ($descCode) { $semDescMap[$code] = $descCode; } 
                        } 
                    } 
                } 
                echo $form->field($filter, 'semester')->widget(Select2::class, [ 
                    'data' => $semesterOptions, 
                    'options' => ['placeholder' => 'Select Semester...', 'id' => 'filter-semester'], 
                    'pluginOptions' => ['allowClear' => true], 
                ])->label('Semester'); 
                echo $form->field($filter, 'semesterDesc')->hiddenInput()->label(false); 
                $mapJson = isset($semDescMap) ? json_encode($semDescMap) : json_encode([]);
                $js = <<<JS
(function(){
    var map = $mapJson;
    var sel = $('#filter-semester');
    var hid = $('input[name="CourseAllocationFilter[semesterDesc]"]');
    var sync = function(){
        var v = sel.val();
        hid.val(map[v] || '');
    };
    sel.on('change', sync);
    sync();
})();
JS;
                $this->registerJs($js);
                ?> 
            </div>
            <div class="col-md-4">
                <?php
                // Academic Level (Level of Study) options based on current dataset
                $levelOptions = [];
                if (!empty($filter->academicYear) && !empty($deptCode)) {
                    $lvlQuery = \app\models\AllocationRequest::find()->alias('AR')
                        ->select(['SM.LEVEL_OF_STUDY AS LEVEL_OF_STUDY', 'LVL.NAME AS NAME'])
                        ->joinWith(['marksheet.semester SM' => function(\yii\db\ActiveQuery $q){
                            $q->select(['SM.SEMESTER_ID','SM.ACADEMIC_YEAR','SM.LEVEL_OF_STUDY']);
                        }], true, 'INNER JOIN')
                        ->joinWith(['marksheet.semester.levelOfStudy LVL' => function(\yii\db\ActiveQuery $q){
                            $q->select(['LVL.LEVEL_OF_STUDY','LVL.NAME']);
                        }], true, 'INNER JOIN')
                        ->where(['SM.ACADEMIC_YEAR' => $filter->academicYear]);

                    if ($filter->purpose === 'requestedCourses') {
                        $lvlQuery->andWhere(['AR.REQUESTING_DEPT' => $deptCode]);
                    } elseif ($filter->purpose === 'serviceCourses') {
                        $lvlQuery->andWhere(['AR.SERVICING_DEPT' => $deptCode]);
                    }

                    $lvlRows = $lvlQuery->distinct()->orderBy(['SM.LEVEL_OF_STUDY' => SORT_ASC])->asArray()->all();
                    foreach ($lvlRows as $row) {
                        $code = $row['LEVEL_OF_STUDY'] ?? '';
                        $name = $row['NAME'] ?? '';
                        if ($code !== '') {
                            $levelOptions[$code] = strtoupper($name ?: $code);
                        }
                    }
                }
                echo $form->field($filter, 'levelOfStudy')->widget(Select2::class, [
                    'data' => $levelOptions,
                    'options' => ['placeholder' => 'Select Level...'],
                    'pluginOptions' => ['allowClear' => true],
                ])->label('Academic level');
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
                'levelOfStudy' => '',
                'semester' => '',
                'purpose' => $filter->purpose,
            ]], ['class' => 'btn btn-outline-secondary px-4']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
