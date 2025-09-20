<?php

namespace app\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "MUTHONI.LEC_LATE_MARKS_COMMENTS".
 *
 * @property int $LATE_M_COMM_ID
 * @property int $LATE_MARK_ID
 * @property string|null $APPROVER_LEVEL
 * @property string|null $USERNAME
 * @property string|null $COMMENT_DATE
 * @property string|null $APPROVER_COMMENT
 */
class LecLateMarkComment extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'MUTHONI.LEC_LATE_MARKS_COMMENTS';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [


            [['APPROVER_COMMENT'], 'default', 'value' => ''],
            [['LATE_MARK_ID'], 'required'],
            [['LATE_M_COMM_ID', 'LATE_MARK_ID'], 'integer'],
            [['APPROVER_LEVEL'], 'string', 'max' => 10],
            [['USERNAME'], 'string', 'max' => 20],
            [['COMMENT_DATE'], 'safe'],
            [['APPROVER_COMMENT'], 'string', 'max' => 300],
            [['LATE_M_COMM_ID'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'LATE_M_COMM_ID' => 'Late M Comm ID',
            'LATE_MARK_ID' => 'Late Mark ID',
            'APPROVER_LEVEL' => 'Approver Level',
            'USERNAME' => 'Username',
            'COMMENT_DATE' => 'Comment Date',
            'APPROVER_COMMENT' => 'Approver Comment',
        ];
    }
    /**
     * Automatically assign the primary key using the Oracle sequence.
     *
     * @param boolean $insert whether this is a new record insertion.
     * @return boolean whether the insertion should continue.
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert && empty($this->LATE_M_COMM_ID)) {
                $this->LATE_M_COMM_ID = Yii::$app->db->createCommand("SELECT MUTHONI.LEC_DEGREE_PROG_USERS_SEQ.NEXTVAL FROM dual")
                    ->queryScalar();
            }
            return true;
        }
        return false;
    }
}
