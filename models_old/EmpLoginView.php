<?php
/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @desc [description]
 */

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "HRMIS.EMP_LOGIN_VIEW".
 *
 * @property int $PAYROLL_NO
 * @property int $EMP_ID
 * @property string $SURNAME
 * @property string $OTHER_NAMES
 * @property string $EMP_TITLE
 * @property string $STATUS_DESC
 * @property string|null $STATUS_CAT
 * @property int|null $OVERALL_STATUS
 * @property string|null $EMAIL
 * @property string|null $MOBILE_NO
 */
class EmpLoginView extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'HRMIS.EMP_LOGIN_VIEW';
    }

    /**
     * {@inheritdoc}
     */
    public static function primaryKey(): array
    {
        return ['EMP_ID'];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['PAYROLL_NO', 'EMP_ID', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE', 'STATUS_DESC'], 'required'],
            [['PAYROLL_NO', 'EMP_ID', 'OVERALL_STATUS'], 'integer'],
            [['SURNAME', 'MOBILE_NO'], 'string', 'max' => 15],
            [['OTHER_NAMES', 'STATUS_DESC'], 'string', 'max' => 25],
            [['EMP_TITLE'], 'string', 'max' => 6],
            [['STATUS_CAT'], 'string', 'max' => 20],
            [['EMAIL'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'PAYROLL_NO' => 'Payroll No',
            'EMP_ID' => 'Emp ID',
            'SURNAME' => 'Surname',
            'OTHER_NAMES' => 'Other Names',
            'EMP_TITLE' => 'Emp Title',
            'STATUS_DESC' => 'Status Desc',
            'STATUS_CAT' => 'Status Cat',
            'OVERALL_STATUS' => 'Overall Status',
            'EMAIL' => 'Email',
            'MOBILE_NO' => 'Mobile No',
        ];
    }
}
