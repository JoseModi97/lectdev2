<?php

/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.MARKSHEETS".
 *
 * @property string $MRKSHEET_ID
 * @property string $REGISTRATION_NUMBER
 * @property string $EXAM_TYPE
 * @property float|null $COURSE_MARKS
 * @property float|null $EXAM_MARKS
 * @property float|null $FINAL_MARKS
 * @property string|null $GRADE
 * @property string|null $REMARKS
 * @property string|null $POST_STATUS
 * @property string|null $USERID
 * @property string|null $ENTRY_DATE
 * @property string|null $LAST_UPDATE
 * @property float|null $INVOICE_DETAIL_ID
 * @property string|null $REGISTRATION_STATUS
 * @property int|null $ASSESSMENT
 * @property string|null $CLASS_CODE
 * @property string|null $SOURCE_IP
 * @property string|null $TIME_STAMP
 * @property int|null $PUBLISH_STATUS
 * @property int|null $TRANSFER_STATUS
 */
class Marksheet extends ActiveRecord
{
    public $cnt;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.MARKSHEETS';
    }

    public static function primaryKey(): array
    {
        return ['MRKSHEET_ID', 'REGISTRATION_NUMBER'];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['MRKSHEET_ID', 'REGISTRATION_NUMBER', 'EXAM_TYPE'], 'required'],
            [['COURSE_MARKS', 'EXAM_MARKS', 'FINAL_MARKS', 'INVOICE_DETAIL_ID'], 'number'],
            [['ASSESSMENT', 'PUBLISH_STATUS', 'TRANSFER_STATUS'], 'integer'],
            [['MRKSHEET_ID'], 'string', 'max' => 60],
            [['REGISTRATION_NUMBER', 'USERID', 'REGISTRATION_STATUS', 'CLASS_CODE'], 'string', 'max' => 20],
            [['EXAM_TYPE'], 'string', 'max' => 12],
            [['GRADE'], 'string', 'max' => 8],
            [['REMARKS'], 'string', 'max' => 240],
            [['POST_STATUS'], 'string', 'max' => 80],
            [['ENTRY_DATE', 'LAST_UPDATE'], 'safe'],
            [['SOURCE_IP', 'TIME_STAMP'], 'string', 'max' => 40],
            [['MRKSHEET_ID', 'REGISTRATION_NUMBER'], 'unique', 'targetAttribute' => ['MRKSHEET_ID', 'REGISTRATION_NUMBER']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'MRKSHEET_ID' => 'Mrksheet ID',
            'REGISTRATION_NUMBER' => 'Registration Number',
            'EXAM_TYPE' => 'Exam Type',
            'COURSE_MARKS' => 'Course Marks',
            'EXAM_MARKS' => 'Exam Marks',
            'FINAL_MARKS' => 'Final Marks',
            'GRADE' => 'Grade',
            'REMARKS' => 'Remarks',
            'POST_STATUS' => 'Post Status',
            'USERID' => 'Userid',
            'ENTRY_DATE' => 'Entry Date',
            'LAST_UPDATE' => 'Last Update',
            'INVOICE_DETAIL_ID' => 'Invoice Detail ID',
            'REGISTRATION_STATUS' => 'Registration Status',
            'ASSESSMENT' => 'Assessment',
            'CLASS_CODE' => 'Class Code',
            'SOURCE_IP' => 'Source Ip',
            'TIME_STAMP' => 'Time Stamp',
            'PUBLISH_STATUS' => 'Publish Status',
            'TRANSFER_STATUS' => 'Transfer Status'
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getStudent(): ActiveQuery
    {
        return $this->hasOne(UonStudent::class, ['REGISTRATION_NUMBER' => 'REGISTRATION_NUMBER']);
    }

    /**
     * @return ActiveQuery
     */
    public function getExamType(): ActiveQuery
    {
        return $this->hasOne(ExamType::class, ['EXAMTYPE_CODE' => 'EXAM_TYPE']);
    }

    public function getMarksheetDef()
    {
        return $this->hasOne(MarksheetDef::class, ['MRKSHEET_ID' => 'MRKSHEET_ID']);
    }

    public function getCourseDescription()
    {
        return $this->marksheetDef && $this->marksheetDef->course
            ? "{$this->marksheetDef->course->COURSE_CODE} - {$this->marksheetDef->course->COURSE_NAME}"
            : 'N/A';
    }
    public function getSemesterCode()
    {
        return $this->marksheetDef && $this->marksheetDef->semester
            ? $this->marksheetDef->semester->SEMESTER_CODE ?? 'N/A'
            : 'N/A';
    }
}
