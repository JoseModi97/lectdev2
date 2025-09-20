<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc Consolidate student marks
 */

namespace app\commands\controllers;

use app\components\SmisHelper;
use app\models\CourseWorkAssessment;
use app\models\StudentCoursework;
use Exception;
use Yii;
use yii\db\Exception as dbException;

class ConsolidateController extends BaseController
{
    const MARKS_NOT_CONSOLIDATED = 0;

    /**
     * Consolidate marks
     * @return void
     * @throws Exception
     */
    public function actionIndex()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $this->consolidateCwMarks();
            $this->consolidateExamMarks();
            $transaction->commit();
        } catch(Exception $ex){
            $transaction->rollBack();

            $logMsg = 'Marks consolidation stopped. Error : ' . $ex->getMessage() . ' File: ' . $ex->getFile()
                . ' Line: ' . $ex->getLine();

            SmisHelper::logMessage($logMsg, __METHOD__, 'error');

            exit();
        }
    }

    /**
     * Consolidate course work marks
     * @return void
     * @throws Exception
     */
    public function actionCourseWork()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $this->consolidateCwMarks();
            $transaction->commit();
        } catch(Exception $ex){
            $transaction->rollBack();

            $logMsg = 'Course work marks consolidation stopped. Error : ' . $ex->getMessage() . ' File: ' . $ex->getFile()
                . ' Line: ' . $ex->getLine();

            SmisHelper::logMessage($logMsg, __METHOD__, 'error');

            exit();
        }
    }

    /**
     * Consolidate exam marks
     * @return void
     * @throws Exception
     */
    public function actionExam()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $this->consolidateExamMarks();
            $transaction->commit();
        } catch(Exception $ex){
            $transaction->rollBack();

            $logMsg = 'Exam marks consolidation stopped. Error : ' . $ex->getMessage() . ' File: ' . $ex->getFile()
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
     */
    private function consolidateCwMarks()
    {
        SmisHelper::logMessage('Coursework marks consolidation started.', __METHOD__);

        $marksheets = $this->getMarksheetsReadyForGrading('ASSESSMENT');

        if(count($marksheets) > 0){

            SmisHelper::logMessage('.......... process ongoing ..........', __METHOD__);

            foreach ($marksheets as $marksheetId){
                if(!SmisHelper::marksheetExists($marksheetId)){
                    SmisHelper::logMessage('Marksheet ' . $marksheetId . ' has its marks entered, but is not found in the table MarksheetDef.', __METHOD__);
                    continue;
                }
                SmisHelper::consolidateCwMarks($marksheetId, 'console');
            }

            SmisHelper::logMessage('Coursework marks consolidation completed.', __METHOD__);
        }else{
            SmisHelper::logMessage('Found 0 marksheets ready.', __METHOD__);
        }
    }

    /**
     * Consolidate exam marks
     * @return void
     * @throws Exception
     */
    private function consolidateExamMarks()
    {
        SmisHelper::logMessage('Exam marks consolidation started.', __METHOD__);

        $marksheets = $this->getMarksheetsReadyForGrading('EXAM');

        if(count($marksheets) > 0){
            SmisHelper::logMessage('.......... process ongoing ..........', __METHOD__);

            foreach ($marksheets as $marksheetId){
                if(!SmisHelper::marksheetExists($marksheetId)){
                    SmisHelper::logMessage('Marksheet ' . $marksheetId . ' has its marks entered, but is not found in the table MarksheetDef.', __METHOD__);
                    continue;
                }

                if(SmisHelper::hasMultipleExamComponents($marksheetId)){
                    SmisHelper::consolidateMultipleExamMarks($marksheetId, 'console');
                }else{
                    SmisHelper::consolidateSingleExamMarks($marksheetId, 'console');
                }
            }

            SmisHelper::logMessage('Exam marks consolidation completed.', __METHOD__);
        }else{
            SmisHelper::logMessage('Found 0 marksheets ready.', __METHOD__);
        }
    }

    /**
     * @param string $markType EXAM/ASSESSMENT
     * @return array marksheets to be graded
     * @throws Exception on failure
     */
    private function getMarksheetsReadyForGrading(string $markType): array
    {
        SmisHelper::logMessage('Checking for marksheets ready for grading started.', __METHOD__);

        $assessments = StudentCoursework::find()->select(['ASSESSMENT_ID'])
            ->where([
                'IS_CONSOLIDATED' => self::MARKS_NOT_CONSOLIDATED,
                'MARK_TYPE' => $markType
            ])
            ->limit(250)
            ->distinct()
            ->asArray()
            ->all();

        $tempMarksheets = [];
        $marksheets = [];
        if(count($assessments) > 0){
            foreach ($assessments as $assessment){
                $cw = CourseWorkAssessment::find()->select(['MARKSHEET_ID'])
                    ->where(['ASSESSMENT_ID' => $assessment['ASSESSMENT_ID']])->one();
                $tempMarksheets[] = $cw['MARKSHEET_ID'];
            }

            // Assessments may have shared marksheet Ids. Remove duplicate marksheets.
            $marksheets = array_unique($tempMarksheets);

            foreach ($marksheets as $key => $value){
                if(empty($value)){
                    unset($marksheets[$key]);
                }
            }

            $logMsg = 'Found ' . count($marksheets) . ' marksheets.';
            SmisHelper::logMessage($logMsg, __METHOD__);

            if(YII_ENV_DEV){
                foreach($marksheets as $marksheet){
                    SmisHelper::logMessage($marksheet, __METHOD__);
                }
            }
        }else{
            $message = 'No marks ready for grading found for ';
            if($markType === 'EXAM'){
                SmisHelper::logMessage($message . 'exam', __METHOD__);
            }else{
                SmisHelper::logMessage($message . 'assessment', __METHOD__);
            }
        }

        SmisHelper::logMessage('Checking for marksheets ready for grading completed.', __METHOD__);

        return $marksheets;
    }
}