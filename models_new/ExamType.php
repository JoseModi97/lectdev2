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
 * This is the model class for table "MUTHONI.EXAM_TYPES".
 *
 * @property string $EXAMTYPE_CODE
 * @property string $DESCRIPTION
 * @property int|null $EXAM_PRIORITY
 * @property string|null $ENTRY_TYPE
 * @property string|null $GRADE_POSTFIX
 */
class ExamType extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.EXAM_TYPES';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['EXAMTYPE_CODE', 'DESCRIPTION'], 'required'],
            [['EXAM_PRIORITY'], 'integer'],
            [['EXAMTYPE_CODE'], 'string', 'max' => 10],
            [['DESCRIPTION'], 'string', 'max' => 30],
            [['ENTRY_TYPE'], 'string', 'max' => 12],
            [['GRADE_POSTFIX'], 'string', 'max' => 2],
            [['EXAMTYPE_CODE'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'EXAMTYPE_CODE' => 'Examtype Code',
            'DESCRIPTION' => 'Description',
            'EXAM_PRIORITY' => 'Exam Priority',
            'ENTRY_TYPE' => 'Entry Type',
            'GRADE_POSTFIX' => 'Grade Postfix',
        ];
    }
}
