<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var app\models\CourseAnalysisFilter $filter
 * @var string $title
 */

use yii\helpers\Url;
use yii\web\View;

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;

$courseAnalysisConfig = [
    'urls' => [
        'academicYears' => Url::to(['/shared-reports/get-academic-years']),
        'programmes' => Url::to(['/shared-reports/get-programmes']),
        'levels' => Url::to(['/shared-reports/get-levels-of-study']),
        'semesters' => Url::to(['/shared-reports/get-semesters']),
        'groups' => Url::to(['/shared-reports/get-groups']),
    ],
    'selected' => [
        'academicYear' => $filter->academicYear,
        'degreeCode' => $filter->degreeCode,
        'levelOfStudy' => $filter->levelOfStudy,
        'group' => $filter->group,
        'semester' => $filter->semester,
    ],
];

$this->registerJsVar('courseAnalysisConfig', $courseAnalysisConfig, View::POS_HEAD);
$this->registerJsFile('@web/js/course-analysis.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<div class="semester-index">
    <div class="card shadow-sm rounded-0 w-100">
        <div class="card-header text-white fw-bold" style="background-image: linear-gradient(#455492, #304186, #455492);">
            Academic Filters
        </div>
        <div class="card-body row g-3">
            <?php echo $this->render('_search', [
                'model' => $filter,
            ]); ?>
        </div>
    </div>
</div>
