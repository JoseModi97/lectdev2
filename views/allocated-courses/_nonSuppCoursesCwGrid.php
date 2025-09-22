<?php

/**
 * @author Jack Jmm
 * @email jackmutiso37@gmail.com
 * @create date 12-9-2025 20:50:22 
 * @desc 
 */

/* @var yii\web\View $this */
/* @var app\models\CourseAssignment $model */
/* @var nonSuppCoursesProvider $dataProvider */
/* @var courseAssignmentSearch $searchModel */
/* @var string $facCode */
/* @var string $gridId */
/* @var string $degreeNameColumn */
/* @var string $courseCodeColumn */
/* @var string $courseNameColumn */
/* @var string $levelOfStudyColumn */
/* @var string $semesterCodeColumn */
/* @var string $groupNameColumn */
/* @var string $academicYear */
/* @var string $academicHoursColumn */

use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Course;
use app\models\Marksheet;
use kartik\grid\GridView;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use app\models\CourseWorkAssessment;
use yii\web\ServerErrorHttpException;

include(Yii::getAlias('@views') . '/allocated-courses/_coursesColumns.php');

// dd($dataProvider->query->limit(10)->all());
$request = Yii::$app->request;
$currentUrl = $request->getUrl();
if (
    substr_count($currentUrl, 'CourseAssignmentSearch%5BacademicYear%5D') > 1 ||
    substr_count($currentUrl, 'CourseAssignmentSearch[academicYear]') > 1
) {
    $queryParams = $request->getQueryParams();
    $cleanParams = [];
    if (isset($queryParams['CourseAssignmentSearch'])) {
        $searchParams = $queryParams['CourseAssignmentSearch'];

        if (isset($searchParams['academicYear'])) {
            if (is_array($searchParams['academicYear'])) {
                $academicYears = array_filter($searchParams['academicYear'], function ($value) {
                    return !empty($value);
                });
                $searchParams['academicYear'] = !empty($academicYears) ? end($academicYears) : '';
            }
        }

        if (!empty($searchParams['academicYear'])) {
            $cleanParams['CourseAssignmentSearch']['academicYear'] = $searchParams['academicYear'];
        }

        foreach ($searchParams as $key => $value) {
            if ($key !== 'academicYear' && !empty($value)) {
                $cleanParams['CourseAssignmentSearch'][$key] = $value;
            }
        }
    }
    foreach ($queryParams as $key => $value) {
        if ($key !== 'CourseAssignmentSearch') {
            $cleanParams[$key] = $value;
        }
    }
    $cleanUrl = Url::current($cleanParams);
    if ($cleanUrl !== $currentUrl) {
        Yii::$app->response->redirect($cleanUrl);
        return;
    }
}


$availableYears = isset(Yii::$app->params['academicYears']) ? Yii::$app->params['academicYears'] : [];
$currentAcademicYear = '';
if (!empty($availableYears)) {
    $years = array_filter(array_keys($availableYears), function ($key) {
        return $key !== '';
    });

    if (!empty($years)) {
        rsort($years);
        $currentAcademicYear = $years[0];
    }
}
$defaultValue = $searchModel->academicYear ?: $currentAcademicYear;


$groupedProgrammes = [];
foreach ($programmes as $programme) {
    $name = $programme['DEGREE_NAME'];
    $code = $programme['DEGREE_CODE'];

    if (!isset($groupedProgrammes[$name])) {
        $groupedProgrammes[$name] = [];
    }
    $groupedProgrammes[$name][] = $code;
}

$formattedProgrammes = [];
foreach ($groupedProgrammes as $name => $codes) {
    if (count($codes) > 1) {
        foreach ($codes as $code) {
            $formattedProgrammes[$code] = $name . ' (' . $code . ')';
        }
    } else {
        $formattedProgrammes[$codes[0]] = $name;
    }
}




$courseworkActionColumn = [
    'class' => 'kartik\grid\ActionColumn',
    'header' => 'ACTIONS',
    'template' => '{manage-marks} {coursework} ',
    'contentOptions' => [
        'style' => 'white-space:nowrap;',
        'class' => 'kartik-sheet-style kv-align-middle'
    ],
    // 'buttons' => [

    //     'manage-marks' => function ($url, $model) {
    //         $course = Course::find()->select(['HAS_COURSE_WORK'])
    //             ->where(['COURSE_ID' => $model->marksheetDef->COURSE_ID])->one();

    //         if ($course->HAS_COURSE_WORK === 1) {
    //             $marksheetId = $model->MRKSHEET_ID;
    //             $cwModel = CourseWorkAssessment::find()->alias('CW')
    //                 ->joinWith(['assessmentType AT'])
    //                 ->where(['CW.MARKSHEET_ID' => $marksheetId])
    //                 ->andWhere(['NOT', ['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT']])
    //                 ->andWhere(['NOT', ['AT.ASSESSMENT_NAME' => 'EXAM']])
    //                 ->one();
    //             if (is_null($cwModel)) {
    //                 $linkName = '<i class="fas fa-plus"></i> Coursework';
    //                 $linkTitle = 'Create coursework';
    //                 $linkClass = 'btn btn-xs btn-spacer btn-create';
    //             } else {
    //                 $linkName = '<i class="fas fa-eye"></i> Coursework';
    //                 $linkTitle = 'View created coursework';
    //                 $linkClass = 'btn btn-xs btn-spacer';
    //             }

    //         return Html::a(
    //             '<i class="fas fa-eye"></i> Manage Marks',
    //             Url::to([
    //                 '/assessments',
    //                 'marksheetId' => $marksheetId,
    //                 'type' => 'coursework',
    //                 'section' => 'manage-marks'
    //             ]),
    //             [
    //                 'title' => 'Manage Marks',
    //                 'class' => 'btn btn-xs btn-spacer'
    //             ]
    //         );
    //         } else {
    //             return Html::button('<i class="fas fa-ban"></i> Not Available', [
    //                 'class' => 'btn btn-disabled btn-danger',
    //                 'disabled' => 'disabled'
    //             ]);
    //         }
    //     },
    //     'coursework' => function ($url, $model) {
    //         $course = Course::find()->select(['HAS_COURSE_WORK'])
    //             ->where(['COURSE_ID' => $model->marksheetDef->COURSE_ID])->one();

    //         if ($course->HAS_COURSE_WORK === 1) {
    //             $marksheetId = $model->MRKSHEET_ID;
    //             $cwModel = CourseWorkAssessment::find()->alias('CW')
    //                 ->joinWith(['assessmentType AT'])
    //                 ->where(['CW.MARKSHEET_ID' => $marksheetId])
    //                 ->andWhere(['NOT', ['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT']])
    //                 ->andWhere(['NOT', ['AT.ASSESSMENT_NAME' => 'EXAM']])
    //                 ->one();
    //             if (is_null($cwModel)) {
    //                 $linkName = '<i class="fas fa-plus"></i> Coursework';
    //                 $linkTitle = 'Create coursework';
    //                 $linkClass = 'btn btn-xs btn-spacer btn-create';
    //             } else {
    //                 $linkName = '<i class="fas fa-eye"></i> Coursework';
    //                 $linkTitle = 'View created coursework';
    //                 $linkClass = 'btn btn-xs btn-spacer';
    //             }
    //             return Html::a(
    //                 $linkName,
    //                 Url::to([
    //                     '/assessments',
    //                     'marksheetId' => $marksheetId,
    //                     'type' => 'coursework',
    //                     'section' => 'coursework'
    //                 ]),
    //                 [
    //                     'title' => $linkTitle,
    //                     'class' => $linkClass
    //                 ]
    //             );
    //         } else {
    //             return Html::button('<i class="fas fa-ban"></i> cw not required', [
    //                 'class' => 'btn btn-disabled btn-danger',
    //                 'disabled' => 'disabled'
    //             ]);
    //         }
    //     },
    // ]
    'buttons' => [
        'manage-marks' => function ($url, $model) {
            $course = Course::find()->select(['HAS_COURSE_WORK'])
                ->where(['COURSE_ID' => $model->marksheetDef->COURSE_ID])->one();
            if ($course->HAS_COURSE_WORK === 1) {
                $marksheetId = $model->MRKSHEET_ID;
                $cwModel = CourseWorkAssessment::find()->alias('CW')
                    ->joinWith(['assessmentType AT'])
                    ->where(['CW.MARKSHEET_ID' => $marksheetId])
                    ->andWhere(['NOT', ['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT']])
                    ->andWhere(['NOT', ['AT.ASSESSMENT_NAME' => 'EXAM']])
                    ->one();
                if (is_null($cwModel)) {
                    $linkName = '<i class="fas fa-plus text-success"></i> Coursework';
                    $linkTitle = 'Create coursework';
                    $linkClass = 'btn btn-xs btn-spacer btn-create';
                } else {
                    $linkName = '<i class="fas fa-eye text-info"></i> Coursework';
                    $linkTitle = 'View created coursework';
                    $linkClass = 'btn btn-xs btn-spacer';
                }


                $count = Marksheet::find()->where(['MRKSHEET_ID' => $marksheetId])->count();


                return Html::a(
                    '<i class="fas fa-cogs text-primary"></i>Coursework Marks (' . $count . ')',
                    Url::to([
                        '/assessments',
                        'marksheetId' => $marksheetId,
                        'type' => 'coursework',
                        'section' => 'manage-marks'
                    ]),
                    [
                        'title' => 'Manage Marks',
                        'class' => 'btn btn-xs btn-spacer text-primary'
                    ]
                );
            } else {
                return Html::button('<i class="fas fa-ban text-danger"></i> Not Available', [
                    'class' => 'btn btn-disabled btn-danger',
                    'disabled' => 'disabled'
                ]);
            }
        },
        'coursework' => function ($url, $model) {
            $course = Course::find()->select(['HAS_COURSE_WORK'])
                ->where(['COURSE_ID' => $model->marksheetDef->COURSE_ID])->one();


            $count = Marksheet::find()->where(['MRKSHEET_ID' => $model->MRKSHEET_ID])->count();

            if ($course->HAS_COURSE_WORK === 1) {
                $marksheetId = $model->MRKSHEET_ID;
                $cwModel = CourseWorkAssessment::find()->alias('CW')
                    ->joinWith(['assessmentType AT'])
                    ->where(['CW.MARKSHEET_ID' => $marksheetId])
                    ->andWhere(['NOT', ['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT']])
                    ->andWhere(['NOT', ['AT.ASSESSMENT_NAME' => 'EXAM']])
                    ->one();
                if (is_null($cwModel)) {
                    if ($count <= 0) {
                        $linkClass = 'btn btn-xs btn-spacer text-success disabled ';
                    } else {
                        $linkClass = 'btn btn-xs btn-spacer btn-create text-success';
                    }


                    $linkName = '<i class="fas fa-plus text-success"></i> Coursework';
                    $linkTitle = 'Create coursework';
                } else {
                    $linkName = '<i class="fas fa-eye text-info"></i> Coursework';
                    $linkTitle = 'View created coursework';
                    $linkClass = 'btn btn-xs btn-spacer text-info';
                }
                return Html::a(
                    $linkName,
                    Url::to([
                        '/assessments',
                        'marksheetId' => $marksheetId,
                        'type' => 'coursework',
                        'section' => 'coursework'
                    ]),
                    [
                        'title' => $linkTitle,
                        'class' => $linkClass,
                        'style' => 'border: none !important;'
                    ]
                );
            } else {
                return Html::button('<i class="fas fa-ban text-warning"></i> cw not required', [
                    'class' => 'btn btn-disabled btn-danger',
                    'disabled' => 'disabled'
                ]);
            }
        },
    ]
];

try {
    echo '<div class="filter-section">';
    echo Html::beginForm(Url::current([]), 'get', [
        'id' => 'academic-year-filter-form',
        'class' => 'form-inline',
        'data-pjax' => true
    ]);

    echo '<div class="filter-container">';

    echo '<div class="form-group filter-form-group">';
    echo Html::label('Academic Year:', 'academic-year-filter', ['class' => 'filter-label']);
    echo Select2::widget([
        'name' => 'CourseAssignmentSearch[academicYear]',
        'value' => $defaultValue,
        'data' => array_merge(['' => 'All Academic Years'], $availableYears),
        'options' => [
            'id' => 'academic-year-filter',
            'placeholder' => 'Select Academic Year...',
        ],
        'pluginOptions' => [
            'allowClear' => false,
            'escapeMarkup' => new \yii\web\JsExpression('function (markup) { return markup; }'),
        ]
    ]);
    echo '</div>';

    echo '<div class="form-group filter-form-group">';
    echo Html::label('Programme:', 'programme-filter', ['class' => 'filter-label']);

    $formattedProgrammes = [];
    foreach ($programmes as $programme) {
        $key = $programme['DEGREE_CODE'];
        $display = $programme['DEGREE_NAME'] . ' (' . $programme['DEGREE_CODE'] . ')';
        $formattedProgrammes[$key] = $display;
    }

    echo Select2::widget([
        'name' => 'CourseAssignmentSearch[programme]',
        'value' => $searchModel->programme ?: '',
        'data' => array_merge(['' => 'All Programmes'], $formattedProgrammes),
        'options' => [
            'id' => 'programme-filter',
            'placeholder' => 'Select Programme...',
            'style' => 'width: 100%; min-width: 300px !important;',
        ],
        'pluginOptions' => [
            'allowClear' => true,
            'escapeMarkup' => new \yii\web\JsExpression('function (markup) { return markup; }'),
            'width' => '100%',
        ]
    ]);
    echo '</div>';


    echo Html::submitButton('<i class="fas fa-filter"></i> Filter', [
        'class' => 'btn btn-primary btn-sm',
        'style' => 'display: none;'
    ]);

    echo Html::a(
        '<i class="fas fa-times"></i> Clear All Filters',
        Url::to(['/allocated-courses/index?CourseAssignmentSearch[academicYear]=' . $defaultValue]),
        ['class' => 'btn btn-outline-secondary btn-sm filter-clear-btn']
    );

    echo '</div>';
    echo Html::endForm();
    echo '</div>';

    echo GridView::widget([
        'id' => $gridId,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'headerRowOptions' => ['class' => 'kartik-sheet-style'],
        'filterRowOptions' => ['class' => 'kartik-sheet-style'],
        'pjax' => true,
        'pjaxSettings' => [
            'options' => [
                'id' => $gridId . '-pjax',
                'enablePushState' => true,
                'enableReplaceState' => true,
            ]
        ],
        'toolbar' => [
            [
                'content' =>
                Html::button('<i class="fas fa-sync"></i> Refresh', [
                    'class' => 'btn btn-outline-secondary btn-sm',
                    'onclick' => 'window.location.href = "' . Url::current([]) . '";'
                ]) . ' ' .
                    Html::button('<i class="fas fa-expand"></i> Expand All Groups', [
                        'class' => 'btn btn-outline-info btn-sm',
                        'onclick' => '$("#' . $gridId . '").find("[data-toggle=\'collapse\']").click();'
                    ])
            ],
            '{toggleData}'
        ],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<div class="panel-heading-custom h3"><i class="fas fa-chalkboard-teacher"></i> My Course Allocations' .
                (!empty($searchModel->academicYear) ? ' for ' . $searchModel->academicYear : '') .
                ' | Coursework Management</div>',
            // 'before' =>  '<div class="panel-heading-custom h3"><i class="fas fa-chalkboard-teacher"></i> My Course Allocations' .
            //     (!empty($searchModel->academicYear) ? ' for ' . $searchModel->academicYear : '') .
            //     ' | Coursework Management</div>',
            'footer' => '<div class="text-center"><small class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        Courses are grouped by Programme ? Level & Semester for better organization
                        </small></div>'
        ],
        'persistResize' => false,
        'toggleDataContainer' => ['class' => 'btn-group mr-2'],
        'toggleDataOptions' => ['minCount' => 10],
        'itemLabelSingle' => 'course',
        'itemLabelPlural' => 'courses',
        'showPageSummary' => true,
        'pageSummaryRowOptions' => ['class' => 'kv-page-summary info'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            $programmeGroupColumn,
            $courseCodeColumn,
            $courseNameColumn,
            $levelOfStudyColumn,
            $groupNameColumn,
            $courseworkActionColumn,
        ]
    ]);


    $this->registerJs("
    $(document).ready(function() {
        $('#academic-year-filter').on('change', function() {
            var form = $('#academic-year-filter-form');
            var selectedValue = $(this).val();
            
            var baseUrl = '" . Url::current([]) . "';
            var newUrl = baseUrl;
            
            if (selectedValue && selectedValue !== '') {
                var separator = baseUrl.indexOf('?') !== -1 ? '&' : '?';
                newUrl = baseUrl + separator + 'CourseAssignmentSearch%5BacademicYear%5D=' + encodeURIComponent(selectedValue);
            }
            
            window.location.href = newUrl;
        });
        
        $('#programme-filter').on('change', function() {
            var selectedProgramme = $(this).val();
            var currentUrl = new URL(window.location.href);
            var params = new URLSearchParams(currentUrl.search);
            
            params.delete('CourseAssignmentSearch[programme]');
            
            if (selectedProgramme && selectedProgramme !== '') {
                params.set('CourseAssignmentSearch[programme]', selectedProgramme);
            }
            
            var newUrl = currentUrl.pathname + '?' + params.toString();
            
            if (params.toString() === '') {
                newUrl = currentUrl.pathname;
            }
            
            window.location.href = newUrl;
        });
        
        $('.kv-grouped-row').each(function() {
            var groupText = $(this).find('td').text();
            
            if (groupText.indexOf('|') !== -1 && !groupText.includes('SEMESTER')) {
                $(this).addClass('group-programme-header');
            } 
            else if (groupText.indexOf('SEMESTER') !== -1 || groupText.indexOf('YEAR') !== -1) {
                $(this).addClass('group-level-header');
            }
            
            var nextRows = $(this).nextUntil('.kv-grouped-row');
            var count = nextRows.length;
            var headerText = $(this).find('td').html();
            $(this).find('td').html(headerText + ' <span class=\"badge badge-light ml-2\">' + count + ' course(s)</span>');
        });
    });
");
} catch (Exception $ex) {
    $message = 'Failed to create grid for allocated courses.';
    if (YII_ENV_DEV) {
        $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}
