<?php

/**
 * @author Jack Jmm
 * @email jackmutiso37@gmail.com
 * @create date 12-9-2025 20:50:22 
 * @desc 
 */
namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.LEC_COURSE_ASSIGNMENT".
 *
 * @property int $LEC_ASSIGNMENT_ID
 * @property string|null $MRKSHEET_ID
 * @property int|null $PAYROLL_NO
 * @property string|null $ASSIGNMENT_DATE
 * @property MarksheetDef $marksheetDef
 * @property EmpVerifyView $staff
 */
class CourseAssignment extends ActiveRecord
{
    public $coursesNumber;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.LEC_COURSE_ASSIGNMENT';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['LEC_ASSIGNMENT_ID', 'PAYROLL_NO'], 'integer'],
            [['MRKSHEET_ID'], 'string', 'max' => 60],
            [['ASSIGNMENT_DATE'], 'safe'],
            [['LEC_ASSIGNMENT_ID'], 'unique'],
        ];
    }
 
    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'LEC_ASSIGNMENT_ID' => 'Lec Assignment ID',
            'MRKSHEET_ID' => 'Mrksheet ID',
            'PAYROLL_NO' => 'Payroll No',
            'ASSIGNMENT_DATE' => 'Assignment Date',
        ];
    }

    /**
     * @return ActiveQuery for MarksheetDef
     */
    public function getMarksheetDef(): ActiveQuery
    {
        return $this->hasOne(MarksheetDef::class, ['MRKSHEET_ID' => 'MRKSHEET_ID']);
    }

    /**
     * @return ActiveQuery for allocated lecturer
     */
    public function getStaff(): ActiveQuery
    {
        return $this->hasOne(EmpVerifyView::class, ['PAYROLL_NO' => 'PAYROLL_NO']);
    }
}
