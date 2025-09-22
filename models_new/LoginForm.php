<?php

/**
 * @author Jack Jmm
 * @email jackmutiso37@gmail.com
 * @create date 12-9-2025 20:50:22 
 * @desc 
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
