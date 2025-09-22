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
 * This is the model class for table "MUTHONI.SEMESTERS".
 *
 * @property string $SEMESTER_ID
 * @property string $ACADEMIC_YEAR
 * @property string $DEGREE_CODE
 * @property int $LEVEL_OF_STUDY
 * @property int $SEMESTER_CODE
 * @property string|null $INTAKE_CODE
 * @property string $START_DATE
 * @property string $END_DATE
 * @property string|null $FIRST_SEMESTER
 * @property string|null $SEMESTER_NAME
 * @property string|null $CLOSING_DATE
 * @property string|null $ADMIN_USER
 * @property string|null $GROUP_CODE
 * @property string|null $REGISTRATION_DEADLINE
 * @property string|null $DESCRIPTION_CODE
 * @property string|null $SESSION_TYPE
 * @property string|null $DISPLAY_DATE
 * @property string|null $REGISTRATION_DATE
 * @property string|null $SEMESTER_TYPE
 * @property LevelOfStudy $levelOfStudy
 * @property SemesterDescription $semesterDescription
 * @property DegreeProgramme $degreeProgramme
 * @property Group $group
 */
class Semester extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.SEMESTERS';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['SEMESTER_ID', 'ACADEMIC_YEAR', 'DEGREE_CODE', 'LEVEL_OF_STUDY', 'SEMESTER_CODE', 'START_DATE', 'END_DATE'], 'required'],
            [['LEVEL_OF_STUDY', 'SEMESTER_CODE'], 'integer'],
            [['SEMESTER_ID', 'FIRST_SEMESTER'], 'string', 'max' => 40],
            [['ACADEMIC_YEAR', 'GROUP_CODE', 'SEMESTER_TYPE'], 'string', 'max' => 20],
            [['DEGREE_CODE', 'INTAKE_CODE', 'SEMESTER_NAME', 'DESCRIPTION_CODE'], 'string', 'max' => 12],
            [['START_DATE', 'END_DATE', 'CLOSING_DATE', 'REGISTRATION_DEADLINE', 'DISPLAY_DATE', 'REGISTRATION_DATE'], 'string', 'max' => 7],
            [['ADMIN_USER'], 'string', 'max' => 255],
            [['SESSION_TYPE'], 'string', 'max' => 15],
            [['SEMESTER_ID'], 'unique'],
            [['purpose', 'ACADEMIC_YEAR', 'DEGREE_CODE'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'SEMESTER_ID' => 'Semester ID',
            'ACADEMIC_YEAR' => 'Academic Year',
            'DEGREE_CODE' => 'Degree Code',
            'LEVEL_OF_STUDY' => 'Level Of Study',
            'SEMESTER_CODE' => 'Semester Code',
            'INTAKE_CODE' => 'Intake Code',
            'START_DATE' => 'Start Date',
            'END_DATE' => 'End Date',
            'FIRST_SEMESTER' => 'First Semester',
            'SEMESTER_NAME' => 'Semester Name',
            'CLOSING_DATE' => 'Closing Date',
            'ADMIN_USER' => 'Admin User',
            'GROUP_CODE' => 'Group Code',
            'REGISTRATION_DEADLINE' => 'Registration Deadline',
            'DESCRIPTION_CODE' => 'Description Code',
            'SESSION_TYPE' => 'Session Type',
            'DISPLAY_DATE' => 'Display Date',
            'REGISTRATION_DATE' => 'Registration Date',
            'SEMESTER_TYPE' => 'Semester Type',
        ];
    }

    /**
     * Gets query for degree programme
     * @return ActiveQuery
     */
    public function getDegreeProgramme(): ActiveQuery
    {
        return $this->hasOne(DegreeProgramme::class, ['DEGREE_CODE' => 'DEGREE_CODE']);
    }

    /**
     * Gets query for level of semester description
     * @return ActiveQuery
     */
    public function getSemesterDescription(): ActiveQuery
    {
        return $this->hasOne(SemesterDescription::class, ['DESCRIPTION_CODE' => 'DESCRIPTION_CODE']);
    }

    /**
     * Gets query for level of study
     * @return ActiveQuery
     */
    public function getLevelOfStudy(): ActiveQuery
    {
        return $this->hasOne(LevelOfStudy::class, ['LEVEL_OF_STUDY' => 'LEVEL_OF_STUDY']);
    }

    /**
     * Gets query for groups
     * @return ActiveQuery
     */
    public function getGroup(): ActiveQuery
    {
        return $this->hasOne(Group::class, ['GROUP_CODE' => 'GROUP_CODE']);
    }
}
