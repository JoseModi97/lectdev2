<?php

use app\models\DegreeProgramme;
use app\models\Group;
use app\models\LevelOfStudy;
use app\models\SemesterDescription;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use app\components\BreadcrumbHelper;


/** @var yii\web\View $this */
/** @var app\models\search\SemesterSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var bool $searchPerformed */
// dd(array_column($dataProvider->getModels(), 'ACADEMIC_YEAR'));
$code = $deptCode;

echo BreadcrumbHelper::generate([
    ['label' => 'Programme Timetables']
]);
$data = $dataProvider->getModels();
$semesterSearchParams = Yii::$app->request->get('SemesterSearch', []);
$filtersForParam = Yii::$app->request->get('filtersFor');
$purpose = $semesterSearchParams['purpose'] ?? $filtersForParam ?? '';


$filtertype = [
    'nonSuppCourses' => 'Non Supplementary Courses',
    'suppCourses' => 'Supplementary Courses',
    'requestedCourses' => 'Requested Courses',
    'serviceCourses' => 'Service Courses',
];

$filterSemester = [];
foreach ($data as $model) {
    $filterSemester[$model['SEMESTER_CODE']] = $model['SEMESTER_CODE'] . ' - ' . $model['semesterDescription']['SEMESTER_DESC'];
}


$this->title = 'Semesters';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="semester-index">
    <div class="card shadow-sm rounded-0 w-100">
        <div class="card-header text-white fw-bold" style="background-image: linear-gradient(#455492, #304186, #455492);">
            Academic Filters
        </div>
        <div class=" card-body row g-3">
            <?php echo $this->render('_search', [
                'model' => $searchModel,
                'facCode' => $facCode
            ]); ?>
        </div>

    </div>
    <div class="card shadow-sm rounded-0 w-100">
        <div class="card-body row g-3">
            <h5><?= $filtertype[$filtersForParam] ?? $filtertype[$purpose] ?? '' ?></h5>
            <?php
            if (!empty($semesterSearchParams)) {
                $academicYear = $semesterSearchParams['ACADEMIC_YEAR'] ?? '';
                $degreeCode = $semesterSearchParams['DEGREE_CODE'] ?? '';
                $degree = $degreeCode !== '' ? DegreeProgramme::findOne(['DEGREE_CODE' => $degreeCode]) : null;
            ?>
                <h5>
                    <?= Html::encode($academicYear) ?>
                    <?php if ($degreeCode !== '') : ?>
                        | <?= Html::encode($degreeCode) ?>
                        <?php if ($degree) : ?>
                            | <?= Html::encode($degree->DEGREE_NAME) ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </h5>
            <?php
            }
            ?>

            <?php
            $expandColumn = [
                'class' => 'kartik\\grid\\ExpandRowColumn',
                'width' => '5%',
                'value' => function () {
                    return GridView::ROW_COLLAPSED;
                },
                'detailUrl' => function ($model) use ($purpose) {
                    if (empty($purpose)) {
                        return '#';
                    }

                    $filterParameters = [
                        'purpose'      => $purpose,
                        'academicYear' => $model->ACADEMIC_YEAR,
                        'degreeCode'   => $model->DEGREE_CODE,
                        'levelOfStudy' => $model->LEVEL_OF_STUDY,
                        'group'        => $model->GROUP_CODE,
                        'semester'     => $model->SEMESTER_CODE,
                    ];

                    $gridId = 'non-supp-courses-grid-' . md5($model->SEMESTER_ID ?? serialize($filterParameters));

                    return Url::to([
                        'allocation/give-partial',
                        'gridId' => $gridId,
                        'wrap' => 0,
                        'showNotes' => 0,
                        'CourseAllocationFilter' => $filterParameters,
                    ]);
                },
                'detailRowCssClass' => 'bg-light',
                'expandOneOnly' => true,
                'expandIcon' => '<i class="fas fa-chevron-down"></i>',
                'collapseIcon' => '<i class="fas fa-chevron-up"></i>',
                'contentOptions' => ['class' => 'text-center align-middle'],
                'headerOptions' => ['class' => 'text-center align-middle'],
                'visible' => !empty($purpose),
            ];

            $columns = array_merge([
                $expandColumn,
                ['class' => 'yii\\grid\\SerialColumn'],

                [
                    'attribute' => 'LEVEL_OF_STUDY',
                    'header' => '<b>LEVEL OF STUDY</b>',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $level = LevelOfStudy::findOne(['LEVEL_OF_STUDY' => $model->LEVEL_OF_STUDY]);
                        return $level ? $level->NAME : null;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => $searchPerformed ? ArrayHelper::map(
                        LevelOfStudy::find()->where(['LEVEL_OF_STUDY' => array_unique(array_column($dataProvider->query->all(), 'LEVEL_OF_STUDY'))])->all(),
                        'LEVEL_OF_STUDY',
                        'NAME'
                    ) : false,
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filterInputOptions' => [
                        'placeholder' => 'Select level...',
                    ],
                    'group' => true,
                    'groupedRow' => true,
                ],
                [
                    'attribute' => 'SEMESTER_CODE',
                    'header' => '<b>SEMESTER CODE</b>',
                    'format' => 'raw',
                    'width' => '50%',
                    'value' => function ($model) {
                        if ($model->SEMESTER_CODE === null) {
                            return ucfirst('(not set)');
                        }

                        $semDesc = SemesterDescription::findOne(['DESCRIPTION_CODE' => $model->DESCRIPTION_CODE]);

                        $words = $model->SEMESTER_CODE . ' - ' . $semDesc['SEMESTER_DESC'];
                        return $words;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => $searchPerformed ? $filterSemester : false,
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filterInputOptions' => ['placeholder' => 'Any Semester'],

                ],
                [
                    'attribute' => 'GROUP_CODE',
                    'header' => '<b>GROUP CODE</b>',
                    'format' => 'raw',
                    'width' => '40%',
                    'group' => true,
                    'subGroupOf' => 1,
                    'value' => function ($model) {
                        $group = Group::findOne(['GROUP_CODE' => $model->GROUP_CODE]);
                        return $group ? $group->GROUP_NAME : null;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => $searchPerformed ? ArrayHelper::map(
                        Group::find()->where(['GROUP_CODE' => array_unique(array_column($dataProvider->query->all(), 'GROUP_CODE'))])->all(),
                        'GROUP_CODE',
                        'GROUP_NAME'
                    ) : false,
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],
                    'filterInputOptions' => [
                        'placeholder' => 'Select a group...',
                    ],
                ],
            ]);

            echo GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'pjax' => false,

                'responsiveWrap' => false,
                'condensed' => true,
                'hover' => true,
                'striped' => false,
                'bordered' => true,
                'columns' => $columns,
            ]);
            ?>
        </div>

    </div>

</div>

<?= $this->render('/allocation/allocationHelpers', ['deptCode' => $deptCode]); ?>