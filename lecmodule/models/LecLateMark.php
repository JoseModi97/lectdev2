<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "MUTHONI.LEC_LATE_MARKS".
 *
 * @property string $MRKSHEET_ID
 * @property string $REGISTRATION_NUMBER
 * @property string|null $EXAM_TYPE
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
 * @property int|null $MARKS_COMPLETE
 * @property string|null $COURSE_CODE
 * @property string|null $MARKS_SOURCE
 * @property int $LEC_LATE_MARKS_ID
 * @property int|null $LECTURER_APPROVAL
 * @property int|null $HOD_APPROVAL
 * @property int|null $DEAN_APPROVAL
 * @property string|null $RECORD_VALIDITY
 * @property int|null $RECORD_STATUS
 */
class LecLateMark extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'MUTHONI.LEC_LATE_MARKS';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['COURSE_MARKS', 'EXAM_MARKS', 'FINAL_MARKS', 'INVOICE_DETAIL_ID', 'ASSESSMENT', 'PUBLISH_STATUS', 'MARKS_COMPLETE', 'RECORD_STATUS'], 'default', 'value' => null],
            [['RECORD_VALIDITY'], 'default', 'value' => ''],
            [['DEAN_APPROVAL'], 'default', 'value' => 0],
            [['COURSE_MARKS', 'EXAM_MARKS', 'FINAL_MARKS', 'INVOICE_DETAIL_ID'], 'number'],
            [['ASSESSMENT', 'PUBLISH_STATUS', 'MARKS_COMPLETE', 'LEC_LATE_MARKS_ID', 'LECTURER_APPROVAL', 'HOD_APPROVAL', 'DEAN_APPROVAL', 'RECORD_STATUS'], 'integer'],
            [['LEC_LATE_MARKS_ID'], 'required'],
            [['MRKSHEET_ID'], 'string', 'max' => 60],
            [['REGISTRATION_NUMBER', 'USERID', 'REGISTRATION_STATUS', 'CLASS_CODE', 'RECORD_VALIDITY'], 'string', 'max' => 20],
            [['EXAM_TYPE'], 'string', 'max' => 12],
            [['GRADE'], 'string', 'max' => 8],
            [['REMARKS', 'MARKS_SOURCE'], 'string', 'max' => 240],
            [['POST_STATUS'], 'string', 'max' => 80],
            [['ENTRY_DATE', 'LAST_UPDATE'], 'safe'],
            [['SOURCE_IP', 'TIME_STAMP'], 'string', 'max' => 40],
            [['COURSE_CODE'], 'string', 'max' => 15],
            [['LEC_LATE_MARKS_ID'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
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
            'MARKS_COMPLETE' => 'Marks Complete',
            'COURSE_CODE' => 'Course Code',
            'MARKS_SOURCE' => 'Marks Source',
            'LEC_LATE_MARKS_ID' => 'Lec Late Marks ID',
            'LECTURER_APPROVAL' => 'Lecturer Approval',
            'HOD_APPROVAL' => 'Hod Approval',
            'DEAN_APPROVAL' => 'Dean Approval',
            'RECORD_VALIDITY' => 'Record Validity',
            'RECORD_STATUS' => 'Record Status',
        ];
    }
    public static function primaryKey()
    {
        return ['LEC_LATE_MARKS_ID', 'MRKSHEET_ID', 'REGISTRATION_NUMBER'];
    }
}
