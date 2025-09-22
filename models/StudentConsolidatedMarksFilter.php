<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

namespace app\models;

use yii\base\Model;

/**
 * This is the model behind the filters for consolidated marks per student
 * @property string $academicYear
 * @property string $degreeCode
 * @property string $group
 * @property string $levelOfStudy
 * @property string $approvalLevel
 */
class StudentConsolidatedMarksFilter extends Model
{
    public $academicYear;
    public $degreeCode;
    public $group;
    public $levelOfStudy;
    public $approvalLevel;

    /**
     * @return array the validation rules.
     */
    public function rules(): array
    {
        return [
            [['academicYear', 'degreeCode', 'group', 'levelOfStudy', 'approvalLevel'], 'string'],
            [['academicYear', 'degreeCode', 'group', 'levelOfStudy', 'approvalLevel'], 'trim'],
            [['academicYear', 'degreeCode', 'group', 'levelOfStudy', 'approvalLevel'], 'default'],
            [['academicYear', 'degreeCode', 'group', 'approvalLevel'], 'required'],
        ];
    }

    /**
     * @return string[]
     */
    public function attributeLabels(): array
    {
        return [
            'academicYear' => 'Academic year',
            'degreeCode' => 'Degree name',
            'group' => 'Group',
            'levelOfStudy' => 'Level of study',
            'approvalLevel' => 'Approval level'
        ];
    }
}