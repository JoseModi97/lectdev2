<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.LEC_TIMETABLE".
 *
 * @property string $MRKSHEET_ID
 * @property string $LECTURE_ID
 * @property string|null $LECTURE_ROOM
 * @property string|null $LECTURE_TIME
 * @property int|null $PAYROLL_NO
 * @property string|null $LECTURER
 * @property int|null $DAY_ID
 * @property int|null $FROM_HR
 * @property int|null $FROM_MIN
 * @property int|null $TO_HR
 * @property int|null $TO_MIN
 * @property string|null $CLASS_CODE
 * @property string|null $SESSION_ID
 * @property string|null $USERID
 * @property string|null $LAST_UPDATE
 * @property string|null $COURSE_LEADER
 * @property MarksheetDef $marksheetDef
 */
class Timetable extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.LEC_TIMETABLE';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['MRKSHEET_ID', 'LECTURE_ID'], 'required'],
            [['PAYROLL_NO', 'DAY_ID', 'FROM_HR', 'FROM_MIN', 'TO_HR', 'TO_MIN'], 'integer'],
            [['MRKSHEET_ID'], 'string', 'max' => 60],
            [['LECTURE_ID'], 'string', 'max' => 8],
            [['LECTURE_ROOM'], 'string', 'max' => 40],
            [['LECTURE_TIME', 'USERID'], 'string', 'max' => 30],
            [['LECTURER'], 'string', 'max' => 50],
            [['CLASS_CODE', 'COURSE_LEADER'], 'string', 'max' => 20],
            [['SESSION_ID'], 'string', 'max' => 12],
            [['LAST_UPDATE'], 'string', 'max' => 7],
            [['MRKSHEET_ID', 'LECTURE_ID'], 'unique', 'targetAttribute' => ['MRKSHEET_ID', 'LECTURE_ID']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'MRKSHEET_ID' => 'Mrksheet ID',
            'LECTURE_ID' => 'Lecture ID',
            'LECTURE_ROOM' => 'Lecture Room',
            'LECTURE_TIME' => 'Lecture Time',
            'PAYROLL_NO' => 'Payroll No',
            'LECTURER' => 'Lecturer',
            'DAY_ID' => 'Day ID',
            'FROM_HR' => 'From Hr',
            'FROM_MIN' => 'From Min',
            'TO_HR' => 'To Hr',
            'TO_MIN' => 'To Min',
            'CLASS_CODE' => 'Class Code',
            'SESSION_ID' => 'Session ID',
            'USERID' => 'Userid',
            'LAST_UPDATE' => 'Last Update',
            'COURSE_LEADER' => 'Course Leader',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getMarksheetDef(): ActiveQuery
    {
        return $this->hasOne(MarksheetDef::class, ['MRKSHEET_ID' => 'MRKSHEET_ID']);
    }
}
