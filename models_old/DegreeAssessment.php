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
 * This is the model class for table "MUTHONI.LEC_DEGREE_ASSESSMENT".
 *
 * @property int $DEG_ASSESSMENT_ID
 * @property int|null $COURSE_WORK_RATIO
 * @property int|null $EXAM_RATIO
 * @property string|null $DEGREE_CODE
 * @property string|null $EFFECTIVE_START_DATE
 * @property string|null $EFFECTIVE_END_DATE
 * @property string|null $STATUS
 */
class DegreeAssessment extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.LEC_DEGREE_ASSESSMENT';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['DEG_ASSESSMENT_ID'], 'required'],
            [['DEG_ASSESSMENT_ID', 'COURSE_WORK_RATIO', 'EXAM_RATIO'], 'integer'],
            [['DEGREE_CODE'], 'string', 'max' => 5],
            [['EFFECTIVE_START_DATE', 'EFFECTIVE_END_DATE'], 'string', 'max' => 7],
            [['STATUS'], 'string', 'max' => 10],
            [['DEG_ASSESSMENT_ID'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'DEG_ASSESSMENT_ID' => 'Deg Assessment ID',
            'COURSE_WORK_RATIO' => 'Course Work Ratio',
            'EXAM_RATIO' => 'Exam Ratio',
            'DEGREE_CODE' => 'Degree Code',
            'EFFECTIVE_START_DATE' => 'Effective Start Date',
            'EFFECTIVE_END_DATE' => 'Effective End Date',
            'STATUS' => 'Status',
        ];
    }
}
