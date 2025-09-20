<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.FACULTIES".
 *
 * @property string $COL_CODE
 * @property string $FAC_CODE
 * @property string $FACULTY_NAME
 * @property string|null $TEL_NO
 * @property string|null $FAX_NO
 * @property string|null $EMAIL
 * @property string|null $URL
 * @property string|null $PHYSICAL_LOCATION
 * @property string|null $PO_BOX_ADDRESS
 * @property string|null $DEAN_OF_FACULTY
 * @property string|null $FAC_TYPE
 * @property string|null $DEG_INITIAL
 * @property string|null $FAC_HEAD
 * @property string|null $ONLINE_SUPPORT
 * @property string|null $HR_FAC_CODE
 */
class Faculty extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.FACULTIES';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['COL_CODE', 'FAC_CODE', 'FACULTY_NAME'], 'required'],
            [['COL_CODE'], 'string', 'max' => 6],
            [['FAC_CODE'], 'string', 'max' => 3],
            [['FACULTY_NAME', 'DEAN_OF_FACULTY'], 'string', 'max' => 60],
            [['TEL_NO', 'FAX_NO', 'EMAIL'], 'string', 'max' => 30],
            [['URL'], 'string', 'max' => 100],
            [['PHYSICAL_LOCATION'], 'string', 'max' => 80],
            [['PO_BOX_ADDRESS'], 'string', 'max' => 40],
            [['FAC_TYPE', 'ONLINE_SUPPORT'], 'string', 'max' => 12],
            [['DEG_INITIAL', 'HR_FAC_CODE'], 'string', 'max' => 10],
            [['FAC_HEAD'], 'string', 'max' => 20],
            [['FAC_CODE'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'COL_CODE' => 'Col Code',
            'FAC_CODE' => 'Fac Code',
            'FACULTY_NAME' => 'Faculty Name',
            'TEL_NO' => 'Tel No',
            'FAX_NO' => 'Fax No',
            'EMAIL' => 'Email',
            'URL' => 'Url',
            'PHYSICAL_LOCATION' => 'Physical Location',
            'PO_BOX_ADDRESS' => 'Po Box Address',
            'DEAN_OF_FACULTY' => 'Dean Of Faculty',
            'FAC_TYPE' => 'Fac Type',
            'DEG_INITIAL' => 'Deg Initial',
            'FAC_HEAD' => 'Fac Head',
            'ONLINE_SUPPORT' => 'Online Support',
            'HR_FAC_CODE' => 'Hr Fac Code',
        ];
    }
}
