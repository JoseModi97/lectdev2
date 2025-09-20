<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @date: 7/15/2025
 * @time: 8:15 AM
 */

namespace app\commands\controllers;

use app\components\SmisHelper;
use app\models\Marksheet;
use app\models\StudentCourse;
use Exception;
use Yii;
use yii\db\Expression;

class AutoPublishController extends BaseController
{
    /**
     * @throws Exception
     */
    public function actionIndex()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $marksheetId = '2021/2022_B66_3_1_20_BQS301_0083';

            SmisHelper::logMessage('Publishing to student courses started...' . $marksheetId, __METHOD__);

            SmisHelper::pushToStudentCourses($marksheetId);

            $transaction->commit();

        } catch (Exception $ex) {
            $transaction->rollBack();

            $logMsg = 'Marksheets publishing to student courses stopped. Error : ' . $ex->getMessage() . ' File: ' . $ex->getFile()
                . ' Line: ' . $ex->getLine();

            SmisHelper::logMessage($logMsg, __METHOD__);

            exit();
        }
    }
}