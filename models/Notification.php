<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.LEC_NOTIFICATIONS".
 *
 * @property int|null $NOTIFICATION_ID
 * @property int|null $EXAM_APPROVAL_ID
 * @property string|null $MESSAGE_SUBJECT
 * @property string|null $MESSAGE_BODY
 * @property string|null $RECEIPIENT
 * @property int|null $RECEIPIENT_USER_ID
 * @property string|null $DELIVERY_STATUS
 * @property string|null $ERROR_MESSAGES
 * @property string|null $DATE_TIME_SENT
 * @property string|null $PURPOSE
 */
class Notification extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.LEC_NOTIFICATIONS';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['NOTIFICATION_ID', 'EXAM_APPROVAL_ID', 'RECEIPIENT_USER_ID'], 'integer'],
            [['DATE_TIME_SENT'], 'safe'],
            [['MESSAGE_SUBJECT', 'RECEIPIENT', 'ERROR_MESSAGES', 'PURPOSE'], 'string', 'max' => 255],
            [['MESSAGE_BODY'], 'string', 'max' => 1024],
            [['DELIVERY_STATUS'], 'string', 'max' => 15],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'NOTIFICATION_ID' => 'Notification ID',
            'EXAM_APPROVAL_ID' => 'Exam Approval ID',
            'MESSAGE_SUBJECT' => 'Message Subject',
            'MESSAGE_BODY' => 'Message Body',
            'RECEIPIENT' => 'Receipient',
            'RECEIPIENT_USER_ID' => 'Receipient User ID',
            'DELIVERY_STATUS' => 'Delivery Status',
            'ERROR_MESSAGES' => 'Error Messages',
            'DATE_TIME_SENT' => 'Date Time Sent',
            'PURPOSE' => 'Purpose',
        ];
    }
}
