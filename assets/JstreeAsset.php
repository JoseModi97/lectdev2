<?php

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * Js Tree asset bundle.
 *
 * @author Jack jmm <jackmutiso37@gmail.come>
 * @since 2.0
 */
class JstreeAsset extends AssetBundle
{
    public $sourcePath = '@vendor/vakata/jstree/dist/';
    //public $sourcePath = '@npm/chart.js/dist';

    public $depends = [
        YiiAsset::class,
    ];

    public $cssOptions = array(
        'position' => \yii\web\View::POS_HEAD
    );

    public $css = [
        'themes/default/style.css',
    ];

    public $js = [
        'jstree.js',
    ];

    public $publishOptions = [];
}
