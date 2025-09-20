<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

namespace app\commands\controllers;

use yii\console\Controller;
use yii\helpers\VarDumper;

class BaseController extends Controller
{
    /**
     * Dump variable on console for quick debugging
     * @param $varToDump
     * @return void
     */
    protected function dd($varToDump)
    {
        VarDumper::dump($varToDump, 10);
        exit();
    }

    /**
     * Dump data on the console terminal and exit execution
     * @param $varToDump
     * @return void
     */
    protected function de($varToDump)
    {
        print_r($varToDump);
        exit();
    }
}