<?php

/**
 * @author Jack Jmm
 * @email jackmutiso37@gmail.com
 * @create date 12-9-2025 20:50:22 
 * @desc 
 */

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.COURSES".
 *
 * @property string $COURSE_ID
 * @property string $COURSE_CODE A unique code for the course
 * @property string $COURSE_NAME The full name of the course
 * @property int $COURSE_SEMESTER The semester which the course is taken, e.g 1, 2...
 * @property int $COURSE_YEAR_OF_STUDY The year of study that the course is undertaken
 * @property int $ACADEMIC_HOURS The number of hours the course take
 * @property string|null $DEPT_CODE
 * @property string|null $COURSE_URL
 * @property string|null $CORE_COURSE
 * @property int|null $CREDIT_FACTOR
 * @property string|null $MAJOR_CODE
 * @property string|null $COMMON_COURSE
 * @property float|null $COURSE_COURSE
 * @property string|null $CATEGORY
 * @property int|null $PASS_MARK
 * @property int|null $PROJECT_COURSE
 * @property float|null $BILLING_FACTOR
 * @property int $HAS_COURSE_WORK
 * @property int $IS_COMMON
 * @property Department $dept
 */
class Course extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.COURSES';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['COURSE_ID', 'COURSE_CODE', 'COURSE_NAME', 'COURSE_SEMESTER', 'COURSE_YEAR_OF_STUDY'], 'required'],
            [['COURSE_SEMESTER', 'COURSE_YEAR_OF_STUDY', 'ACADEMIC_HOURS', 'CREDIT_FACTOR', 'PASS_MARK', 'PROJECT_COURSE', 'HAS_COURSE_WORK', 'IS_COMMON'], 'integer'],
            [['COURSE_COURSE', 'BILLING_FACTOR'], 'number'],
            [['COURSE_ID', 'MAJOR_CODE'], 'string', 'max' => 20],
            [['COURSE_CODE'], 'string', 'max' => 15],
            [['COURSE_NAME'], 'string', 'max' => 256],
            [['DEPT_CODE'], 'string', 'max' => 6],
            [['COURSE_URL'], 'string', 'max' => 80],
            [['CORE_COURSE'], 'string', 'max' => 8],
            [['COMMON_COURSE', 'CATEGORY'], 'string', 'max' => 1],
            [['COURSE_ID'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'COURSE_ID' => 'Course ID',
            'COURSE_CODE' => 'Course Code',
            'COURSE_NAME' => 'Course Name',
            'COURSE_SEMESTER' => 'Course Semester',
            'COURSE_YEAR_OF_STUDY' => 'Course Year Of Study',
            'ACADEMIC_HOURS' => 'Academic Hours',
            'DEPT_CODE' => 'Dept Code',
            'COURSE_URL' => 'Course Url',
            'CORE_COURSE' => 'Core Course',
            'CREDIT_FACTOR' => 'Credit Factor',
            'MAJOR_CODE' => 'Major Code',
            'COMMON_COURSE' => 'Common Course',
            'COURSE_COURSE' => 'Course Course',
            'CATEGORY' => 'Category',
            'PASS_MARK' => 'Pass Mark',
            'PROJECT_COURSE' => 'Project Course',
            'BILLING_FACTOR' => 'Billing Factor',
            'HAS_COURSE_WORK' => 'Has Course Work',
            'IS_COMMON' => 'Is Common',
        ];
    }

    /**
     * @return ActiveQuery [type]
     */
    public function getDept(): ActiveQuery
    {
        return $this->hasOne(Department::class, ['DEPT_CODE' => 'DEPT_CODE']);
    }
}
