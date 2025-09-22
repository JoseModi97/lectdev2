<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.LEC_MARKSHEETS".
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
 */
class TempMarksheet extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.LEC_MARKSHEETS';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['MRKSHEET_ID', 'REGISTRATION_NUMBER'], 'required'],
            [['COURSE_MARKS', 'EXAM_MARKS', 'FINAL_MARKS', 'INVOICE_DETAIL_ID'], 'number'],
            [['ASSESSMENT', 'PUBLISH_STATUS', 'MARKS_COMPLETE'], 'integer'],
            [['MRKSHEET_ID'], 'string', 'max' => 60],
            [['REGISTRATION_NUMBER', 'USERID', 'REGISTRATION_STATUS', 'CLASS_CODE'], 'string', 'max' => 20],
            [['EXAM_TYPE'], 'string', 'max' => 12],
            [['GRADE'], 'string', 'max' => 8],
            [['REMARKS'], 'string', 'max' => 240],
            [['POST_STATUS'], 'string', 'max' => 80],
            [['ENTRY_DATE', 'LAST_UPDATE'], 'safe'],
            [['SOURCE_IP', 'TIME_STAMP'], 'string', 'max' => 40],
            [['COURSE_CODE'], 'string', 'max' => 15],
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
            'MARKS_COMPLETE' => 'Marks Complete',
            'COURSE_CODE' => 'Course Code',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getStudent(): ActiveQuery
    {
        return $this->hasOne(UonStudent::class, ['REGISTRATION_NUMBER' => 'REGISTRATION_NUMBER']);
    }
}
