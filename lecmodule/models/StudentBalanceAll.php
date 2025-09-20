<?php
/**
 * @date: 8/8/2025
 * @time: 12:52 PM
 */

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.UON_STUDENTS_BALANCE_ALL".
 *
 * @property string $REGISTRATION_NUMBER
 * @property float|null $DEBIT
 * @property float|null $CREDIT
 * @property float|null $BALANCE
 */
class StudentBalanceAll extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.UON_STUDENTS_BALANCE_ALL';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['REGISTRATION_NUMBER'], 'required'],
            [['DEBIT', 'CREDIT', 'BALANCE'], 'number'],
            [['REGISTRATION_NUMBER'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'REGISTRATION_NUMBER' => 'Registration Number',
            'DEBIT' => 'Debit',
            'CREDIT' => 'Credit',
            'BALANCE' => 'Balance',
        ];
    }
}