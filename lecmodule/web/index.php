<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 15-12-2020 20:50:22 
 * @modify date 07-07-2021 20:50:22 
 * @desc [description]
 */

require __DIR__ . '/../config/const.php';
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

/**
 * We never want to halt the application execution in production. 
 * We therefore only use this method in development.
 * 
 * @param $v $v [explicite description]
 *
 * @return void
 */
function dd($v) {
    if(YII_ENV_DEV) {
        \yii\helpers\VarDumper::dump($v, 10, true);
        exit();
    }
}

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();

