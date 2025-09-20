<?php


namespace app\assets;

use yii\web\View;

class JSTreeAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/vakata/jstree/dist/';

    public $css = [
        'themes/default/style.min.css',
    ];

    public $js = [
        'jstree.js',
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