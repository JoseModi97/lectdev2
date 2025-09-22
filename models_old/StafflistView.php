<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @desc [description]
 */

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "HRMIS.STAFFLIST_VIEW".
 *
 * @property int $PAYROLL_NO
 * @property int $EMP_ID
 * @property string $SURNAME
 * @property string $OTHER_NAMES
 * @property int $EMPLOY_ID
 * @property string $APPOINT_DATE
 * @property string|null $ACTUAL_GRADE
 * @property string $DEPT_CODE
 * @property string $DEPT_NAME
 * @property string $COL_CODE
 * @property string $COL_NAME
 * @property string $FACULTY_NAME
 * @property string $FAC_CODE
 * @property int $DESG_ID
 * @property string $DESG_NAME
 * @property string $GRADE_CODE
 * @property string|null $GRADE_DESCR
 * @property int $UNION_ID
 * @property string $UNION_ABBREV
 * @property string $UNION_NAME
 * @property string|null $JOB_ID
 * @property string|null $JOB_CADRE
 * @property string $EMP_TITLE
 * @property string $BIRTH_DATE
 * @property string $DOCUMENT_NO
 * @property int|null $DOC_TYPE_ID
 * @property string|null $PIN_NO
 * @property string $NATIONALITY
 * @property string|null $NHIF_NO
 * @property string|null $NSSF_NO
 * @property string|null $GENDER
 * @property string $HOME_DISTRICT
 * @property string $TRIBE_NAME
 * @property string $DOC_DESCR
 * @property string|null $WORK_STATION
 * @property int $STAFF_STATUS
 * @property string $STATUS_DESC
 * @property int $EMP_TYPE_ID
 * @property string $EMPLOYMENT_TYPE
 * @property int $TERM_APPOINT
 * @property string $TERM_DESCR
 * @property int|null $OVERALL_STATUS
 * @property string|null $EMAIL
 * @property string|null $MOBILE_NO
 * @property int $RETIRE_AGE
 * @property string|null $UNIT_DESC
 * @property string $SORT_CODE
 */
class StaffListView extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'HRMIS.STAFFLIST_VIEW';
    }

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
            [['PAYROLL_NO', 'EMP_ID', 'SURNAME', 'OTHER_NAMES', 'EMPLOY_ID', 'APPOINT_DATE', 'DEPT_CODE', 'DEPT_NAME', 'COL_CODE', 'COL_NAME', 'FACULTY_NAME', 'FAC_CODE', 'DESG_ID', 'DESG_NAME', 'GRADE_CODE', 'UNION_ID', 'UNION_ABBREV', 'UNION_NAME', 'EMP_TITLE', 'BIRTH_DATE', 'DOCUMENT_NO', 'NATIONALITY', 'HOME_DISTRICT', 'TRIBE_NAME', 'DOC_DESCR', 'STAFF_STATUS', 'STATUS_DESC', 'EMP_TYPE_ID', 'EMPLOYMENT_TYPE', 'TERM_APPOINT', 'TERM_DESCR', 'RETIRE_AGE', 'SORT_CODE'], 'required'],
            [['PAYROLL_NO', 'EMP_ID', 'EMPLOY_ID', 'DESG_ID', 'UNION_ID', 'DOC_TYPE_ID', 'STAFF_STATUS', 'EMP_TYPE_ID', 'TERM_APPOINT', 'OVERALL_STATUS', 'RETIRE_AGE'], 'integer'],
            [['SURNAME', 'PIN_NO', 'NHIF_NO', 'NSSF_NO', 'MOBILE_NO'], 'string', 'max' => 15],
            [['OTHER_NAMES', 'HOME_DISTRICT', 'TRIBE_NAME', 'DOC_DESCR', 'STATUS_DESC', 'TERM_DESCR'], 'string', 'max' => 25],
            [['APPOINT_DATE', 'BIRTH_DATE'], 'string', 'max' => 7],
            [['ACTUAL_GRADE', 'DOCUMENT_NO', 'SORT_CODE'], 'string', 'max' => 20],
            [['DEPT_CODE', 'COL_CODE', 'FAC_CODE', 'GRADE_CODE', 'EMP_TITLE'], 'string', 'max' => 6],
            [['DEPT_NAME', 'FACULTY_NAME', 'DESG_NAME', 'UNION_NAME', 'EMAIL', 'UNIT_DESC'], 'string', 'max' => 100],
            [['COL_NAME'], 'string', 'max' => 60],
            [['GRADE_DESCR', 'JOB_ID', 'JOB_CADRE', 'WORK_STATION'], 'string', 'max' => 50],
            [['UNION_ABBREV'], 'string', 'max' => 12],
            [['NATIONALITY'], 'string', 'max' => 5],
            [['GENDER'], 'string', 'max' => 8],
            [['EMPLOYMENT_TYPE'], 'string', 'max' => 40],
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
            'EMPLOY_ID' => 'Employ ID',
            'APPOINT_DATE' => 'Appoint Date',
            'ACTUAL_GRADE' => 'Actual Grade',
            'DEPT_CODE' => 'Dept Code',
            'DEPT_NAME' => 'Dept Name',
            'COL_CODE' => 'Col Code',
            'COL_NAME' => 'Col Name',
            'FACULTY_NAME' => 'Faculty Name',
            'FAC_CODE' => 'Fac Code',
            'DESG_ID' => 'Desg ID',
            'DESG_NAME' => 'Desg Name',
            'GRADE_CODE' => 'Grade Code',
            'GRADE_DESCR' => 'Grade Descr',
            'UNION_ID' => 'Union ID',
            'UNION_ABBREV' => 'Union Abbrev',
            'UNION_NAME' => 'Union Name',
            'JOB_ID' => 'Job ID',
            'JOB_CADRE' => 'Job Cadre',
            'EMP_TITLE' => 'Emp Title',
            'BIRTH_DATE' => 'Birth Date',
            'DOCUMENT_NO' => 'Document No',
            'DOC_TYPE_ID' => 'Doc Type ID',
            'PIN_NO' => 'Pin No',
            'NATIONALITY' => 'Nationality',
            'NHIF_NO' => 'Nhif No',
            'NSSF_NO' => 'Nssf No',
            'GENDER' => 'Gender',
            'HOME_DISTRICT' => 'Home District',
            'TRIBE_NAME' => 'Tribe Name',
            'DOC_DESCR' => 'Doc Descr',
            'WORK_STATION' => 'Work Station',
            'STAFF_STATUS' => 'Staff Status',
            'STATUS_DESC' => 'Status Desc',
            'EMP_TYPE_ID' => 'Emp Type ID',
            'EMPLOYMENT_TYPE' => 'Employment Type',
            'TERM_APPOINT' => 'Term Appoint',
            'TERM_DESCR' => 'Term Descr',
            'OVERALL_STATUS' => 'Overall Status',
            'EMAIL' => 'Email',
            'MOBILE_NO' => 'Mobile No',
            'RETIRE_AGE' => 'Retire Age',
            'UNIT_DESC' => 'Unit Desc',
            'SORT_CODE' => 'Sort Code',
        ];
    }
}
