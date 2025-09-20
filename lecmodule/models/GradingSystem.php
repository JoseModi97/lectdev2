<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.GRADINGSYSTEM".
 *
 * @property int $GRADINGCODE
 * @property string $GRADINGNAME
 */
class GradingSystem extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.GRADINGSYSTEM';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['GRADINGCODE', 'GRADINGNAME'], 'required'],
            [['GRADINGCODE'], 'integer'],
            [['GRADINGNAME'], 'string', 'max' => 20],
            [['GRADINGCODE'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'GRADINGCODE' => 'Gradingcode',
            'GRADINGNAME' => 'Gradingname',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getGradingSystemDetails(): ActiveQuery
    {
        return $this->hasOne(GradingSystemDetails::class, ['GRADINGID' => 'GRADINGCODE']);
    }
}
