<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc Show pending/approved courses
 */

/**
 * @var yii\web\View $this
 * @var app\models\MarksheetDef $model
 * @var yii\data\ActiveDataProvider $pendingCoursesDataProvider
 * @var yii\data\ActiveDataProvider $approvedCoursesDataProvider
 * @var app\models\search\CoursesToApproveSearch $pendingCoursesSearchModel
 * @var app\models\search\CoursesToApproveSearch $approvedCoursesSearchModel
 * @var string $pendingPanelHeading
 * @var string $approvedPanelHeading
 * @var string $title
 * @var app\models\MarksApprovalFilter $filter
 * @var string $filtersInterface
 * @var string $resultsType
 */

use kartik\tabs\TabsX;
use yii\helpers\Html;
use yii\web\ServerErrorHttpException;

$this->title = $title;
$this->params['breadcrumbs'][] = [
    'label' => 'Course filters',
    'url' => ['/marks-approval/new-filters', 'level' => $filter->approvalLevel, 'filtersInterface' => $filtersInterface]
];
$this->params['breadcrumbs'][] = $this->title;

$pendingCourses = $this->render('pendingCoursesGrid', [
    'gridId' => 'pending-courses-grid',
    'dataProvider' => $pendingCoursesDataProvider,
    'searchModel' => $pendingCoursesSearchModel,
    'panelHeading' => $pendingPanelHeading,
    'filter' => $filter,
    'filtersInterface' => $filtersInterface,
    'resultsType' => $resultsType
]);

$approvedCourses = $this->render('approvedCoursesGrid', [
    'gridId' => 'approved-courses-grid',
    'dataProvider' => $approvedCoursesDataProvider,
    'searchModel' => $approvedCoursesSearchModel,
    'panelHeading' => $approvedPanelHeading,
    'filter' => $filter,
    'filtersInterface' => $filtersInterface,
    'resultsType' => $resultsType
]);

$items = [
    [
        'label' => '<i class="glyphicon glyphicon-menu-down"></i> <strong>Pending</strong>',
        'content' => $pendingCourses,
        'options' => [
            'id' => 'pending-courses-tab'
        ]
    ],
    [
        'label' => '<i class="glyphicon glyphicon-menu-down"></i> <strong>Approved</strong>',
        'content' => $approvedCourses,
        'options' => [
            'id' => 'approved-courses-tab'
        ]
    ]
];
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading"><b>Please note the following</b></div>
            <div class="panel-body">
                <p>
                    1. If these reports are not current, update them using the
                    <?= Html::a('View consolidated marks', '#', ['class' => 'btn btn-xs'])?>
                    link for the course's assessments/exam. It is found on the pages used to enter and edit or approve marks.
                </p>
            </div>
        </div>
    </div>
</div>

<?php
if($filtersInterface === '1'){
    echo $this->render('courseToApproveUi1ActiveFilters', ['filter' => $filter]);
    echo $this->render('coursesToApproveUi1MoreFilters', ['filter' => $filter, 'filtersInterface' => $filtersInterface]);
}else{
    echo $this->render('courseToApproveUi2ActiveFilters', ['filter' => $filter]);
    echo $this->render('coursesToApproveUi2MoreFilters', ['filter' => $filter, 'filtersInterface' => $filtersInterface]);
}

try {
    echo TabsX::widget([
        'items' => $items,
        'position' => TabsX::POS_ABOVE,
        'encodeLabels' => false,
        'options' => ['class' => 'nav nav-pills'],
        'pluginOptions' => ['class' => 'nav nav-invert']
    ]);
} catch (Exception $ex) {
    $message = $ex->getMessage();
    if(YII_ENV_DEV){
        $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}

// Loader to show user
echo $this->render('../marks/_appIsLoading');