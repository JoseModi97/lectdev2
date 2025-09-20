<?php
/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @modify date 01-06-2020 17:05:27 
 * @desc [description]
 */

namespace app\components;

use Yii;
use neconix\yii2oci8\Oci8Connection;
use Exception;
use yii\web\ServerErrorHttpException;

class DbConnection extends Oci8Connection
{
    /**
     * @throws ServerErrorHttpException
     */
    public function init()
    {
        try {
            parent::init();

            $session = Yii::$app->session;
            $this->username = $session->get('username');
            $this->password = Yii::$app->getSecurity()->decryptByPassword(
                $session->get('password'),
                $session->get('secretKey'));        
        }catch (Exception $ex){
            $message = 'Logon denied. Invalid username/password.';
            if(YII_ENV_DEV){
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, '500');
        }
    }
}