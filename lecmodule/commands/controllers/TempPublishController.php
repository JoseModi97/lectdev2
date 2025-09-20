<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc publish marks to smis
 */

namespace app\commands\controllers;

use app\components\SmisHelper;
use app\models\Marksheet;
use app\models\TempMarksheet;
use Exception;
use Yii;
use yii\db\Expression;

final class TempPublishController extends BaseController
{
    const MARKS_COMPLETE = 1;
    const MARKS_PUBLISHED = 1;
    const MARKS_NOT_PUBLISHED = 0;

    /**
     * Publish the marks to smis
     * @return void
     * @throws Exception
     */
    public function actionIndex()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try{
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
                    MUTHONI.LEC_MARKSHEETS.MARKS_COMPLETE = :marksComplete
                AND MUTHONI.LEC_MARKSHEETS.PUBLISH_STATUS = :notPublished
                AND MUTHONI.LEC_STUDENT_COURSE_WORK.MARK_TYPE = :markType
                AND MUTHONI.LEC_STUDENT_COURSE_WORK.LECTURER_APPROVAL_STATUS = :approvalStatus
                AND MUTHONI.LEC_STUDENT_COURSE_WORK.HOD_APPROVAL_STATUS = :approvalStatus
                AND MUTHONI.LEC_STUDENT_COURSE_WORK.DEAN_APPROVAL_STATUS = :approvalStatus
                AND MUTHONI.LEC_ASSESSMENT_TYPES.ASSESSMENT_NAME LIKE :assessmentName
                ORDER BY
                MUTHONI.LEC_MARKSHEETS.MRKSHEET_ID ASC
            ) WHERE ROWNUM <= 1000";


            $studentsToPublishParams = [
                ':marksComplete' => self::MARKS_COMPLETE,
                ':notPublished' => self::MARKS_NOT_PUBLISHED,
                ':markType' => 'EXAM',
                ':approvalStatus' => 'APPROVED',
                ':assessmentName' => 'EXAM%'
            ];

            $studentsToPublish = $connection->createCommand($studentsToPublishSql)->bindValues($studentsToPublishParams)->queryAll();

            SmisHelper::logMessage('Total students marks to publish: ' . count($studentsToPublish), __METHOD__);

            foreach ($studentsToPublish as $studentToPublish){
                $marksheetId = $studentToPublish['MRKSHEET_ID'];
                $regNumber = $studentToPublish['REGISTRATION_NUMBER'];

                $tempMarksheet = TempMarksheet::find()->where(['MRKSHEET_ID' => $marksheetId, 'REGISTRATION_NUMBER' => $regNumber])->one();

                $marksheetStudent = Marksheet::find()->where(['MRKSHEET_ID' => $marksheetId, 'REGISTRATION_NUMBER' => $regNumber])->one();

                /**
                 * If a student has marks but no longer in the marksheet, update their marks complete to 1 and
                 * publish status to 1 but not push the marks to smis
                 */
                if(empty($marksheetStudent)){
                    $tempMarksheet->MARKS_COMPLETE = self::MARKS_COMPLETE;
                    $tempMarksheet->PUBLISH_STATUS = self::MARKS_PUBLISHED;
                    if(!$tempMarksheet->save()){
                        if(!empty($tempMarksheet->getErrors())){
                            SmisHelper::logMessage(
                                'Missing ' . $regNumber . ' in ' . $marksheetId . ' failed to update publish and marks complete. '.
                                json_encode($tempMarksheet->getErrors()), __METHOD__);
                        }else{
                            throw new Exception('Missing student failed to update publish and marks complete status.');
                        }
                    }
                    continue;
                }

                $marksheetStudent->COURSE_MARKS = $tempMarksheet->COURSE_MARKS;
                $marksheetStudent->EXAM_MARKS = $tempMarksheet->EXAM_MARKS;
                $marksheetStudent->FINAL_MARKS = $tempMarksheet->FINAL_MARKS;
                $marksheetStudent->GRADE = $tempMarksheet->GRADE;
                if(is_null($marksheetStudent->ENTRY_DATE)){
                    $marksheetStudent->ENTRY_DATE = new Expression('CURRENT_DATE');
                }
                $marksheetStudent->LAST_UPDATE = new Expression('CURRENT_DATE');

                if($marksheetStudent->save()){
                    $tempMarksheet->PUBLISH_STATUS = self::MARKS_PUBLISHED;
                    if(!$tempMarksheet->save()){
                        if(!empty($tempMarksheet->getErrors())){
                            SmisHelper::logMessage($marksheetId . ' failed to update publish status. Errors: '.
                                json_encode($marksheetStudent->getErrors()), __METHOD__);
                        }else{
                            throw new Exception('Marks failed to publish');
                        }
                    }
                }else{
                    if(!empty($marksheetStudent->getErrors())){
                        SmisHelper::logMessage($marksheetId . ' not published in smis. Errors: '.
                            json_encode($marksheetStudent->getErrors()), __METHOD__);
                    }else{
                        throw new Exception('Marks failed to publish');
                    }
                }
            }

            SmisHelper::logMessage('Marksheets publishing complete.', __METHOD__);

            $transaction->commit();
        }
        catch(Exception $ex){
            $transaction->rollBack();

            $logMsg = 'Marksheets publishing stopped. Error : ' . $ex->getMessage() . ' File: ' . $ex->getFile()
                . ' Line: ' . $ex->getLine();

            SmisHelper::logMessage($logMsg, __METHOD__);

            exit();
        }
    }
}
