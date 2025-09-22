<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.LEC_EXAM_UPDATE_APPROVALS".
 *
 * @property int $EXAM_APPROVAL_ID
 * @property int|null $COURSEWORK_ID
 * @property string $APPROVAL_DATE
 * @property int|null $APPROVAL_LEVEL_ID
 * @property int|null $RECOMMENDED_MARKS
 * @property int|null $CHANGED_FROM
 * @property int|null $CHANGD_TO
 * @property string|null $REMARKS
 * @property int|null $APPROVER_ID
 */
class ExamUpdateApproval extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.LEC_EXAM_UPDATE_APPROVALS';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['RECOMMENDED_MARKS', 'CHANGD_TO','APPROVAL_DATE'], 'required'],
            [['EXAM_APPROVAL_ID', 'COURSEWORK_ID', 'APPROVAL_LEVEL_ID', 'RECOMMENDED_MARKS', 'CHANGED_FROM', 'CHANGD_TO', 'APPROVER_ID'], 'integer'],
            [['APPROVAL_DATE'], 'safe'],
            [['REMARKS'], 'string', 'max' => 1024],
            [['EXAM_APPROVAL_ID'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'EXAM_APPROVAL_ID' => 'Exam Approval ID',
            'COURSEWORK_ID' => 'Coursework ID',
            'APPROVAL_DATE' => 'Approval Date',
            'APPROVAL_LEVEL_ID' => 'Approval Level ID',
            'RECOMMENDED_MARKS' => 'Recommended Marks',
            'CHANGED_FROM' => 'Changed From',
            'CHANGD_TO' => 'New Marks',
            'REMARKS' => 'Remarks',
            'APPROVER_ID' => 'Approver ID',
        ];
    }
}
