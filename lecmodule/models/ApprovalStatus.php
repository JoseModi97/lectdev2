<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 11-01-2021 11:48:28 
 * @modify date 11-01-2021 11:48:28 
 * @desc [description]
 */

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.LEC_APPROVAL_STATUS".
 *
 * @property int $APPROVAL_STATUS_ID
 * @property string|null $NAME
 */
class ApprovalStatus extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.LEC_APPROVAL_STATUS';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['APPROVAL_STATUS_ID'], 'integer'],
            [['NAME'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'APPROVAL_STATUS_ID' => 'Approval Status ID',
            'NAME' => 'Name',
        ];
    }
}
