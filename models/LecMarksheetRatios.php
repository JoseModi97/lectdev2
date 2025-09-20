<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "MUTHONI.LEC_MARKSHEET_RATIOS".
 *
 * @property float $MARKSHEET_RATIO_ID
 * @property string $COURSE_CODE
 * @property int $CW_RATIO
 * @property int $EXAM_RATIO
 * @property string|null $PROGRAM_CODE
 */
class LecMarksheetRatios extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'MUTHONI.LEC_MARKSHEET_RATIOS';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['PROGRAM_CODE'], 'default', 'value' => ''],
            [['MARKSHEET_RATIO_ID', 'CW_RATIO', 'EXAM_RATIO'], 'required'],
            [['MARKSHEET_RATIO_ID'], 'number'],
            [['CW_RATIO', 'EXAM_RATIO'], 'integer'],
            [['COURSE_CODE', 'PROGRAM_CODE'], 'string', 'max' => 10],
            [['MARKSHEET_RATIO_ID'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'MARKSHEET_RATIO_ID' => 'Marksheet Ratio ID',
            'COURSE_CODE' => 'Course Code',
            'CW_RATIO' => 'Cw Ratio',
            'EXAM_RATIO' => 'Exam Ratio',
            'PROGRAM_CODE' => 'Program Code',
        ];
    }

}
