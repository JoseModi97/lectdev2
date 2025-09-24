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
 * This is the model class for table "MUTHONI.DEGREE_PROGRAMMES".
 *
 * @property string $DEGREE_CODE A unque code for the degree programme, e.g P15
 * @property string $DEGREE_NAME The actual name of the degree programme
 * @property string $DEGREE_TYPE A field for describing the degree type, either post graduate or undergraduate etc
 * @property int $DURATION
 * @property string $UNIV_UNIVERISTY_CODE A unique code for the unversity, eg. UON
 * @property string $FACUL_FAC_CODE
 * @property string $CLUST_CLUSTER_NUMBER A unique number for the cluster
 * @property int $GRADINGSYSTEM
 * @property string|null $DEGREE_URL
 * @property string|null $PROG_TYPE
 * @property string|null $FULL_NAME
 * @property string|null $DEG_MODE
 * @property int|null $COURSE_REG_TYPE
 * @property string|null $PHYSICAL_LOCATION
 * @property int|null $BILLABLE
 * @property string $DEG_PREFIX
 * @property string|null $DEGREE_STATUS
 * @property int|null $TRANSCRIPT_FACTOR
 * @property float|null $PASS_MARK
 * @property string|null $BILLING_START_YEAR
 * @property int|null $ANNUAL_SEMESTERS
 * @property int|null $MAX_UNITS
 * @property string|null $ONLINE_SUPPORT
 * @property string|null $AWARD_ROUNDING
 * @property string|null $AVERAGE_TYPE
 * @property int|null $BILLING_TYPE
 * @property string|null $P_DIMENSION 
 */
class DegreeProgramme extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.DEGREE_PROGRAMMES';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['DEGREE_CODE', 'DEGREE_NAME', 'DEGREE_TYPE', 'DURATION', 'UNIV_UNIVERISTY_CODE', 'FACUL_FAC_CODE', 'CLUST_CLUSTER_NUMBER', 'GRADINGSYSTEM', 'DEG_PREFIX'], 'required'],
            [['DURATION', 'GRADINGSYSTEM', 'COURSE_REG_TYPE', 'BILLABLE', 'TRANSCRIPT_FACTOR', 'ANNUAL_SEMESTERS', 'MAX_UNITS', 'BILLING_TYPE'], 'integer'],
            [['PASS_MARK'], 'number'],
            [['DEGREE_CODE', 'UNIV_UNIVERISTY_CODE', 'CLUST_CLUSTER_NUMBER'], 'string', 'max' => 5],
            [['DEGREE_NAME', 'FULL_NAME'], 'string', 'max' => 180],
            [['DEGREE_TYPE'], 'string', 'max' => 40],
            [['FACUL_FAC_CODE'], 'string', 'max' => 6],
            [['DEGREE_URL'], 'string', 'max' => 120],
            [['PROG_TYPE', 'BILLING_START_YEAR', 'AWARD_ROUNDING', 'AVERAGE_TYPE'], 'string', 'max' => 20],
            [['DEG_MODE'], 'string', 'max' => 16],
            [['PHYSICAL_LOCATION'], 'string', 'max' => 150],
            [['DEG_PREFIX', 'ONLINE_SUPPORT'], 'string', 'max' => 12],
            [['DEGREE_STATUS'], 'string', 'max' => 30],
            [['P_DIMENSION '], 'string', 'max' => 50],
            [['DEGREE_CODE'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'DEGREE_CODE' => 'Degree Code',
            'DEGREE_NAME' => 'Degree Name',
            'DEGREE_TYPE' => 'Degree Type',
            'DURATION' => 'Duration',
            'UNIV_UNIVERISTY_CODE' => 'Univ Univeristy Code',
            'FACUL_FAC_CODE' => 'Facul Fac Code',
            'CLUST_CLUSTER_NUMBER' => 'Clust Cluster Number',
            'GRADINGSYSTEM' => 'Gradingsystem',
            'DEGREE_URL' => 'Degree Url',
            'PROG_TYPE' => 'Prog Type',
            'FULL_NAME' => 'Full Name',
            'DEG_MODE' => 'Deg Mode',
            'COURSE_REG_TYPE' => 'Course Reg Type',
            'PHYSICAL_LOCATION' => 'Physical Location',
            'BILLABLE' => 'Billable',
            'DEG_PREFIX' => 'Deg Prefix',
            'DEGREE_STATUS' => 'Degree Status',
            'TRANSCRIPT_FACTOR' => 'Transcript Factor',
            'PASS_MARK' => 'Pass Mark',
            'BILLING_START_YEAR' => 'Billing Start Year',
            'ANNUAL_SEMESTERS' => 'Annual Semesters',
            'MAX_UNITS' => 'Max Units',
            'ONLINE_SUPPORT' => 'Online Support',
            'AWARD_ROUNDING' => 'Award Rounding',
            'AVERAGE_TYPE' => 'Average Type',
            'BILLING_TYPE' => 'Billing Type',
            'P_DIMENSION ' => 'P Dimension',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getDepartment(): ActiveQuery
    {
        return $this->hasOne(Department::class, ['FAC_CODE' => 'FACUL_FAC_CODE']);
    }

    /**
     * @return ActiveQuery
     */
    public function getGradingSystem(): ActiveQuery
    {
        return $this->hasOne(GradingSystem::class, ['GRADINGCODE' => 'GRADINGSYSTEM']);
    }

    /**
     * @return ActiveQuery
     */
    public function getFaculty(): ActiveQuery
    {
        return $this->hasOne(Faculty::class, ['FAC_CODE' => 'FACUL_FAC_CODE']);
    }
}
