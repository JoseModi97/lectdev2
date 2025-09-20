<?php
/**
 * @date: 7/31/2025
 * @time: 8:42 AM
 */

namespace app\commands\controllers;

use app\components\SmisHelper;
use Yii;
use yii\db\Expression;

class TestController extends BaseController
{
    /**
     * Simple database query test
     */
    public function actionMysql(): int
    {
        try {
            $db = Yii::$app->get('db2');

            // Simple test - just check if we can connect and get server info
            $version = $db->createCommand('SELECT VERSION() as version')->queryOne();

            $this->stdout("=== Database Connection Test ===\n", \yii\helpers\Console::BOLD);
            $this->stdout("Status: ", \yii\helpers\Console::BOLD);
            $this->stdout("Connected Successfully\n", \yii\helpers\Console::FG_GREEN);
            $this->stdout("MySQL Version: " . $version['version'] . "\n");
            $this->stdout("Database Name: " . $db->createCommand('SELECT DATABASE()')->queryScalar() . "\n");
            $this->stdout("Current Time: " . $db->createCommand('SELECT NOW()')->queryScalar() . "\n");

            return 0; // Success exit code

        } catch (\Exception $e) {
            $this->stdout("=== Database Connection Test ===\n", \yii\helpers\Console::BOLD);
            $this->stdout("Status: ", \yii\helpers\Console::BOLD);
            $this->stdout("Connection Failed\n", \yii\helpers\Console::FG_RED);
            $this->stdout("Error: " . $e->getMessage() . "\n", \yii\helpers\Console::FG_RED);

            return 1; // Error exit code
        }
    }

    public function actionCount()
    {
        $student = \app\models\SPMarksheet::find()->where([
            'marksheet_id' => '2024/2025_X75_4_2_10_XET402_0048',
            'registration_number' => 'X75/0379/2021'
        ])->one();

        $student->course_work = 30;
        $student->final_grade = 'B';
        $student->course_remarks = 'Good';
        $student->last_update = new Expression('CURRENT_DATE');
        $student->publish_status = 1;
        $student->publish_date = new Expression('CURRENT_DATE');
        $student->save();

        $after = \app\models\SPMarksheet::find()->where([
            'marksheet_id' => '2024/2025_X75_4_2_10_XET402_0048',
            'registration_number' => 'X75/0379/2021'
        ])->asArray()->one();

        $this->dd($after);
    }

    public function actionPublish()
    {
        $transaction = Yii::$app->db->beginTransaction();
        $spTransaction = Yii::$app->db2->beginTransaction();
        try {

            $marksheetId = '2021/2022_B66_3_1_20_BQS301_0083';

            SmisHelper::publishMarks($marksheetId);

            $transaction->commit();
            $spTransaction->commit();

        } catch (\Exception $ex) {
            $transaction->rollBack();
            $spTransaction->rollBack();
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }

            $this->stdout("Error: " . $message . "\n", \yii\helpers\Console::FG_RED);
        }
    }
}