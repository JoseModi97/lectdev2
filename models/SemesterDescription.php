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
 * This is the model class for table "MUTHONI.SEMESTER_DESCRIPTIONS".
 *
 * @property string $DESCRIPTION_CODE
 * @property string|null $SEMESTER_DESC
 * @property int|null $ORDER_PRIORITY
 */
class SemesterDescription extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.SEMESTER_DESCRIPTIONS';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['DESCRIPTION_CODE'], 'required'],
            [['ORDER_PRIORITY'], 'integer'],
            [['DESCRIPTION_CODE'], 'string', 'max' => 12],
            [['SEMESTER_DESC'], 'string', 'max' => 20],
            [['DESCRIPTION_CODE'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'DESCRIPTION_CODE' => 'Description Code',
            'SEMESTER_DESC' => 'Semester Desc',
            'ORDER_PRIORITY' => 'Order Priority',
        ];
    }
}
