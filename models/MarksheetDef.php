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
 * This is the model class for table "MUTHONI.MARKSHEET_DEF".
 *
 * @property string $MRKSHEET_ID
 * @property string|null $GROUP_CODE
 * @property string $COURSE_ID
 * @property string|null $SEMESTER_ID
 * @property string|null $EXAM_DATE
 * @property string|null $EXAM_ROOM
 * @property string|null $EXAM_TIME
 * @property int|null $PAYROLL_NO
 * @property string|null $IN_EXAMINER
 * @property string|null $EX_EXAMINER
 * @property float|null $MEAN_MARK
 * @property string|null $ENTRY_DATE
 * @property string|null $USERID
 * @property string|null $LAST_UPDATE
 * @property int|null $STUDENT_NUMBER
 * @property string|null $CLASS_CODE
 * @property int|null $FROM_HR
 * @property int|null $FROM_MIN
 * @property int|null $TO_HR
 * @property int|null $TO_MIN
 * @property Semester $semester
 * @Property Course $course
 * @Property Timetable $timetable
 * @Property Group $group
 * @property Marksheet $marksheet
 */
class MarksheetDef extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.MARKSHEET_DEF';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['MRKSHEET_ID', 'COURSE_ID'], 'required'],
            [['PAYROLL_NO', 'STUDENT_NUMBER', 'FROM_HR', 'FROM_MIN', 'TO_HR', 'TO_MIN'], 'integer'],
            [['MEAN_MARK'], 'number'],
            [['MRKSHEET_ID'], 'string', 'max' => 60],
            [['GROUP_CODE'], 'string', 'max' => 15],
            [['COURSE_ID', 'EXAM_TIME', 'USERID', 'CLASS_CODE'], 'string', 'max' => 20],
            [['SEMESTER_ID'], 'string', 'max' => 40],
            [['EXAM_DATE', 'ENTRY_DATE', 'LAST_UPDATE'], 'safe'],
            [['EXAM_ROOM', 'IN_EXAMINER', 'EX_EXAMINER'], 'string', 'max' => 50],
            [['MRKSHEET_ID'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'MRKSHEET_ID' => 'Mrksheet ID',
            'GROUP_CODE' => 'Group Code',
            'COURSE_ID' => 'Course ID',
            'SEMESTER_ID' => 'Semester ID',
            'EXAM_DATE' => 'Exam Date',
            'EXAM_ROOM' => 'Exam Room',
            'EXAM_TIME' => 'Exam Time',
            'PAYROLL_NO' => 'Payroll No',
            'IN_EXAMINER' => 'In Examiner',
            'EX_EXAMINER' => 'Ex Examiner',
            'MEAN_MARK' => 'Mean Mark',
            'ENTRY_DATE' => 'Entry Date',
            'USERID' => 'Userid',
            'LAST_UPDATE' => 'Last Update',
            'STUDENT_NUMBER' => 'Student Number',
            'CLASS_CODE' => 'Class Code',
            'FROM_HR' => 'From Hr',
            'FROM_MIN' => 'From Min',
            'TO_HR' => 'To Hr',
            'TO_MIN' => 'To Min',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getSemester(): ActiveQuery
    {
        return $this->hasOne(Semester::class, ['SEMESTER_ID' => 'SEMESTER_ID']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCourse(): ActiveQuery
    {
        return $this->hasOne(Course::class, ['COURSE_ID' => 'COURSE_ID']);
    }

    /**
     * @return ActiveQuery
     */
    public function getGroup(): ActiveQuery
    {
        return $this->hasOne(Group::class, ['GROUP_CODE' => 'GROUP_CODE']);
    }

    /**
     * @return ActiveQuery
     */
    public function getMarksheet(): ActiveQuery
    {
        return $this->hasOne(Marksheet::class,['MRKSHEET_ID' => 'MRKSHEET_ID']);
    }

    /**
     * @return ActiveQuery
     */
    public function getTimetable(): ActiveQuery
    {
        return $this->hasOne(Timetable::class, ['MRKSHEET_ID' => 'MRKSHEET_ID']);
    }
}
