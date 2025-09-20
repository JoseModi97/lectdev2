<?php

namespace app\components;

use Yii;
use yii\db\Connection;
use Exception;

class DbConnection extends Connection
{
    public function init()
    {
        parent::init();

        // Only try to access session in web applications
        if (Yii::$app instanceof \yii\web\Application && Yii::$app->has('session')) {
            $session = Yii::$app->session;

            // Only set credentials if session data exists
            if ($session->has('username') && $session->has('password') && $session->has('secretKey')) {
                try {
                    $username = $session->get('username');
                    $encryptedPassword = $session->get('password');
                    $secretKey = $session->get('secretKey');
                    $password = Yii::$app->getSecurity()->decryptByPassword($encryptedPassword, $secretKey);
                    $this->username = $username;
                    $this->password = $password;
                } catch (Exception $ex) {
                    // Log the error but don't throw exception
                    Yii::error('Failed to decrypt password: ' . $ex->getMessage());
                    // Set to null or default credentials
                    $this->username = null;
                    $this->password = null;
                }
            }
        }

        // For console applications or when no session data exists,
        // let the connection attempt with null credentials or default values
        // The actual authentication failure will be handled elsewhere
    }
}
