<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.DEPARTMENTS".
 *
 * @property string|null $FAC_CODE
 * @property string $DEPT_CODE
 * @property string|null $DEPT_NAME
 * @property string|null $PHY_LOCATION
 * @property string|null $TEL_NO
 * @property string|null $FAX_NO
 * @property string|null $EMAIL
 * @property string|null $WEBSITE
 * @property string|null $DEPT_TYPE
 * @property string|null $HR_DEPT_CODE
 */
class Department extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.DEPARTMENTS';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['FAC_CODE'], 'string', 'max' => 3],
            [['DEPT_CODE', 'HR_DEPT_CODE'], 'string', 'max' => 6],
            [['DEPT_NAME'], 'string', 'max' => 60],
            [['PHY_LOCATION', 'TEL_NO', 'FAX_NO', 'EMAIL'], 'string', 'max' => 40],
            [['WEBSITE'], 'string', 'max' => 120],
            [['DEPT_TYPE'], 'string', 'max' => 12],
            [['DEPT_CODE'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'FAC_CODE' => 'Fac Code',
            'DEPT_CODE' => 'Dept Code',
            'DEPT_NAME' => 'Dept Name',
            'PHY_LOCATION' => 'Phy Location',
            'TEL_NO' => 'Tel No',
            'FAX_NO' => 'Fax No',
            'EMAIL' => 'Email',
            'WEBSITE' => 'Website',
            'DEPT_TYPE' => 'Dept Type',
            'HR_DEPT_CODE' => 'Hr Dept Code',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getFaculty(): ActiveQuery
    {
        return $this->hasOne(Faculty::class, ['FAC_CODE' => 'FAC_CODE']);
    }

    /**
     * @return ActiveQuery
     */
    public function getProgrammes(): ActiveQuery
    {
        return $this->hasMany(DegreeProgramme::class, ['FACUL_FAC_CODE' => 'FAC_CODE']);
    }
}
