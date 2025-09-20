<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.STUDENT_BALANCES".
 *
 * @property string $COL_CODE
 * @property string $COL_NAME
 * @property string $FAC_CODE
 * @property string $FACULTY_NAME
 * @property string $DEGREE_CODE
 * @property string $DEGREE_NAME
 * @property string $DEGREE_TYPE
 * @property string|null $ACADEMIC_YEAR
 * @property string $REGISTRATION_NUMBER
 * @property string $SURNAME
 * @property string $OTHER_NAMES
 * @property string|null $STUDENT_STATUS
 * @property string $STUDENT_CATEGORY_ID
 * @property string $CATEGORY_DESCRIPTION
 * @property float|null $DEBITS
 * @property float|null $CREDIT
 * @property float|null $BALANCE
 */
class StudentBalance extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.STUDENT_BALANCES';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['COL_CODE', 'COL_NAME', 'FAC_CODE', 'FACULTY_NAME', 'DEGREE_CODE', 'DEGREE_NAME', 'DEGREE_TYPE', 'REGISTRATION_NUMBER', 'SURNAME', 'OTHER_NAMES', 'STUDENT_CATEGORY_ID', 'CATEGORY_DESCRIPTION'], 'required'],
            [['DEBITS', 'CREDIT', 'BALANCE'], 'number'],
            [['COL_CODE', 'DEGREE_CODE'], 'string', 'max' => 6],
            [['COL_NAME', 'OTHER_NAMES'], 'string', 'max' => 100],
            [['FAC_CODE', 'STUDENT_CATEGORY_ID'], 'string', 'max' => 5],
            [['FACULTY_NAME'], 'string', 'max' => 60],
            [['DEGREE_NAME'], 'string', 'max' => 180],
            [['DEGREE_TYPE'], 'string', 'max' => 40],
            [['ACADEMIC_YEAR', 'CATEGORY_DESCRIPTION'], 'string', 'max' => 50],
            [['REGISTRATION_NUMBER'], 'string', 'max' => 20],
            [['SURNAME'], 'string', 'max' => 30],
            [['STUDENT_STATUS'], 'string', 'max' => 15],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'COL_CODE' => 'Col Code',
            'COL_NAME' => 'Col Name',
            'FAC_CODE' => 'Fac Code',
            'FACULTY_NAME' => 'Faculty Name',
            'DEGREE_CODE' => 'Degree Code',
            'DEGREE_NAME' => 'Degree Name',
            'DEGREE_TYPE' => 'Degree Type',
            'ACADEMIC_YEAR' => 'Academic Year',
            'REGISTRATION_NUMBER' => 'Registration Number',
            'SURNAME' => 'Surname',
            'OTHER_NAMES' => 'Other Names',
            'STUDENT_STATUS' => 'Student Status',
            'STUDENT_CATEGORY_ID' => 'Student Category ID',
            'CATEGORY_DESCRIPTION' => 'Category Description',
            'DEBITS' => 'Debits',
            'CREDIT' => 'Credit',
            'BALANCE' => 'Balance',
        ];
    }
}
