<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 */

namespace app\controllers;

use app\components\SmisHelper;
use app\models\AssessmentType;
use app\models\Course;
use app\models\CourseWorkAssessment;
use app\models\EmpVerifyView;
use app\models\MarksheetDef;
use app\models\search\MarksheetSearch;
use app\models\Semester;
use Exception;
use Yii;
use yii\db\Expression;
use yii\web\Controller;
use yii\web\ServerErrorHttpException;

class BaseController extends Controller implements BaseControllerInterface
{
    /**
     * @var string logged-in user payroll number
     */
    protected $payrollNo;

    /**
     * @var string logged-in user department code
     */
    protected $deptCode;

    /**
     * @var string logged-in user department name
     */
    protected $deptName;

    /**
     * @var string logged-in user faculty code
     */
    protected $facCode;

    /**
     * @var string logged-in user faculty name
     */
    protected $facName;

    /**
     * @var string current academic year
     */
    protected $academicYear;

    public function getPayrollNo(): ?string
    {
        return $this->payrollNo;
    }

    public function getDeptCode(): ?string
    {
        return $this->deptCode;
    }

    public function getDeptName(): ?string
    {
        return $this->deptName;
    }

    public function getFacCode(): ?string
    {
        return $this->facCode;
    }

    public function getFacName(): ?string
    {
        return $this->facName;
    }

    public function getAcademicYear(): ?string
    {
        return $this->academicYear;
    }

    /**
     * Initialize the controllers
     *
     * @throws ServerErrorHttpException
     */
    public function init()
    {
        try {
            parent::init();

            /**
             * We use the username, password and secretKey stored in the session to make a db connection.
             * If either is missing logout user.
             */
            $session = Yii::$app->session;
            if (
                empty($session->get('username')) || empty($session->get('password'))
                || empty($session->get('secretKey'))
            ) {
                Yii::$app->user->switchIdentity(null);
            }

            if (Yii::$app->user->isGuest) {
                return $this->redirect('site/login');
            } else {
                $staff = EmpVerifyView::find()
                    ->select([
                        'DEPT_CODE',
                        'DEPT_NAME',
                        'FAC_CODE',
                        'FACULTY_NAME'
                    ])
                    ->where(['PAYROLL_NO' => Yii::$app->user->identity->PAYROLL_NO])
                    ->asArray()
                    ->one();

                $this->payrollNo = Yii::$app->user->identity->PAYROLL_NO;
                $this->deptCode = $staff['DEPT_CODE'];
                $this->deptName = $staff['DEPT_NAME'];
                $this->facCode = $staff['FAC_CODE'];
                $this->facName = $staff['FACULTY_NAME'];
                $this->academicYear = $this->getCurrentAcademicYear();

                
            }
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }



    /**
     * Get the current academic year
     * @return string
     * @throws Exception
     */
    public function getCurrentAcademicYear(): string // Changed from protected to public
    {
        if (YII_ENV_DEV) {
            return '2020/2021';
        } else {
            return '2021/2022';
        }
    }
}
