<?php

namespace app\modules\v1\controllers;

use app\models\LoginForm;
use app\models\User;
use Firebase\JWT\JWT;
use Yii;
use yii\web\Response;

class LoginController extends BaseController
{
    private $secretKey = 'your_secret_key'; // Replace with a strong secret key

    public function actionLogin()
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate()) {
            $user = User::findByUsername($model->payrollNumber);
            if ($user) {
                $payload = [
                    'iat' => time(),
                    'exp' => time() + 3600, // Token expires in 1 hour
                    'data' => [
                        'userId' => $user->getId(),
                        'username' => $user->PAYROLL_NO,
                    ],
                ];

                $token = JWT::encode($payload, $this->secretKey, 'HS256');

                return ['status' => 'success', 'token' => $token];
            } else {
                return ['status' => 'error', 'message' => 'Invalid credentials.'];
            }
        } else {
            return $model->getErrors();
        }
    }
}
