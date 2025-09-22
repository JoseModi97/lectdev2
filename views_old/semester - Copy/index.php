<?php

use app\models\Course;
use app\models\CourseAllocationFilter;
use app\models\Group;
use app\models\LevelOfStudy;
use app\models\MarksheetDef;
use app\models\search\AllocationRequestsSearchNew;
use app\models\search\MarksheetDefAllocationSearchNew;
use app\models\Semester;
use app\models\SemesterDescription;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\search\SemesterSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var bool $searchPerformed */
// dd(array_column($dataProvider->getModels(), 'ACADEMIC_YEAR'));
$code = $deptCode;

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
            <h5><?= Html::encode($this->title) ?></h5>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'pjax' => false,

                'responsiveWrap' => false,
                'condensed' => true,
                'hover' => true,
                'striped' => false,
                'bordered' => true,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    // 'SEMESTER_ID',
                    // 'ACADEMIC_YEAR',
                    // 'DEGREE_CODE',
                    [
                        'attribute' => 'LEVEL_OF_STUDY',
                        'header' => '<b>LEVEL OF STUDY</b>',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $level = LevelOfStudy::findOne(['LEVEL_OF_STUDY' => $model->LEVEL_OF_STUDY]);
                            return $level ? Yii::$app->request->get('SemesterSearch')['ACADEMIC_YEAR'] . ' | ' . $level->NAME : null;
                        },
                        'filterType' => GridView::FILTER_SELECT2,
                        'filter' => $searchPerformed ? ArrayHelper::map(
                            LevelOfStudy::find()->all(),
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
                        // SEMESTER_CODE column
                        'attribute' => 'SEMESTER_CODE',
                        'header' => '<b>SEMESTER CODE</b>',
                        'format' => 'raw',
                        'width' => '50%',
                        'value' => function ($model) {
                            if ($model->SEMESTER_CODE === null) {
                                return ucfirst('(not set)');
                            }

                            $formatter = new NumberFormatter("en", NumberFormatter::SPELLOUT);
                            $words = $formatter->format($model->SEMESTER_CODE);
                            return ucfirst(strtolower($words));
                        },
                        'filterType' => GridView::FILTER_SELECT2,
                        'filter' => $searchPerformed ? ArrayHelper::map(
                            Semester::find()->select(['SEMESTER_CODE'])->distinct()->all(),
                            'SEMESTER_CODE', // actual value
                            function ($semester) {
                                $formatter = new NumberFormatter("en", NumberFormatter::SPELLOUT);
                                return ucfirst(strtolower($formatter->format($semester->SEMESTER_CODE)));
                            }
                        ) : false,
                        'filterWidgetOptions' => [
                            'pluginOptions' => ['allowClear' => true],
                        ],
                        'filterInputOptions' => [
                            'placeholder' => 'Select semester...',
                        ],
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
                            Group::find()->all(),  // all groups
                            'GROUP_CODE',          // the value
                            'GROUP_NAME'           // the label shown
                        ) : false,
                        'filterWidgetOptions' => [
                            'pluginOptions' => ['allowClear' => true],
                        ],
                        'filterInputOptions' => [
                            'placeholder' => 'Select a group...',
                        ],
                    ],

                    //'INTAKE_CODE',
                    //'START_DATE',
                    //'END_DATE',
                    //'FIRST_SEMESTER',
                    //'SEMESTER_NAME',
                    //'CLOSING_DATE',
                    //'ADMIN_USER',
                    //'REGISTRATION_DEADLINE',
                    //'DESCRIPTION_CODE',
                    //'SESSION_TYPE',
                    //'DISPLAY_DATE',
                    //'REGISTRATION_DATE',
                    //'SEMESTER_TYPE',
                    // [
                    //     'class' => ActionColumn::className(),
                    //     'urlCreator' => function ($action, Semester $model, $key, $index, $column) {
                    //         return Url::toRoute([$action, 'SEMESTER_ID' => $model->SEMESTER_ID]);
                    //     }
                    // ],

                    [
                        'label' => 'Action',
                        'format' => 'raw',
                        'width' => '10%',
                        'contentOptions' => ['class' => 'w-auto text-center', 'style' => 'white-space: nowrap;'], // ? let td size fit button
                        'headerOptions' => ['class' => 'w-auto text-center', 'style' => 'white-space: nowrap;'],
                        'visible' => !empty(Yii::$app->request->get('SemesterSearch')['purpose']),
                        'value' => function ($model) {
                            $filterParameters = [
                                'purpose'      => Yii::$app->request->get('SemesterSearch')['purpose'],
                                'academicYear' => $model->ACADEMIC_YEAR,
                                'degreeCode'   => $model->DEGREE_CODE,
                                'levelOfStudy' => $model->LEVEL_OF_STUDY,
                                'group'        => $model->GROUP_CODE,
                                'semester'     => $model->SEMESTER_CODE,
                            ];

                            $url = Url::to([
                                'allocation/give',
                                'CourseAllocationFilter' => $filterParameters,
                                '_csrf' => Yii::$app->request->csrfToken,
                            ]);
                            return Html::a(
                                '<b> Courses</b>',
                                $url,
                                [
                                    'title' => 'Click to apply No Supplementary filter',
                                    'class' => 'btn btn-sm text-white',
                                    'style' => "background-image: linear-gradient(#455492, #304186, #455492)",
                                ]
                            );
                        },
                    ],



                ],
            ]); ?>
        </div>

    </div>

</div>

<?php
// $filter = new CourseAllocationFilter();
// $filter->academicYear = '734';

// dd($filter->getAttributes());
function fetchMarkSheets($filterParameters, $deptCode, $purpose)
{
    $filter = new CourseAllocationFilter();
    $filter->academicYear = $filterParameters['academicYear'];
    $filter->degreeCode = $filterParameters['degreeCode'];
    $filter->group = $filterParameters['group'];
    $filter->levelOfStudy = $filterParameters['levelOfStudy'];
    $filter->semester = $filterParameters['semester'];
    $filter->purpose = $purpose;
    $semesterId = $filter->academicYear . '_' . $filter->degreeCode . '_' . $filter->levelOfStudy
        . '_' . $filter->semester . '_' . $filter->group;

    $marksheets = app\models\MarksheetDef::find()->select(['MRKSHEET_ID', 'COURSE_ID'])
        ->where(['SEMESTER_ID' => $semesterId])->asArray()->all();

    $timetableCourses = [];
    foreach ($marksheets as $marksheet) {
        $courseId = $marksheet['COURSE_ID'];
        $course = Course::find()->select(['DEPT_CODE', 'IS_COMMON'])->where(['COURSE_ID' => $courseId])->asArray()->one();

        if ($course['DEPT_CODE'] === $deptCode) {
            $timetableCourses[] = $courseId;
        }

        if (intval($course['IS_COMMON']) === 1) {
            $timetableCourses[] = $courseId;
        }
    }

    $timetableCourses = array_unique($timetableCourses);

    $query = app\models\MarksheetDef::find()
        ->select([
            'MUTHONI.MARKSHEET_DEF.MRKSHEET_ID',
            'MUTHONI.MARKSHEET_DEF.SEMESTER_ID',
            'MUTHONI.MARKSHEET_DEF.COURSE_ID',
            'MUTHONI.MARKSHEET_DEF.GROUP_CODE',
            'MUTHONI.MARKSHEET_DEF.PAYROLL_NO'
        ])
        ->where(['MUTHONI.MARKSHEET_DEF.SEMESTER_ID' => $semesterId])
        ->andWhere(['IN', 'MUTHONI.MARKSHEET_DEF.COURSE_ID', $timetableCourses])
        ->joinWith(['semester' => function ($q) {
            $q->select([
                'MUTHONI.SEMESTERS.SEMESTER_ID',
                'MUTHONI.SEMESTERS.SEMESTER_CODE',
                'MUTHONI.SEMESTERS.DEGREE_CODE',
                'MUTHONI.SEMESTERS.ACADEMIC_YEAR',
                'MUTHONI.SEMESTERS.LEVEL_OF_STUDY',
                'MUTHONI.SEMESTERS.DESCRIPTION_CODE',
                'MUTHONI.SEMESTERS.SEMESTER_TYPE'
            ]);
        }], true, 'INNER JOIN');
    if ($filter->purpose === 'nonSuppCourses') {
        $query->andWhere(['NOT', ['MUTHONI.SEMESTERS.SEMESTER_TYPE' => 'SUPPLEMENTARY']]);
    } elseif ($filter->purpose === 'suppCourses') {
        $query->andWhere(['MUTHONI.SEMESTERS.SEMESTER_TYPE' => 'SUPPLEMENTARY']);
    }

    $query->joinWith(['semester.degreeProgramme' => function ($q) {
        $q->select([
            'MUTHONI.DEGREE_PROGRAMMES.DEGREE_CODE',
            'MUTHONI.DEGREE_PROGRAMMES.DEGREE_NAME'
        ]);
    }], true, 'INNER JOIN')
        ->joinWith(['semester.semesterDescription DESC' => function ($q) {
            $q->select([
                'DESC.DESCRIPTION_CODE',
                'DESC.SEMESTER_DESC'
            ]);
        }], true, 'INNER JOIN')
        ->joinWith(['group GR' => function ($q) {
            $q->select([
                'GR.GROUP_CODE',
                'GR.GROUP_NAME'
            ]);
        }], true, 'INNER JOIN')
        ->joinWith(['course' => function ($q) {
            $q->select([
                'MUTHONI.COURSES.COURSE_ID',
                'MUTHONI.COURSES.COURSE_CODE',
                'MUTHONI.COURSES.COURSE_NAME',
                'MUTHONI.COURSES.DEPT_CODE'
            ]);
        }], true, 'INNER JOIN');

    if (!empty($courseFilter->courseCode)) {
        $query->andWhere(['like', 'MUTHONI.COURSES.COURSE_CODE', $filter->courseCode]);
    }

    if (!empty($courseFilter->courseName)) {
        $query->andWhere(['like', 'MUTHONI.COURSES.COURSE_NAME', $filter->courseName]);
    }

    $query->joinWith(['course.dept' => function ($q) {
        $q->select([
            'MUTHONI.DEPARTMENTS.DEPT_CODE',
            'MUTHONI.DEPARTMENTS.DEPT_NAME',
            'MUTHONI.DEPARTMENTS.FAC_CODE'
        ]);
    }], true, 'INNER JOIN')
        ->orderBy(['MUTHONI.DEGREE_PROGRAMMES.DEGREE_NAME' => SORT_DESC]);


    return count($query->all());
}
?>