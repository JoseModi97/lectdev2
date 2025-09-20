<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @date: 11/1/2023
 * @time: 11:17 AM
 */

declare(strict_types=1);

namespace app\controllers;

use app\components\SmisHelper;
use app\models\Marksheet;
use app\models\TempMarksheet;
use Exception;
use Yii;
use yii\db\Exception as dbException;
use yii\db\Expression;
use yii\web\Controller;

class CronJobsController extends Controller
{
    const MARKS_COMPLETE = 1;
    const MARKS_PUBLISHED = 1;
    const MARKS_NOT_PUBLISHED = 0;

    public function init()
    {
        parent::init();

        Yii::$app->getDb()->username = AUTO_USER;
        Yii::$app->getDb()->password = AUTO_PASS;
    }

    /**
     * Consolidate marks
     * @return void
     * @throws Exception
     */
    public function actionConsolidate()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $get = Yii::$app->request->get();
            $marksheets = [];
            if (!empty($get)) {
                foreach ($get as $id) {
                    $marksheets[] = $id;
                }
                $this->consolidateCwMarks($marksheets);
                $this->consolidateExamMarks($marksheets);
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
     * Publish the marks to smis
     * @return void
     * @throws Exception
     */
    public function actionPublish()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            SmisHelper::logMessage('Marksheets publishing started.', __METHOD__);

            $connection = Yii::$app->getDb();

            $studentsToPublishSql = "SELECT * FROM (
                SELECT
                MUTHONI.LEC_MARKSHEETS.MRKSHEET_ID,
                MUTHONI.LEC_MARKSHEETS.REGISTRATION_NUMBER,
                MUTHONI.LEC_CW_ASSESSMENT.ASSESSMENT_ID,
                MUTHONI.LEC_ASSESSMENT_TYPES.ASSESSMENT_NAME
                FROM
                MUTHONI.LEC_MARKSHEETS
                INNER JOIN MUTHONI.LEC_CW_ASSESSMENT ON MUTHONI.LEC_MARKSHEETS.MRKSHEET_ID = MUTHONI.LEC_CW_ASSESSMENT.MARKSHEET_ID
                INNER JOIN MUTHONI.LEC_ASSESSMENT_TYPES ON MUTHONI.LEC_CW_ASSESSMENT.ASSESSMENT_TYPE_ID = MUTHONI.LEC_ASSESSMENT_TYPES.ASSESSMENT_TYPE_ID
                INNER JOIN MUTHONI.LEC_STUDENT_COURSE_WORK ON MUTHONI.LEC_CW_ASSESSMENT.ASSESSMENT_ID = MUTHONI.LEC_STUDENT_COURSE_WORK.ASSESSMENT_ID
                AND MUTHONI.LEC_STUDENT_COURSE_WORK.REGISTRATION_NUMBER = MUTHONI.LEC_MARKSHEETS.REGISTRATION_NUMBER
                WHERE 
                    MUTHONI.LEC_MARKSHEETS.GRADE IS NOT NULL
                AND MUTHONI.LEC_MARKSHEETS.PUBLISH_STATUS = :notPublished
                AND MUTHONI.LEC_STUDENT_COURSE_WORK.MARK_TYPE = :markType
                AND MUTHONI.LEC_STUDENT_COURSE_WORK.LECTURER_APPROVAL_STATUS = :approvalStatus
                AND MUTHONI.LEC_STUDENT_COURSE_WORK.HOD_APPROVAL_STATUS = :approvalStatus
                AND MUTHONI.LEC_STUDENT_COURSE_WORK.DEAN_APPROVAL_STATUS = :approvalStatus
                AND MUTHONI.LEC_ASSESSMENT_TYPES.ASSESSMENT_NAME LIKE :assessmentName
                ORDER BY
                MUTHONI.LEC_MARKSHEETS.MRKSHEET_ID ASC
            ) WHERE ROWNUM <= 10000";

            $studentsToPublishParams = [
                ':notPublished' => self::MARKS_NOT_PUBLISHED,
                ':markType' => 'EXAM',
                ':approvalStatus' => 'APPROVED',
                ':assessmentName' => 'EXAM%'
            ];

            $studentsToPublish = $connection->createCommand($studentsToPublishSql)->bindValues($studentsToPublishParams)->queryAll();

            SmisHelper::logMessage('Total students marks to publish: ' . count($studentsToPublish), __METHOD__);

            foreach ($studentsToPublish as $studentToPublish) {
                $marksheetId = $studentToPublish['MRKSHEET_ID'];
                $regNumber = $studentToPublish['REGISTRATION_NUMBER'];

                $tempMarksheet = TempMarksheet::find()->where(['MRKSHEET_ID' => $marksheetId, 'REGISTRATION_NUMBER' => $regNumber])->one();

                $studentIsInMarksheet = Marksheet::find()->where(['MRKSHEET_ID' => $marksheetId, 'REGISTRATION_NUMBER' => $regNumber])->count();

                /**
                 * If a student has marks but no longer in the marksheet, update their marks complete to 1 and
                 * publish status to 1 but not push the marks to smis
                 */
                if (intval($studentIsInMarksheet) === 0) {
                    $tempMarksheet->MARKS_COMPLETE = self::MARKS_COMPLETE;
                    $tempMarksheet->PUBLISH_STATUS = self::MARKS_PUBLISHED;
                    if (!$tempMarksheet->save()) {
                        if (!empty($tempMarksheet->getErrors())) {
                            SmisHelper::logMessage(
                                'Missing ' . $regNumber . ' in ' . $marksheetId . ' failed to update publish and marks complete. ' .
                                json_encode($tempMarksheet->getErrors()), __METHOD__);
                        } else {
                            throw new Exception('Missing student failed to update publish and marks complete status.');
                        }
                    }
                    continue;
                }

                $marksheetStudent = Marksheet::find()->where(['MRKSHEET_ID' => $marksheetId, 'REGISTRATION_NUMBER' => $regNumber])->one();
                $marksheetStudent->COURSE_MARKS = $tempMarksheet->COURSE_MARKS;
                $marksheetStudent->EXAM_MARKS = $tempMarksheet->EXAM_MARKS;
                $marksheetStudent->FINAL_MARKS = $tempMarksheet->FINAL_MARKS;
                $marksheetStudent->GRADE = $tempMarksheet->GRADE;
                if (is_null($marksheetStudent->ENTRY_DATE)) {
                    $marksheetStudent->ENTRY_DATE = new Expression('CURRENT_DATE');
                }
                $marksheetStudent->LAST_UPDATE = new Expression('CURRENT_DATE');

                if ($marksheetStudent->save()) {
                    $tempMarksheet->PUBLISH_STATUS = self::MARKS_PUBLISHED;
                    if (!$tempMarksheet->save()) {
                        if (!empty($tempMarksheet->getErrors())) {
                            SmisHelper::logMessage($marksheetId . ' failed to update publish status. Errors: ' .
                                json_encode($marksheetStudent->getErrors()), __METHOD__);
                        } else {
                            throw new Exception('Marks failed to publish');
                        }
                    }
                } else {
                    if (!empty($marksheetStudent->getErrors())) {
                        SmisHelper::logMessage($marksheetId . ' not published in smis. Errors: ' .
                            json_encode($marksheetStudent->getErrors()), __METHOD__);
                    } else {
                        throw new Exception('Marks failed to publish');
                    }
                }
            }

            SmisHelper::logMessage('Marksheets publishing complete.', __METHOD__);

            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();

            $logMsg = 'Marksheets publishing stopped. Error : ' . $ex->getMessage() . ' File: ' . $ex->getFile()
                . ' Line: ' . $ex->getLine();

            SmisHelper::logMessage($logMsg, __METHOD__);

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
     */
    private function consolidateCwMarks(array $marksheets)
    {
        SmisHelper::logMessage('Coursework marks consolidation started.', __METHOD__);

        if (count($marksheets) > 0) {

            SmisHelper::logMessage('.......... process ongoing ..........', __METHOD__);

            foreach ($marksheets as $marksheetId) {
                if (!SmisHelper::marksheetExists($marksheetId)) {
                    SmisHelper::logMessage('Marksheet ' . $marksheetId . ' has its marks entered, but is not found in the table MarksheetDef.', __METHOD__);
                    continue;
                }
                SmisHelper::consolidateCwMarks($marksheetId, 'console');
            }

            SmisHelper::logMessage('Coursework marks consolidation completed.', __METHOD__);
        } else {
            SmisHelper::logMessage('Found 0 marksheets ready.', __METHOD__);
        }
    }

    /**
     * Consolidate exam marks
     * @return void
     * @throws Exception
     */
    private function consolidateExamMarks(array $marksheets)
    {
        SmisHelper::logMessage('Exam marks consolidation started.', __METHOD__);

        if (count($marksheets) > 0) {
            SmisHelper::logMessage('.......... process ongoing ..........', __METHOD__);

            foreach ($marksheets as $marksheetId) {
                if (!SmisHelper::marksheetExists($marksheetId)) {
                    SmisHelper::logMessage('Marksheet ' . $marksheetId . ' has its marks entered, but is not found in the table MarksheetDef.', __METHOD__);
                    continue;
                }

                if (SmisHelper::hasMultipleExamComponents($marksheetId)) {
                    SmisHelper::consolidateMultipleExamMarks($marksheetId, 'console');
                } else {
                    SmisHelper::consolidateSingleExamMarks($marksheetId, 'console');
                }
            }

            SmisHelper::logMessage('Exam marks consolidation completed.', __METHOD__);
        } else {
            SmisHelper::logMessage('Found 0 marksheets ready.', __METHOD__);
        }
    }

    public function actionDebugMarks($id)
    {
        $id = Yii::$app->request->get()['id'];

        // Fetch the marks data
        $marks = TempMarksheet::find()
            ->select(['REGISTRATION_NUMBER', 'EXAM_TYPE', 'GRADE', 'COURSE_MARKS', 'EXAM_MARKS',
                'FINAL_MARKS', 'MARKS_COMPLETE', 'PUBLISH_STATUS', 'COURSE_CODE'])
            ->where(['MRKSHEET_ID' => $id])
            ->asArray()
            ->all();

        // Start output buffering to capture the table as a string
        ob_start();
        ?>

        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
            <tr>
                <th>Course Code</th>
                <th>Registration Number</th>
                <th>Exam Type</th>
                <th>Course Marks</th>
                <th>Exam Marks</th>
                <th>Final Marks</th>
                <th>Grade</th>
                <th>Marks Complete</th>
                <th>Publish Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($marks as $mark): ?>
                <tr>
                    <td><?= $mark['COURSE_CODE']; ?></td>
                    <td><?= $mark['REGISTRATION_NUMBER']; ?></td>
                    <td><?= $mark['EXAM_TYPE']; ?></td>
                    <td><?= $mark['COURSE_MARKS']; ?></td>
                    <td><?= $mark['EXAM_MARKS']; ?></td>
                    <td><?= $mark['FINAL_MARKS']; ?></td>
                    <td><?= $mark['GRADE']; ?></td>
                    <td><?= $mark['MARKS_COMPLETE'] ?></td>
                    <td><?= $mark['PUBLISH_STATUS'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php
        // Get the contents of the buffer and clean it
        $tableOutput = ob_get_clean();

        // Echo the table for debugging purposes
        echo $tableOutput;
    }
}