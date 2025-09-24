<?php

namespace app\assets;

use yii\web\AssetBundle;

class ChartjsAsset extends AssetBundle
{
    public $sourcePath = '@app/web/js/chartjs';
    public $js = [
        'Chart.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        // Assuming Bootstrap 5 is used based on other asset bundles in composer.json
        'yii\bootstrap5\BootstrapAsset',
    ];
}
