<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 */

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.LEC_STUDENT_COURSE_WORK".
 *
 * @property int $COURSE_WORK_ID
 * @property int|null $ASSESSMENT_ID
 * @property float|null $MARK
 * @property string|null $LECTURER_APPROVAL_STATUS
 * @property string|null $HOD_APPROVAL_STATUS
 * @property string|null $DEAN_APPROVAL_STATUS
 * @property string|null $REGISTRATION_NUMBER
 * @property int|null $USER_ID
 * @property string|null $DATE_ENTERED
 * @property string|null $REMARKS
 * @property string|null $MARK_TYPE
 * @property float|null $RAW_MARK
 * @property int|null $PROCESSED_AS_FINAL
 * @property float|null $PUBLISHED_MARKS
 * @property int|null $IS_CONSOLIDATED
 */
class StudentCoursework extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.LEC_STUDENT_COURSE_WORK';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['COURSE_WORK_ID', 'ASSESSMENT_ID', 'USER_ID', 'PROCESSED_AS_FINAL', 'IS_CONSOLIDATED'], 'integer'],
            [['MARK', 'RAW_MARK', 'PUBLISHED_MARKS'], 'number'],
            [['LECTURER_APPROVAL_STATUS', 'HOD_APPROVAL_STATUS', 'DEAN_APPROVAL_STATUS'], 'string', 'max' => 10],
            [['REGISTRATION_NUMBER'], 'string', 'max' => 30],
            [['DATE_ENTERED'], 'safe'],
            [['REMARKS'], 'string', 'max' => 255],
            [['MARK_TYPE'], 'string', 'max' => 100],
            [['COURSE_WORK_ID'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'COURSE_WORK_ID' => 'Course Work ID',
            'ASSESSMENT_ID' => 'Assessment ID',
            'MARK' => 'Mark',
            'LECTURER_APPROVAL_STATUS' => 'Lecturer Approval Status',
            'HOD_APPROVAL_STATUS' => 'Hod Approval Status',
            'DEAN_APPROVAL_STATUS' => 'Dean Approval Status',
            'REGISTRATION_NUMBER' => 'Registration Number',
            'USER_ID' => 'User ID',
            'DATE_ENTERED' => 'Date Entered',
            'REMARKS' => 'Remarks',
            'MARK_TYPE' => 'Mark Type',
            'RAW_MARK' => 'Raw Mark',
            'PROCESSED_AS_FINAL' => 'Processed As Final',
            'PUBLISHED_MARKS' => 'Published Marks',
            'IS_CONSOLIDATED' => 'Is Consolidated',
        ];
    }

    /**
     * @return ActiveQuery [type]
     */
    public function getAssessment(): ActiveQuery
    {
        return $this->hasOne(CourseWorkAssessment::class, ['ASSESSMENT_ID' => 'ASSESSMENT_ID']);
    }

    /**
     * @return ActiveQuery [type]
     */
    public function getStudent(): ActiveQuery
    {
        return $this->hasOne(UonStudent::class, ['REGISTRATION_NUMBER' => 'REGISTRATION_NUMBER']);
    }
}
