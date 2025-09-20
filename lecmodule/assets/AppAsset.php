<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        // main styles
        'css/main.css',
    ];
    public $js = [
        // Jquery validation
        'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js',
        'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/additional-methods.min.js',
        // Axios
//        'https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js',
        'https://unpkg.com/axios@1.0.0/dist/axios.min.js',
        // Html2pdf
        'js/html2pdf.bundle.min.js',
        // JStree
        'js/jstree.setup.js',
        // main js script
        'js/main.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'raoul2000\bootswatch\BootswatchAsset',
    ];
}