<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.STUDENT_MARKSHEETS_VIEW".
 *
 * @property string $MRKSHEET_ID
 * @property string $REGISTRATION_NUMBER
 * @property string $SURNAME
 * @property string $OTHER_NAMES
 * @property string|null $EMAIL
 * @property string|null $TELEPHONE
 * @property string $EXAMTYPE_CODE
 * @property string $DESCRIPTION
 */
class StudentMarksheetView extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.STUDENT_MARKSHEETS_VIEW';
    }

    /**
     * {@inheritdoc}
     */
    public static function primaryKey(): array
    {
        return ['MRKSHEET_ID', 'REGISTRATION_NUMBER'];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['MRKSHEET_ID', 'REGISTRATION_NUMBER', 'SURNAME', 'OTHER_NAMES', 'EXAMTYPE_CODE', 'DESCRIPTION'], 'required'],
            [['MRKSHEET_ID', 'EMAIL'], 'string', 'max' => 60],
            [['REGISTRATION_NUMBER'], 'string', 'max' => 20],
            [['SURNAME', 'DESCRIPTION'], 'string', 'max' => 30],
            [['OTHER_NAMES', 'TELEPHONE'], 'string', 'max' => 100],
            [['EXAMTYPE_CODE'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'MRKSHEET_ID' => 'Mrksheet ID',
            'REGISTRATION_NUMBER' => 'Registration Number',
            'SURNAME' => 'Surname',
            'OTHER_NAMES' => 'Other Names',
            'EMAIL' => 'Email',
            'TELEPHONE' => 'Telephone',
            'EXAMTYPE_CODE' => 'Examtype Code',
            'DESCRIPTION' => 'Description',
        ];
    }
}
