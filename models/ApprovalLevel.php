<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 28-01-2021 16:44:50 
 * @modify date 28-01-2021 16:44:50 
 * @desc [description]
 */

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.LEC_APPROVAL_LEVELS".
 *
 * @property int $APPROVAL_LEVEL_ID
 * @property int|null $APPROVAL_ORDER
 * @property string|null $NAME
 * @property string|null $DESCRIPTION
 */
class ApprovalLevel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.LEC_APPROVAL_LEVELS';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['APPROVAL_LEVEL_ID', 'APPROVAL_ORDER'], 'integer'],
            [['NAME'], 'string', 'max' => 20],
            [['DESCRIPTION'], 'string', 'max' => 1024],
            [['APPROVAL_LEVEL_ID'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'APPROVAL_LEVEL_ID' => 'Approval Level ID',
            'APPROVAL_ORDER' => 'Approval Order',
            'NAME' => 'Name',
            'DESCRIPTION' => 'Description',
        ];
    }
}
