<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc contains common functionalities for graphical class performance report
 */

/**
 * @var yii\web\View $this
 * @var string $title
 * @var string[] $reportDetails
 * @var string $user
 * @var string $date
 */

use app\assets\ChartjsAsset;

chartjsAsset::register($this);

$this->title = $title;
$this->params['breadcrumbs'][] = [
    'label' => 'Grade analysis',
    'url' => ['/shared-reports/course-analysis']
];
$this->params['breadcrumbs'][] = $reportDetails['courseCode'];
$this->params['breadcrumbs'][] = $this->title;

