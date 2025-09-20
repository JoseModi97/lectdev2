<?php
/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 */

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "HRMIS.EMP_VERIFY".
 *
 * @property int $PAYROLL_NO
 * @property int $EMP_ID
 * @property string $SURNAME
 * @property string $OTHER_NAMES
 * @property string $EMP_TITLE
 * @property string|null $SEX
 * @property string $STATUS_DESC
 * @property string|null $STATUS_CAT
 * @property int|null $OVERALL_STATUS
 * @property string $IDPP_NO
 * @property string|null $PIN_NO
 * @property string $DEPT_NAME
 * @property string $DEPT_CODE
 * @property string $FAC_CODE
 * @property string $FACULTY_NAME
 * @property string|null $EMAIL
 * @property string|null $MOBILE
 * @property string|null $JOB_CADRE
 */
class EmpVerifyView extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'HRMIS.EMP_VERIFY_LECTURER';
    }

    /**
     * {@inheritdoc}
     */
    public static function primaryKey(): array
    {
        return ['EMP_ID'];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['PAYROLL_NO', 'EMP_ID', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE', 'STATUS_DESC', 'IDPP_NO', 'DEPT_NAME', 'DEPT_CODE', 'FAC_CODE', 'FACULTY_NAME'], 'required'],
            [['PAYROLL_NO', 'EMP_ID', 'OVERALL_STATUS'], 'integer'],
            [['SURNAME', 'PIN_NO', 'MOBILE'], 'string', 'max' => 15],
            [['OTHER_NAMES', 'STATUS_DESC'], 'string', 'max' => 25],
            [['EMP_TITLE', 'DEPT_CODE', 'FAC_CODE'], 'string', 'max' => 6],
            [['SEX'], 'string', 'max' => 8],
            [['STATUS_CAT', 'IDPP_NO'], 'string', 'max' => 20],
            [['DEPT_NAME'], 'string', 'max' => 1000],
            [['FACULTY_NAME', 'EMAIL'], 'string', 'max' => 100],
            [['JOB_CADRE'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'PAYROLL_NO' => 'Payroll No',
            'EMP_ID' => 'Emp ID',
            'SURNAME' => 'Surname',
            'OTHER_NAMES' => 'Other Names',
            'EMP_TITLE' => 'Emp Title',
            'SEX' => 'Sex',
            'STATUS_DESC' => 'Status Desc',
            'STATUS_CAT' => 'Status Cat',
            'OVERALL_STATUS' => 'Overall Status',
            'IDPP_NO' => 'Idpp No',
            'PIN_NO' => 'Pin No',
            'DEPT_NAME' => 'Dept Name',
            'DEPT_CODE' => 'Dept Code',
            'FAC_CODE' => 'Fac Code',
            'FACULTY_NAME' => 'Faculty Name',
            'EMAIL' => 'Email',
            'MOBILE' => 'Mobile',
            'JOB_CADRE' => 'Job Cadre',
        ];
    }
}
