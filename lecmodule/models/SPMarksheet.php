<?php

namespace app\models;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\Connection;

/**
 * This is the model class for table "marksheets".
 *
 * @property string $marksheet_id
 * @property string $registration_number
 * @property string $exam_type
 * @property string $class_code
 * @property float|null $course_work
 * @property string|null $final_grade
 * @property string|null $course_remarks
 * @property string|null $reg_status
 * @property int|null $reconcile_status
 * @property string|null $terminal_ip
 * @property string|null $last_update
 * @property int|null $publish_status
 * @property string|null $publish_date
 */
class SPMarksheet extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'smis.marksheets';
    }

    /**
     * @return Connection the database connection used by this AR class.
     * @throws InvalidConfigException
     */
    public static function getDb(): Connection
    {
        return Yii::$app->get('db2');
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['marksheet_id', 'registration_number', 'exam_type', 'class_code'], 'required'],
            [['course_work'], 'number'],
            [['reconcile_status', 'publish_status'], 'integer'],
            [['last_update', 'publish_date'], 'safe'],
            [['marksheet_id'], 'string', 'max' => 50],
            [['registration_number'], 'string', 'max' => 20],
            [['exam_type'], 'string', 'max' => 12],
            [['class_code'], 'string', 'max' => 20],
            [['final_grade'], 'string', 'max' => 2],
            [['course_remarks'], 'string', 'max' => 250],
            [['reg_status'], 'string', 'max' => 12],
            [['terminal_ip'], 'string', 'max' => 30],
            [['marksheet_id', 'registration_number'], 'unique', 'targetAttribute' => ['marksheet_id', 'registration_number']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'marksheet_id' => 'Marksheet ID',
            'registration_number' => 'Registration Number',
            'exam_type' => 'Exam Type',
            'class_code' => 'Class Code',
            'course_work' => 'Course Work (CAT)',
            'final_grade' => 'Final Grade',
            'course_remarks' => 'Course Remarks',
            'reg_status' => 'Registration Status',
            'reconcile_status' => 'Reconcile Status',
            'terminal_ip' => 'Terminal IP',
            'last_update' => 'Last Update',
            'publish_status' => 'Publish Status',
            'publish_date' => 'Publish Date',
        ];
    }
}