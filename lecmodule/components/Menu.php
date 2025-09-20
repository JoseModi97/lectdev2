<?php
/**
 * Created by PhpStorm.
 * User: Anthony
 * Date: 05/03/2018
 * Time: 4:25 PM
 */

namespace app\components;

use yii\helpers\Html;
use yii\helpers\Url;

class Menu
{
    /**
     * @param $name
     * @param array $link
     * @param string $icon
     * @return string
     */
    public static function nodeGen($name, $link=[], $icon='options'){
        $url = Url::to($link);
        $cnt = '<li data-jstree=\'{"type":"'.$icon.'"}\'>
                    '.Html::a($name,$url).'
                </li>';
        return $cnt;
    }
}