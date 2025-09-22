<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 15-12-2020 20:50:22 
 * @modify date 15-12-2020 20:50:22 
 * @desc [description]
 */

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.ACADEMIC_PERIOD".
 *
 * @property string $PERIOD_CODE
 * @property string|null $PERIOD_NAME
 * @property string|null $PERIOD_START_DATE
 * @property string|null $PERIOD_END_DATE
 * @property int|null $PERIOD_INDEX
 */
class AcademicPeriod extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.ACADEMIC_PERIOD';
    }
   
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['PERIOD_CODE'], 'required'],
            [['PERIOD_INDEX'], 'integer'],
            [['PERIOD_CODE', 'PERIOD_NAME'], 'string', 'max' => 50],
            [['PERIOD_START_DATE', 'PERIOD_END_DATE'], 'string', 'max' => 7],
            [['PERIOD_CODE'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'PERIOD_CODE' => 'Period Code',
            'PERIOD_NAME' => 'Period Name',
            'PERIOD_START_DATE' => 'Period Start Date',
            'PERIOD_END_DATE' => 'Period End Date',
            'PERIOD_INDEX' => 'Period Index',
        ];
    }
}
