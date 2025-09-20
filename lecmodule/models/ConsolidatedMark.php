<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.LEC_CONSOLIDATED_MARKS".
 *
 * @property int $ID
 * @property string $MARKSHEET_ID
 * @property string $REGISTRATION_NUMBER
 * @property float|null $CW_MARKS
 * @property float|null $TOTAL_MARKS
 * @property string|null $UPDATED_AT
 * @property float|null $EXAM_MARKS
 * @property string|null $GRADE
 * @property string|null $EXAM_TYPE
 * @property int $UP_TO_DATE
 * @property UonStudent $student
 */
class ConsolidatedMark extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.LEC_CONSOLIDATED_MARKS';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['MARKSHEET_ID', 'REGISTRATION_NUMBER'], 'required'],
            [['ID', 'UP_TO_DATE'], 'number'],
            [['CW_MARKS', 'TOTAL_MARKS', 'EXAM_MARKS'], 'number'],
            [['MARKSHEET_ID'], 'string', 'max' => 60],
            [['REGISTRATION_NUMBER'], 'string', 'max' => 30],
            [['UPDATED_AT'], 'safe'],
            [['GRADE'], 'string', 'max' => 8],
            [['EXAM_TYPE'], 'string', 'max' => 12],
            [['ID'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'ID' => 'ID',
            'MARKSHEET_ID' => 'Marksheet ID',
            'REGISTRATION_NUMBER' => 'Registration Number',
            'CW_MARKS' => 'Cw Marks',
            'TOTAL_MARKS' => 'Total Marks',
            'UPDATED_AT' => 'Updated At',
            'EXAM_MARKS' => 'Exam Marks',
            'GRADE' => 'Grade',
            'EXAM_TYPE' => 'Exam Type',
            'UP_TO_DATE' => 'Up To Date',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getStudent(): ActiveQuery
    {
        return $this->hasOne(UonStudent::class, ['REGISTRATION_NUMBER' => 'REGISTRATION_NUMBER']);
    }
}
