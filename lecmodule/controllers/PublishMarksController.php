<?php
/**
 * @date: 8/6/2025
 * @time: 11:38 AM
 */

namespace app\controllers;

use app\components\SmisHelper;
use app\models\search\MarksToPublishSearch;
use app\models\StudentBalance;
use Exception;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class PublishMarksController extends BaseController
{
    /**
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     */
    public function init()
    {
        parent::init();
        $allowedRoles = [
            'LEC_SMIS_DEAN',
        ];
        SmisHelper::allowAccess($allowedRoles);
    }

    public function actionIndex(): string
    {
        $searchModel = new MarksToPublishSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

//        // Collect all registration numbers from data
//        $registrationNumbers = array_column($dataProvider->getModels(), 'REGISTRATION_NUMBER');
//
//        // Fetch the latest balances by REGISTRATION_NUMBER using subquery to get the latest academic year
//        $latestBalances = StudentBalance::find()
//            ->from('MUTHONI.STUDENT_BALANCES') // fully qualified table name
//            ->where(['REGISTRATION_NUMBER' => $registrationNumbers])
//            ->andWhere(new \yii\db\Expression('ACADEMIC_YEAR = (
//                SELECT MAX(sb2.ACADEMIC_YEAR) FROM MUTHONI.STUDENT_BALANCES sb2
//                WHERE sb2.REGISTRATION_NUMBER = MUTHONI.STUDENT_BALANCES.REGISTRATION_NUMBER
//            )'))->all();
//
//        $balanceMap = [];
//        foreach ($latestBalances as $balance) {
//            $balanceMap[$balance->REGISTRATION_NUMBER] = $balance;
//        }

        list($academicYear, $program, $level, $semester, $group, $courseCode, $unused) = explode('_', Yii::$app->request->queryParams['marksheetId']);

        $panelHeading = 'Publish Marks for '
            . "Academic Year: $academicYear, "
            . "Program: $program, "
            . "Level: $level, "
            . "Semester: $semester, "
            . "Group: $group, "
            . "Course: $courseCode";

        return $this->render('index', [
            'title' => 'Publish Marks',
            'deptCode' => $this->deptCode,
            'facCode' => $this->facCode,
            'marksheetId' => Yii::$app->request->queryParams['marksheetId'],
            'panelHeading' => $panelHeading,
            'provider' => $dataProvider,
            'filterModel' => $searchModel
        ]);
    }

    public function actionPublish(): Response
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = Yii::$app->request->post();
            $marksheetId = $post['marksheetId'];

            SmisHelper::consolidateAndPublishMarks($marksheetId);

            $transaction->commit();

            if (Yii::$app->request->isAjax) {
                return $this->asJson([
                    'success' => true,
                    'message' => 'Marks published successfully'
                ]);
            }

            $this->setFlash('success', 'Publish Marks', 'Operation completed successfully.');
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);

        } catch (Exception $ex) {
            $transaction->rollBack();

            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }

            return $this->asJson(['success' => false, 'message' => $message]);
        }
    }
}