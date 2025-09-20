<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 15-12-2020 20:50:22 
 * @modify date 15-12-2020 20:50:22 
 * @desc [description]
 */

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.UON_STUDENTS".
 *
 * @property string|null $INDEX_NUMBER K.C.S.E index number
 * @property string|null $MARITAL_STATUS Student's marital stutus
 * @property string $OTHER_NAMES Other names that the student uses
 * @property string|null $RELIGION Student's religion
 * @property string $REGISTRATION_NUMBER The student's registration number at the university
 * @property string|null $ROOM__NUMBER Student's room nuber in the halls
 * @property string|null $REMARKS Some remarks about the student
 * @property string $SEX Student's gender
 * @property string|null $STUDENT_PHOTO An pass port size photo of the  student
 * @property string $SURNAME Student's surname
 * @property string|null $DEGREE_CODE The code for the degree programme the student is enrolled for
 * @property string|null $DATE_OF_REGISTRATION The date the student registers for the a programme
 * @property string|null $DATE_OF_COMPLETION The date the student clears his/her course at the university
 * @property int|null $ENTRY_YEAR The calendar year the student joined the university
 * @property string|null $SERIAL_NUMBER A four digit number unique for every student in given year of  entry
 * @property int|null $YEAR_OF_STUDY The current year of study for a student
 * @property string|null $METHOD_OF_STUDY The method of study that applies only to Postgaduate students.  Eg. Thesis
 * @property string|null $DIS_DISTRICT_CODE A 3 digit code represting a district
 * @property string|null $RHALL_HALL_CODE The unique code for the hall
 * @property string $D_PROG_DEGREE_CODE A unque code for the degree programme, e.g P15
 * @property string $STC_STUDENT_CATEGORY_ID A unique code for each student category
 * @property string $CIT_COUNTRY_CODE
 * @property string|null $DIS_DISTRICT_CODE_RESIDES_IN A 3 digit code represting a district
 * @property string $SSPONSOR_CODE
 * @property string|null $GRADUATION_YEAR
 * @property string|null $SDEPT_CODE
 * @property string|null $STAFF_STATUS
 * @property string|null $STUDENT_ADDRESS
 * @property string|null $ARCH_DATE
 * @property string|null $INTAKE_NAME
 * @property string|null $STUDENT_STATUS
 * @property string|null $SIGN
 * @property float|null $CURR_BALANCE
 * @property string|null $BIRTH_DATE
 * @property string|null $ACADEMIC_YEAR
 * @property string|null $NATIONAL_ID
 * @property string|null $USER_ID
 * @property string|null $LAST_UPDATE
 * @property string|null $EMAIL
 * @property string|null $STUDY_CENTRE
 * @property string|null $A_REASON
 * @property string|null $TELEPHONE
 * @property int|null $KCSE_YEAR
 * @property string|null $CURRENCY_ID
 * @property int|null $BIO_INFO
 */
class UonStudent extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.UON_STUDENTS';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['OTHER_NAMES', 'REGISTRATION_NUMBER', 'SEX', 'SURNAME', 'STC_STUDENT_CATEGORY_ID', 'CIT_COUNTRY_CODE', 'SSPONSOR_CODE'], 'required'],
            [['ENTRY_YEAR', 'YEAR_OF_STUDY', 'KCSE_YEAR', 'BIO_INFO'], 'integer'],
            [['CURR_BALANCE'], 'number'],
            [['INDEX_NUMBER', 'REGISTRATION_NUMBER', 'ROOM__NUMBER', 'ACADEMIC_YEAR'], 'string', 'max' => 20],
            [['MARITAL_STATUS', 'SERIAL_NUMBER', 'RHALL_HALL_CODE', 'STAFF_STATUS', 'STUDY_CENTRE'], 'string', 'max' => 10],
            [['OTHER_NAMES', 'STUDENT_PHOTO', 'INTAKE_NAME', 'A_REASON', 'TELEPHONE'], 'string', 'max' => 100],
            [['RELIGION', 'SURNAME', 'METHOD_OF_STUDY', 'NATIONAL_ID'], 'string', 'max' => 30],
            [['REMARKS', 'USER_ID'], 'string', 'max' => 50],
            [['SEX', 'DATE_OF_REGISTRATION', 'DATE_OF_COMPLETION', 'GRADUATION_YEAR', 'ARCH_DATE', 'BIRTH_DATE', 'LAST_UPDATE'], 'string', 'max' => 7],
            [['DEGREE_CODE', 'DIS_DISTRICT_CODE', 'D_PROG_DEGREE_CODE', 'STC_STUDENT_CATEGORY_ID', 'CIT_COUNTRY_CODE', 'DIS_DISTRICT_CODE_RESIDES_IN', 'SSPONSOR_CODE', 'SDEPT_CODE'], 'string', 'max' => 5],
            [['STUDENT_ADDRESS'], 'string', 'max' => 200],
            [['STUDENT_STATUS'], 'string', 'max' => 15],
            [['SIGN'], 'string', 'max' => 40],
            [['EMAIL'], 'string', 'max' => 60],
            [['CURRENCY_ID'], 'string', 'max' => 3],
            [['REGISTRATION_NUMBER'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'INDEX_NUMBER' => 'Index Number',
            'MARITAL_STATUS' => 'Marital Status',
            'OTHER_NAMES' => 'Other Names',
            'RELIGION' => 'Religion',
            'REGISTRATION_NUMBER' => 'Registration Number',
            'ROOM__NUMBER' => 'Room Number',
            'REMARKS' => 'Remarks',
            'SEX' => 'Sex',
            'STUDENT_PHOTO' => 'Student Photo',
            'SURNAME' => 'Surname',
            'DEGREE_CODE' => 'Degree Code',
            'DATE_OF_REGISTRATION' => 'Date Of Registration',
            'DATE_OF_COMPLETION' => 'Date Of Completion',
            'ENTRY_YEAR' => 'Entry Year',
            'SERIAL_NUMBER' => 'Serial Number',
            'YEAR_OF_STUDY' => 'Year Of Study',
            'METHOD_OF_STUDY' => 'Method Of Study',
            'DIS_DISTRICT_CODE' => 'Dis District Code',
            'RHALL_HALL_CODE' => 'Rhall Hall Code',
            'D_PROG_DEGREE_CODE' => 'D Prog Degree Code',
            'STC_STUDENT_CATEGORY_ID' => 'Stc Student Category ID',
            'CIT_COUNTRY_CODE' => 'Cit Country Code',
            'DIS_DISTRICT_CODE_RESIDES_IN' => 'Dis District Code Resides In',
            'SSPONSOR_CODE' => 'Ssponsor Code',
            'GRADUATION_YEAR' => 'Graduation Year',
            'SDEPT_CODE' => 'Sdept Code',
            'STAFF_STATUS' => 'Staff Status',
            'STUDENT_ADDRESS' => 'Student Address',
            'ARCH_DATE' => 'Arch Date',
            'INTAKE_NAME' => 'Intake Name',
            'STUDENT_STATUS' => 'Student Status',
            'SIGN' => 'Sign',
            'CURR_BALANCE' => 'Curr Balance',
            'BIRTH_DATE' => 'Birth Date',
            'ACADEMIC_YEAR' => 'Academic Year',
            'NATIONAL_ID' => 'National ID',
            'USER_ID' => 'User ID',
            'LAST_UPDATE' => 'Last Update',
            'EMAIL' => 'Email',
            'STUDY_CENTRE' => 'Study Centre',
            'A_REASON' => 'A Reason',
            'TELEPHONE' => 'Telephone',
            'KCSE_YEAR' => 'Kcse Year',
            'CURRENCY_ID' => 'Currency ID',
            'BIO_INFO' => 'Bio Info',
        ];
    }


    /**
     * Format telephone number
     * 
     * @return string telephone number as 07xxxxxxxx | '' | INVALID NO.
     */
    public static function formatTelephone($telephone):string
    {
        if(is_null($telephone))
            $telephone = 'not provided';

        elseif(strlen($telephone) === 9)
            $telephone = '0'.$telephone;

        elseif(strlen($telephone) === 13)
            $telephone = '0'.substr($telephone,4);
        
        elseif (strlen($telephone) !== 10)
            $telephone = 'invalid';

        return $telephone;
    }


    /**
     * Check if email is valid
     * 
     * @return string email |INVALID EMAIL
     */
    public static function validateEmail($email):string
    {
        if(is_null($email))
            $email = 'not provided';
        else{
            if (filter_var($email, FILTER_VALIDATE_EMAIL))
                $email = $email;
            else
                $email = 'invalid';
        }  
        return $email;
    }

    /**
     * @return ActiveQuery
     */
    public function getDegreeProgramme(): ActiveQuery
    {
        return $this->hasOne(DegreeProgramme::class, ['DEGREE_CODE' => 'DEGREE_CODE']);
    }
}
