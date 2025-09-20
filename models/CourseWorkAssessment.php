<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 15-12-2020 20:50:22 
 * @modify date 15-12-2020 20:50:22 
 * @desc [description]
 */ 

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.LEC_CW_ASSESSMENT".
 *
 * @property int $ASSESSMENT_ID
 * @property string|null $MARKSHEET_ID
 * @property int|null $ASSESSMENT_TYPE_ID
 * @property float|null $WEIGHT
 * @property string|null $RESULT_DUE_DATE
 * @property int|null $DIVIDER
 */
class CourseWorkAssessment extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.LEC_CW_ASSESSMENT';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['ASSESSMENT_ID', 'ASSESSMENT_TYPE_ID'], 'integer'],
            [['WEIGHT', 'DIVIDER'], 'number'],
            [['MARKSHEET_ID'], 'string', 'max' => 60],
            [['RESULT_DUE_DATE'], 'safe'],
            [['ASSESSMENT_ID'], 'unique'],
            [['RESULT_DUE_DATE', 'DIVIDER'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'ASSESSMENT_ID' => 'Assessment ID',
            'MARKSHEET_ID' => 'Marksheet ID',
            'ASSESSMENT_TYPE_ID' => 'Assessment Type ID',
            'WEIGHT' => 'Weight',
            'RESULT_DUE_DATE' => 'Result Due Date',
            'DIVIDER' => 'Divider',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getAssessmentType(): ActiveQuery
    {
        return $this->hasOne(AssessmentType::class, ['ASSESSMENT_TYPE_ID' => 'ASSESSMENT_TYPE_ID']);
    }

    /**
     * @return ActiveQuery
     */
    public function getMarksheetDef(): ActiveQuery
    {
        return $this->hasOne(MarksheetDef::class, ['MRKSHEET_ID' => 'MARKSHEET_ID']);
    }

    /**
     * @return ActiveQuery
     */
    public function getMarksheet(): ActiveQuery
    {
        return $this->hasOne(Marksheet::class, ['MRKSHEET_ID' => 'MARKSHEET_ID']);
    }

    /**
     * @return ActiveQuery for MarksheetDef
     */
    public function getAssignment(): ActiveQuery
    {
        return $this->hasOne(CourseAssignment::class, ['MRKSHEET_ID' => 'MARKSHEET_ID']);
    }
}
