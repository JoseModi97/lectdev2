<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 15-12-2020 20:50:22 
 * @modify date 15-12-2020 20:50:22 
 * @desc [description]
 */

namespace app\models;

use yii\base\Model;

class LoginForm extends Model
{
    public $payrollNumber;
    public $userPassword;

    /**
     * @return array the validation rules.
     */
    public function rules(): array
    {
        return [
            [['payrollNumber', 'userPassword'], 'required'],
        ];
    }

    /**
     * @return string[]
     */
    public function attributeLabels(): array
    {
        return [
            'payrollNumber' => 'username',
            'userPassword' => 'password'
        ];
    }
}
