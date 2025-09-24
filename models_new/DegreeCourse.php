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
 * This is the model class for table "MUTHONI.DEGREE_COURSES".
 *
 * @property string $COURSE_ID
 * @property string $DEGREE_CODE
 * @property string|null $MANDATORY
 * @property int|null $PASSMARK
 * @property int|null $LEVEL_OF_STUDY
 * @property string $ACADEMIC_YEAR
 * @property int|null $SEMESTER
 * @property float|null $CREDIT_FACTOR
 * @property string|null $PREREQUISITE_ID
 * @property string|null $COURSE_CATEGORY
 */
class DegreeCourse extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.DEGREE_COURSES';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['COURSE_ID', 'DEGREE_CODE', 'ACADEMIC_YEAR'], 'required'],
            [['PASSMARK', 'LEVEL_OF_STUDY', 'SEMESTER'], 'integer'],
            [['CREDIT_FACTOR'], 'number'],
            [['COURSE_ID', 'ACADEMIC_YEAR', 'PREREQUISITE_ID', 'COURSE_CATEGORY'], 'string', 'max' => 20],
            [['DEGREE_CODE', 'MANDATORY'], 'string', 'max' => 8],
            [['DEGREE_CODE', 'ACADEMIC_YEAR', 'COURSE_ID'], 'unique', 'targetAttribute' => ['DEGREE_CODE', 'ACADEMIC_YEAR', 'COURSE_ID']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'COURSE_ID' => 'Course ID',
            'DEGREE_CODE' => 'Degree Code',
            'MANDATORY' => 'Mandatory',
            'PASSMARK' => 'Passmark',
            'LEVEL_OF_STUDY' => 'Level Of Study',
            'ACADEMIC_YEAR' => 'Academic Year',
            'SEMESTER' => 'Semester',
            'CREDIT_FACTOR' => 'Credit Factor',
            'PREREQUISITE_ID' => 'Prerequisite ID',
            'COURSE_CATEGORY' => 'Course Category',
        ];
    }
}
