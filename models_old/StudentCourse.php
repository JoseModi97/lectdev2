<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.STUDENT_COURSES".
 *
 * @property string $COURSE_REGISTRATION_ID
 * @property string $PROGRESS_CODE
 * @property string $COURSE_ID
 * @property string $EXAMTYPE_CODE
 * @property float|null $FINAL_MARK
 * @property string|null $GRADE
 * @property string|null $PAY_PER_COURSE
 * @property string|null $RESULT_STATUS
 * @property string|null $LAST_UPDATE
 * @property string|null $USERID
 * @property string|null $GROUP_CODE
 * @property string|null $LOCK_STATUS
 * @property int|null $LEVEL_OF_STUDY
 * @property int|null $FINAL
 * @property string|null $MRKSHEET_ID
 * @property float|null $COURSE_MARK
 * @property float|null $EXAM_MARK
 * @property string|null $REMARKS
 * @property int|null $PUBLISH_STATUS
 */
class StudentCourse extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.STUDENT_COURSES';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['COURSE_REGISTRATION_ID', 'PROGRESS_CODE', 'COURSE_ID', 'EXAMTYPE_CODE'], 'required'],
            [['FINAL_MARK', 'COURSE_MARK', 'EXAM_MARK'], 'number'],
            [['LEVEL_OF_STUDY', 'FINAL', 'PUBLISH_STATUS'], 'integer'],
            [['LAST_UPDATE'], 'safe'],
            [['COURSE_REGISTRATION_ID'], 'string', 'max' => 100],
            [['PROGRESS_CODE'], 'string', 'max' => 50],
            [['COURSE_ID'], 'string', 'max' => 20],
            [['EXAMTYPE_CODE'], 'string', 'max' => 10],
            [['GRADE'], 'string', 'max' => 8],
            [['PAY_PER_COURSE'], 'string', 'max' => 1],
            [['RESULT_STATUS'], 'string', 'max' => 20],
            [['USERID'], 'string', 'max' => 20],
            [['GROUP_CODE'], 'string', 'max' => 15],
            [['LOCK_STATUS'], 'string', 'max' => 8],
            [['MRKSHEET_ID'], 'string', 'max' => 60],
            [['REMARKS'], 'string', 'max' => 240],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'COURSE_REGISTRATION_ID' => 'Course Registration ID',
            'PROGRESS_CODE' => 'Progress Code',
            'COURSE_ID' => 'Course ID',
            'EXAMTYPE_CODE' => 'Exam Type Code',
            'FINAL_MARK' => 'Final Mark',
            'GRADE' => 'Grade',
            'PAY_PER_COURSE' => 'Pay Per Course',
            'RESULT_STATUS' => 'Result Status',
            'LAST_UPDATE' => 'Last Update',
            'USERID' => 'User ID',
            'GROUP_CODE' => 'Group Code',
            'LOCK_STATUS' => 'Lock Status',
            'LEVEL_OF_STUDY' => 'Level Of Study',
            'FINAL' => 'Final',
            'MRKSHEET_ID' => 'Mark Sheet ID',
            'COURSE_MARK' => 'Course Mark',
            'EXAM_MARK' => 'Exam Mark',
            'REMARKS' => 'Remarks',
            'PUBLISH_STATUS' => 'Publish Status',
        ];
    }
}