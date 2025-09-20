<?php


namespace app\assets;

use yii\web\View;

class ChartjsAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@npm/chart.js/dist';

    public $css = [
        'Chart.min.css'
    ];

    public $js = [
        'Chart.bundle.min.js'
    ];

    public $cssOptions = [
        'position' => View::POS_HEAD
    ];

    public $jsOptions = [
        'position' => View::POS_END
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}