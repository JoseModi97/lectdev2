<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

namespace app\models;

use yii\base\Model;

/**
 * This is the model behind the filters for course analysis
 * @property string $academicYear
 * @property string $degreeCode
 * @property string $group
 * @property string $levelOfStudy
 * @property string $semester
 * @property string $courseCode
 * @property string $courseName
 * @property string $approvalLevel
 * @property string $restrictedTo
 */
class CourseAnalysisFilter extends Model
{
    public $academicYear;
    public $degreeCode;
    public $group;
    public $levelOfStudy;
    public $semester;
    public $courseCode;
    public $courseName;
    public $approvalLevel;
    public $restrictedTo;

    /**
     * @return array the validation rules.
     */
    public function rules(): array
    {
        return [
            [['academicYear', 'degreeCode', 'group', 'levelOfStudy', 'semester', 'courseCode', 'courseName',
                'approvalLevel', 'restrictedTo'], 'string'],
            [['academicYear', 'degreeCode', 'group', 'levelOfStudy', 'semester', 'courseCode', 'courseName',
                'approvalLevel', 'restrictedTo'], 'trim'],
            [['academicYear', 'degreeCode', 'group', 'levelOfStudy', 'semester', 'courseCode', 'courseName',
                'approvalLevel', 'restrictedTo'], 'default'],
            [['academicYear', 'degreeCode', 'approvalLevel'], 'required'],
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
            'semester' => 'Semester',
            'courseCode' => 'Course code',
            'courseName' => 'Course name',
            'approvalLevel' => 'Approval level',
            'restrictedTo' => 'Restricted to'
        ];
    }
}