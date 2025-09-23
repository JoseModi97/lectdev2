<?php

/**
 
 *
 * @author Jack
 * @email jackmutiso37@gmail.com
 */

namespace app\controllers;

use Yii;
use Exception;
use app\models\User;
use yii\web\Response;
use yii\debug\Toolbar;
use yii\web\Controller;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\filters\VerbFilter;
use app\models\EmpVerifyView;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    public function beforeAction($action)
    {
        if ($action->id == 'ipn') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * Display home page
     *
     * @return string|Response
     */
    public function actionIndex()
    {

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
            $deptCode = $staff['DEPT_CODE'];
            $facCode = $staff['FAC_CODE'];


            return $this->render('index', [
                'deptCode' => $deptCode,
                'facCode' => $facCode
            ]);
        }
    }
    public function actionHod()
    {

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
            $deptCode = $staff['DEPT_CODE'];
            $facCode = $staff['FAC_CODE'];


            return $this->render('hod', [
                'deptCode' => $deptCode,
                'facCode' => $facCode
            ]);
        }
    }

    public function actionDashboard()
    {
        return $this->render('dashboard');
    }
    /**
     * Display login page
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        if (Yii::$app->user->isGuest) {
            $this->layout = 'login';
            $model = new LoginForm();
            $model->userPassword = '';
            $model->payrollNumber = '';
            return $this->render('login', [
                'model' => $model,
            ]);
        } else {
            return $this->goHome();
        }
    }

    /**
     * Process user login
     *
     * @throws InvalidConfigException
     */
    // public function actionProcessLogin()
    // {


    //     try {
    //         $post = Yii::$app->request->post();
    //         // dd($post);
    //         $username = $post['payrollNumber'];
    //         $password = $post['userPassword'];

    //         $this->credentialsOk($username, $password);

    //         $session = Yii::$app->session;
    //         $session->set('username', $username);
    //         $secretKey = random_bytes(16);
    //         $session->set('secretKey', $secretKey);
    //         $encryptedPassword = Yii::$app->getSecurity()->encryptByPassword($password, $secretKey);
    //         $session->set('password', $encryptedPassword);

    //         $user = User::findByUsername($username);
    //         $user->checkRoles();

    //         if (Yii::$app->user->login($user)) {
    //             return $this->goHome();
    //         } else {
    //             throw new Exception('User session failed to start.');
    //         }
    //     } catch (Exception $ex) {
    //         $message = $ex->getMessage();
    //         if (YII_ENV_DEV) {
    //             $message .= ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
    //         }

    //         return Yii::createObject([
    //             'class' => 'yii\web\Response',
    //             'format' => Response::FORMAT_JSON,
    //             'data' => [
    //                 'message' => $message
    //             ]
    //         ]);
    //     }
    // }
    /**
     * Process user login
     *
     * @throws InvalidConfigException
     */
    public function actionProcessLogin()
    {
        // Set response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $post = Yii::$app->request->post();
            $username = $post['payrollNumber'];
            $password = $post['userPassword'];

            $this->credentialsOk($username, $password);

            $session = Yii::$app->session;
            $session->set('username', $username);
            $secretKey = random_bytes(16);
            $session->set('secretKey', $secretKey);
            $encryptedPassword = Yii::$app->getSecurity()->encryptByPassword($password, $secretKey);
            $session->set('password', $encryptedPassword);

            $user = User::findByUsername($username);
            $user->checkRoles();

            if (Yii::$app->user->login($user)) {
                // Return success JSON instead of redirect
                return [
                    'success' => true,
                    'message' => 'Login successful!',
                    'redirect' => Yii::$app->urlManager->createUrl(['/'])  // or your dashboard URL
                ];
            } else {
                throw new Exception('User session failed to start.');
            }
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                // $message .= ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }

            // Return error JSON
            return [
                'success' => false,
                'message' => $message
            ];
        }
    }

    /**
     * Logout user and clear session
     *
     * @return Response
     */
    public function actionLogout(): Response
    {
        Yii::$app->session->remove('username');
        Yii::$app->session->remove('password');
        Yii::$app->session->remove('secretKey');
        Yii::$app->user->logout();
        return $this->goHome();
    }

    /**
     * Check db connection with user credentials
     *
     * @param string $username
     * @param string $password
     * @throws Exception
     */
    // private function credentialsOk(string $username, string $password): void
    // {
    //     try {
    //         Yii::$app->getDb()->username = $username;
    //         Yii::$app->getDb()->password = $password;
    //         Yii::$app->getDb()->createCommand("SELECT COUNT(*) FROM dual")->queryScalar();
    //     } catch (yii\db\Exception $ex) {
    //         throw new Exception('Logon denied. Invalid username/password.' . $ex->getMessage());
    //     }
    // }
    private function credentialsOk(string $username, string $password): void
    {
        //    dd(phpinfo());


        try {
            Yii::$app->getDb()->username = $username;
            Yii::$app->getDb()->password = $password;
            Yii::$app->getDb()->createCommand("SELECT COUNT(*) FROM dual")->queryScalar();
        } catch (\yii\db\Exception $ex) {
            if (strpos($ex->getMessage(), 'could not find driver') !== false) {
                throw new Exception(
                    'Database driver not found. Please check OCI8 installation. ' .
                        'Technical details: ' . $ex->getMessage()
                );
            } else {
                throw new Exception('Logon denied. Invalid username/password. ' . $ex->getMessage());
            }
        }
    }

    public function actionSqlDump()
    {
        return $this->render('sql-dump');
    }
}
