<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.LEC_ALLOCATION_STATUSES".
 *
 * @property int $STATUS_ID
 * @property string|null $STATUS_NAME
 */
class AllocationStatus extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.LEC_ALLOCATION_STATUSES';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['STATUS_NAME'], 'required'],
            [['STATUS_ID'], 'integer'],
            [['STATUS_NAME'], 'string', 'max' => 20],
            [['STATUS_ID'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'STATUS_ID' => 'Status ID',
            'STATUS_NAME' => 'Status Name',
        ];
    }
}
