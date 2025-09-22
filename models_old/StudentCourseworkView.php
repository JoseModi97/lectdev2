<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 15-12-2020 20:50:22 
 * @modify date 15-12-2020 20:50:22 
 * @desc [description]
 */

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.STUDENT_COURSEWORK_VIEW".
 *
 * @property string $ASSESSMENT_NAME
 * @property int|null $ASSESSMENT_TYPE_ID
 * @property float|null $WEIGHT
 * @property string|null $RESULT_DUE_DATE
 * @property string $COURSE_ID
 * @property string|null $MARKSHEET_ID
 * @property int|null $PAYROLL_NO
 * @property float|null $MARK
 * @property string|null $REGISTRATION_NUMBER
 * @property int|null $USER_ID
 * @property string|null $DATE_ENTERED
 * @property int|null $ASSESSMENT_ID
 * @property string|null $DEGREE_CODE
 * @property string $SURNAME
 * @property string $OTHER_NAMES
 * @property string $D_PROG_DEGREE_CODE
 * @property string $DEGREE_TYPE
 * @property string $DEGREE_NAME
 * @property string $FACUL_FAC_CODE
 * @property string $FACULTY_NAME
 * @property string $COL_CODE
 * @property string $COL_NAME
 * @property int $COURSE_WORK_ID
 * @property string|null $REMARKS
 * @property string|null $MARK_TYPE
 * @property float|null $RAW_MARK
 */
class StudentCourseworkView extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.STUDENT_COURSEWORK_VIEW';
    }

    public static function primaryKey(): array
    {
        return ['COURSE_WORK_ID'];
    }
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['ASSESSMENT_NAME', 'COURSE_ID', 'SURNAME', 'OTHER_NAMES', 'D_PROG_DEGREE_CODE', 'DEGREE_TYPE', 'DEGREE_NAME', 'FACUL_FAC_CODE', 'FACULTY_NAME', 'COL_CODE', 'COL_NAME', 'COURSE_WORK_ID'], 'required'],
            [['ASSESSMENT_TYPE_ID', 'PAYROLL_NO', 'USER_ID', 'ASSESSMENT_ID', 'COURSE_WORK_ID'], 'integer'],
            [['WEIGHT', 'MARK', 'RAW_MARK'], 'number'],
            [['ASSESSMENT_NAME', 'COURSE_ID'], 'string', 'max' => 20],
            [['RESULT_DUE_DATE', 'DATE_ENTERED'], 'string', 'max' => 7],
            [['MARKSHEET_ID', 'FACULTY_NAME'], 'string', 'max' => 60],
            [['REGISTRATION_NUMBER', 'SURNAME'], 'string', 'max' => 30],
            [['DEGREE_CODE', 'D_PROG_DEGREE_CODE'], 'string', 'max' => 5],
            [['OTHER_NAMES', 'COL_NAME', 'MARK_TYPE'], 'string', 'max' => 100],
            [['DEGREE_TYPE'], 'string', 'max' => 40],
            [['DEGREE_NAME'], 'string', 'max' => 180],
            [['FACUL_FAC_CODE'], 'string', 'max' => 3],
            [['COL_CODE'], 'string', 'max' => 6],
            [['REMARKS'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'ASSESSMENT_NAME' => 'Assessment Name',
            'ASSESSMENT_TYPE_ID' => 'Assessment Type ID',
            'WEIGHT' => 'Weight',
            'RESULT_DUE_DATE' => 'Result Due Date',
            'COURSE_ID' => 'Course ID',
            'MARKSHEET_ID' => 'Marksheet ID',
            'PAYROLL_NO' => 'Payroll No',
            'MARK' => 'Mark',
            'REGISTRATION_NUMBER' => 'Registration Number',
            'USER_ID' => 'User ID',
            'DATE_ENTERED' => 'Date Entered',
            'ASSESSMENT_ID' => 'Assessment ID',
            'DEGREE_CODE' => 'Degree Code',
            'SURNAME' => 'Surname',
            'OTHER_NAMES' => 'Other Names',
            'D_PROG_DEGREE_CODE' => 'D Prog Degree Code',
            'DEGREE_TYPE' => 'Degree Type',
            'DEGREE_NAME' => 'Degree Name',
            'FACUL_FAC_CODE' => 'Facul Fac Code',
            'FACULTY_NAME' => 'Faculty Name',
            'COL_CODE' => 'Col Code',
            'COL_NAME' => 'Col Name',
            'COURSE_WORK_ID' => 'Course Work ID',
            'REMARKS' => 'Remarks',
            'MARK_TYPE' => 'Mark Type',
            'RAW_MARK' => 'Raw Mark',
        ];
    }
}
