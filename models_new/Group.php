<?php

/**
 * @author Jack Jmm
 * @email jackmutiso37@gmail.com
 * @create date 12-9-2025 20:50:22 
 * @desc 
 */

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.GROUPS".
 *
 * @property string $GROUP_CODE
 * @property string|null $GROUP_NAME
 * @property string $STUDY_CENTER
 * @property string|null $GROUP_STATUS
 */
class Group extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.GROUPS';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['GROUP_CODE', 'STUDY_CENTER'], 'required'],
            [['GROUP_CODE'], 'string', 'max' => 15],
            [['GROUP_NAME', 'STUDY_CENTER'], 'string', 'max' => 30],
            [['GROUP_STATUS'], 'string', 'max' => 20],
            [['GROUP_CODE'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'GROUP_CODE' => 'Group Code',
            'GROUP_NAME' => 'Group Name',
            'STUDY_CENTER' => 'Study Center',
            'GROUP_STATUS' => 'Group Status',
        ];
    }
}
