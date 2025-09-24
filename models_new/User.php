<?php

/**
 * @author Jack Jmm
 * @email jackmutiso37@gmail.com
 * @create date 12-9-2025 20:50:22 
 * @desc 
 */

namespace app\models;

use Yii;
use Exception;
use yii\db\ActiveRecord;
use yii\db\Exception as dbException;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "HRMIS.EMP_LOGIN_VIEW".
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


/** 
 * Our validation doesn't use access tokens or auth keys. 
 * However, the interface demands we provide an impelementation of these methods.
 * Therefore, we only provide an empty body for them.
 */
class User extends ActiveRecord implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'HRMIS.EMP_LOGIN_VIEW';
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
            [['PAYROLL_NO', 'EMP_ID', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE', 'STATUS_DESC'], 'required'],
            [['PAYROLL_NO', 'EMP_ID', 'OVERALL_STATUS'], 'integer'],
            [['SURNAME', 'MOBILE_NO'], 'string', 'max' => 15],
            [['OTHER_NAMES', 'STATUS_DESC'], 'string', 'max' => 25],
            [['EMP_TITLE'], 'string', 'max' => 6],
            [['STATUS_CAT'], 'string', 'max' => 20],
            [['EMAIL'], 'string', 'max' => 100],
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
            'STATUS_DESC' => 'Status Desc',
            'STATUS_CAT' => 'Status Cat',
            'OVERALL_STATUS' => 'Overall Status',
            'EMAIL' => 'Email',
            'MOBILE_NO' => 'Mobile No',
        ];
    }

    /**
     * Finds an identity by the given ID.
     *
     * @param $id
     * @return User|IdentityInterface|null
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->getPrimaryKey();
    }

    public static function findIdentityByAccessToken($token, $type = null) {}

    public function getAuthKey() {}

    public function validateAuthKey($authKey) {}

    /**
     * Get the user trying to log in
     *
     * @param int $payrollNo username
     * @return ActiveRecord|array
     * @throws Exception
     */
    public static function findByUsername(int $payrollNo)
    {
        $user = static::find()->where(['PAYROLL_NO' => $payrollNo])->one();
        if (is_null($user)) {
            throw new Exception('User not found.');
        }
        return $user;
    }

    /**
     * Check for user roles and set them in the session
     *
     * @throws dbException
     * @throws Exception
     */
    public function checkRoles()
    {
        $sql = "SELECT GRANTED_ROLE FROM USER_ROLE_PRIVS WHERE USERNAME = :payrollNo";
        $connection = Yii::$app->getDb();
        $grantedRoles = $connection->createCommand($sql)->bindValues([':payrollNo' => $this->PAYROLL_NO])->queryAll();
        if (empty($grantedRoles)) {
            throw new Exception('You may not have the necessary permissions to continue.');
        }

        $rolesToCheck = [
            'LEC_SMIS_LECTURER',
            'LEC_SMIS_HOD',
            'LEC_SMIS_DEAN',
            'LEC_SMIS_FAC_ADMIN',
            'LEC_SMIS_SYS_ADMIN',
            'SMIS_DEVELOPER'
        ];

        $userRoles = [];
        foreach ($grantedRoles as $grantedRole) {
            $role = $grantedRole['GRANTED_ROLE'];
            if (in_array($role, $rolesToCheck)) {
                $userRoles[] = $role;
            }
        }

        if (empty($userRoles)) {
            throw new Exception('You may not have the necessary permissions to continue.');
        } else {
            Yii::$app->session->set('roles', $userRoles);
        }
    }
}
