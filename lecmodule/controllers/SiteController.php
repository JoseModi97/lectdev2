<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

namespace app\controllers;

use app\models\Course;
use app\models\EmpVerifyView;
use app\models\LoginForm;
use app\models\MarksheetDef;
use app\models\User;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
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
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
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
        if(empty($session->get('username')) || empty($session->get('password'))
            || empty($session->get('secretKey'))){
            Yii::$app->user->switchIdentity(null);
        }

        if(Yii::$app->user->isGuest){
            return $this->redirect('site/login');
        }else{
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

    /**
     * Display login page
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        if(Yii::$app->user->isGuest){
            $this->layout = 'login';
            $model = new LoginForm();
            $model->userPassword = '';
            $model->payrollNumber = '';
            return $this->render('login', [
                'model' => $model,
            ]);
        }else{
            return $this->goHome();
        }
    }

    /**
     * Process user login
     *
     * @throws InvalidConfigException
     */
    public function actionProcessLogin()
    {
        try{
            $post = Yii::$app->request->post();
            $username = $post['LoginForm']['payrollNumber'];
            $password = $post['LoginForm']['userPassword'];

            $this->credentialsOk($username, $password);
            
            $session = Yii::$app->session;
            $session->set('username', $username);
            $secretKey = random_bytes(16);
            $session->set('secretKey', $secretKey);
            $encryptedPassword = Yii::$app->getSecurity()->encryptByPassword($password, $secretKey);
            $session->set('password', $encryptedPassword);

            $user = User::findByUsername($username);
            $user->checkRoles();

            if(Yii::$app->user->login($user)){
                return $this->goHome();
            }else{
                throw new Exception('User session failed to start.');
            }
        }catch (Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message .= ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }

            return Yii::createObject([
                'class' => 'yii\web\Response',
                'format' => Response::FORMAT_JSON,
                'data' => [
                    'message' => $message
                ]
            ]);
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
     * Download user guide
     * @return Response|\yii\console\Response
     * @throws ServerErrorHttpException
     */
    public function actionDownloadManual()
    {
        try{
            $filename = Yii::getAlias('@app') . '/uploads/lecturer_module_manual.pdf';
            return Yii::$app->response->sendFile($filename, 'lecturer_module_user_guide', ['inline' => true]);
        }catch(Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * Check db connection with user credentials
     *
     * @param string $username
     * @param string $password
     * @throws Exception
     */
    private function credentialsOk(string $username, string $password): void
    {
        try{
            Yii::$app->getDb()->username = $username;
            Yii::$app->getDb()->password = $password;
            // Yii::$app->getDb()->createCommand("SELECT COUNT(*) FROM dual")->queryScalar();
        }catch (yii\db\Exception $ex){
            throw new Exception('Logon denied. Invalid username/password.');
        }
    }

    /**
     * Check if lecturer is a leader for a course
     * @param string $payroll
     * @param string $code
     * @param string $year
     * @return void
     * @throws ServerErrorHttpException
     */
    public function actionCheckLeader(string $payroll, string $code, string $year)
    {
        try{
            $courseIds = Course::find()->select(['COURSE_ID'])->where(['COURSE_CODE' => $code])->all();

            $msg = 'Lecturer with payroll ' . $payroll . ' is not the course leader for ' . $code . ' in the academic year '.
                $year;

            foreach ($courseIds as $courseId){
                $marksheetDef = MarksheetDef::find()->select(['PAYROLL_NO'])
                    ->where(['COURSE_ID' => $courseId->COURSE_ID])
                    ->andWhere(['like', 'MRKSHEET_ID', $year . '%', false])
                    ->one();
                if(!is_null($marksheetDef)){
                    if($payroll === strval($marksheetDef->PAYROLL_NO)){
                        $msg = 'Lecturer with payroll ' . $payroll . ' is the course leader for ' . $code . ' in the academic year '.
                            $year;
                        break;
                    }
                }
            }

            print_r($msg); exit();

        }catch (Exception $ex){
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

        /**
     * Run sync cron jobs from browser only for demos
     * @return void
     */
    public function actionConsolidate(): void
    {
        $output = shell_exec('/usr/bin/php /var/www/html/lecmodule/yii consolidate/course-work');
        echo "<pre>$output</pre>";
        echo "-------------------------------------------------------------------------------------------------------";
        echo PHP_EOL;

        $output = shell_exec('/usr/bin/php /var/www/html/lecmodule/yii consolidate/exam');
        echo "<pre>$output</pre>";
        echo "-------------------------------------------------------------------------------------------------------";
        echo PHP_EOL;

        $output = shell_exec('/usr/bin/php /var/www/html/lecmodule/yii publish');
        echo "<pre>$output</pre>";
        echo "-------------------------------------------------------------------------------------------------------";
        echo PHP_EOL;
    }

    public function actionPesaflow()
    {
        $this->layout = 'login';
        return $this->render('pesaflow');
    }

    public function actionIpn()
    {
        echo "Ipn";
        echo PHP_EOL;
    }
}