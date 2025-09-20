<?php

/**
 * @author Jack Jmm<jackmutiso37@gmail.com>
 */

namespace app\helpers;


use Yii;
use DateTime;
use Exception;
use DateTimeZone;
use yii\helpers\Url;
use yii\db\Expression;
use kartik\grid\GridView;
use kartik\widgets\Growl;

class ExamSysHelperHelper
{
    public static function date($column)
    {
        $date = date('Y-m-d H:i:s') . '.000'; 
        return new \yii\db\Expression(
            "TO_TIMESTAMP(:$column, 'YYYY-MM-DD HH24:MI:SS.FF3')",
            [":$column" => $date]
        );
    }
}