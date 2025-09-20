<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @date: 9/11/2024
 * @time: 2:16 PM
 */

namespace app\commands\controllers;

set_time_limit(0);

use app\components\SmisHelper;
use Exception;
use Yii;
use yii\db\Exception as dbException;

class DebugController extends BaseController
{
    /**
     * Consolidate marks
     * @return void
     * @throws Exception
     */
    public function actionConsolidate()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $marksheets = [];

            foreach ($marksheets as $marksheet) {
                SmisHelper::logMessage('--------------------------' . $marksheet . '--------------------------', __METHOD__);

                $this->consolidateCwMarks($marksheet);

                $this->consolidateExamMarks($marksheet);
            }

            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();

            $logMsg = 'Marks consolidation stopped. Error : ' . $ex->getMessage() . ' File: ' . $ex->getFile()
                . ' Line: ' . $ex->getLine();

            SmisHelper::logMessage($logMsg, __METHOD__, 'error');

            exit();
        }
    }

    /**
     * For each student registered in a marksheet, consolidate marks in each assessment,
     * convert out of the cw ratio and save the marks in the temp marksheet.
     * Update the processed as final flag for each student_cw record processed.
     * @return void
     * @throws dbException
     * @throws Exception
     * @throws Exception
     */
    private function consolidateCwMarks(string $marksheet)
    {
        SmisHelper::logMessage('consolidating cw', __METHOD__);
        if (SmisHelper::marksheetExists($marksheet)) {
            SmisHelper::consolidateCwMarks($marksheet, 'console');
        } else {
            SmisHelper::logMessage('marksheet not found ', __METHOD__);
        }
    }

    /**
     * Consolidate exam marks
     * @return void
     * @throws Exception
     */
    private function consolidateExamMarks(string $marksheet)
    {
        SmisHelper::logMessage('consolidating exam', __METHOD__);
        if (SmisHelper::marksheetExists($marksheet)) {
            if (SmisHelper::hasMultipleExamComponents($marksheet)) {
                SmisHelper::consolidateMultipleExamMarks($marksheet, 'console');
            } else {
                SmisHelper::consolidateSingleExamMarks($marksheet, 'console');
            }
        } else {
            SmisHelper::logMessage('marksheet not found ', __METHOD__);
        }
    }
}