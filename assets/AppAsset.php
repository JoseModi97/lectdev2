<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;
use yii\bootstrap5\BootstrapAsset;
use yii\web\YiiAsset;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'css/main.css',
    ];
    public $js = [
        'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js',
        'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/additional-methods.min.js',
        'https://unpkg.com/axios@1.0.0/dist/axios.min.js',
        'js/main.js',
    ];
    // public $depends = [
    //     'yii\web\YiiAsset',
    //     'yii\bootstrap5\BootstrapAsset'
    // ];
    public $depends = [
        YiiAsset::class,
        BootstrapAsset::class,
    ];
}
