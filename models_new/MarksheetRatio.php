<?php
/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 */

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.LEC_MARKSHEET_RATIOS".
 *
 * @property float $MARKSHEET_RATIO_ID
 * @property string $COURSE_CODE
 * @property int $CW_RATIO
 * @property int $EXAM_RATIO
 */
class MarksheetRatio extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.LEC_MARKSHEET_RATIOS';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['COURSE_CODE', 'CW_RATIO', 'EXAM_RATIO'], 'required'],
            [['MARKSHEET_RATIO_ID'], 'number'],
            [['CW_RATIO', 'EXAM_RATIO'], 'integer'],
            [['COURSE_CODE'], 'string', 'max' => 10],
            [['MARKSHEET_RATIO_ID'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'MARKSHEET_RATIO_ID' => 'Marksheet Ratio ID',
            'COURSE_CODE' => 'Course Code',
            'CW_RATIO' => 'Cw Ratio',
            'EXAM_RATIO' => 'Exam Ratio',
        ];
    }
}
