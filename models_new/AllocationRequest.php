<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */
namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.LEC_ALLOCATION_REQUESTS".
 *
 * @property int $REQUEST_ID
 * @property int|null $STATUS_ID
 * @property string|null $MARKSHEET_ID
 * @property string|null $REMARKS
 * @property string|null $REQUESTING_DEPT
 * @property string|null $SERVICING_DEPT
 * @property string|null $REQUEST_DATE
 * @property int $REQUEST_BY
 * @property int|null $ATTENDED_BY
 * @property string|null $ATTENDED_DATE
 * @property Marksheet $marksheet
 * @property Department $requestingDept
 * @property Department $servicingDept
 * @property EmpVerifyView $requestedBy
 * @property EmpVerifyView $attendedBy
 * @property AllocationStatus $status
 */
class AllocationRequest extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.LEC_ALLOCATION_REQUESTS';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['REQUEST_BY'], 'required'],
            [['REQUEST_ID', 'STATUS_ID', 'REQUEST_BY', 'ATTENDED_BY'], 'integer'],
            [['MARKSHEET_ID'], 'string', 'max' => 60],
            [['REMARKS'], 'string', 'max' => 1024],
            [['REQUESTING_DEPT', 'SERVICING_DEPT'], 'string', 'max' => 10],
            [['REQUEST_DATE', 'ATTENDED_DATE'], 'safe'],
            [['REQUEST_ID'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'REQUEST_ID' => 'Request ID',
            'STATUS_ID' => 'Status ID',
            'MARKSHEET_ID' => 'Marksheet ID',
            'REMARKS' => 'Remarks',
            'REQUESTING_DEPT' => 'Requesting Dept',
            'SERVICING_DEPT' => 'Servicing Dept',
            'REQUEST_DATE' => 'Request Date',
            'REQUEST_BY' => 'Request By',
            'ATTENDED_BY' => 'Attended By',
            'ATTENDED_DATE' => 'Attended Date',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getMarksheet(): ActiveQuery
    {
        return $this->hasOne(MarksheetDef::class, ['MRKSHEET_ID' => 'MARKSHEET_ID']);
    }

    /**
     * @return ActiveQuery
     */
    public function getRequestingDept(): ActiveQuery
    {
        return $this->hasOne(Department::class, ['DEPT_CODE' => 'REQUESTING_DEPT']);
    }

    /**
     * @return ActiveQuery
     */
    public function getServicingDept(): ActiveQuery
    {
        return $this->hasOne(Department::class, ['DEPT_CODE' => 'SERVICING_DEPT']);
    }

    /**
     * @return ActiveQuery
     */
    public function getRequestedBy(): ActiveQuery
    {
        return $this->hasOne(EmpVerifyView::class, ['PAYROLL_NO' => 'REQUEST_BY']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAttendedBy(): ActiveQuery
    {
        return $this->hasOne(EmpVerifyView::class, ['PAYROLL_NO' => 'ATTENDED_BY']);
    }

    /**
     * @return ActiveQuery
     */
    public function getStatus(): ActiveQuery
    {
        return $this->hasOne(AllocationStatus::class, ['STATUS_ID' => 'STATUS_ID']);
    }
}
