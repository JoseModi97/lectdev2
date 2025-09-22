<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 16-02-2021 16:42:07 
 * @modify date 16-02-2021 16:42:07 
 * @desc [description]
 */

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.LEC_RETURNED_SCRIPTS".
 *
 * @property int $RETURNED_SCRIPT_ID
 * @property int|null $RETURNED_BY
 * @property int|null $RECEIVED_BY
 * @property string|null $RETURNED_DATE
 * @property string|null $REMARKS
 * @property string|null $MARKSHEET_ID
 * @property string|null $REGISTRATION_NUMBER
 * @property int|null $STATUS
 */
class ReturnedScript extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.LEC_RETURNED_SCRIPTS';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['RETURNED_SCRIPT_ID', 'RETURNED_BY', 'RECEIVED_BY', 'STATUS'], 'integer'],
            [['RETURNED_DATE'], 'safe'],
            [['REMARKS'], 'string', 'max' => 1024],
            [['MARKSHEET_ID'], 'string', 'max' => 60],
            [['REGISTRATION_NUMBER'], 'string', 'max' => 20],
            [['RETURNED_SCRIPT_ID'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'RETURNED_SCRIPT_ID' => 'Returned Script ID',
            'RETURNED_BY' => 'Returned By',
            'RECEIVED_BY' => 'Received By',
            'RETURNED_DATE' => 'Returned Date',
            'REMARKS' => 'Remarks',
            'MARKSHEET_ID' => 'Marksheet ID',
            'REGISTRATION_NUMBER' => 'Registration Number',
            'STATUS' => 'Status',
        ];
    }
}
