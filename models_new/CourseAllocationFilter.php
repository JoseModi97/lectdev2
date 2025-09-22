<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

namespace app\models;

use yii\base\Model;

/**
 * This is the model behind the filters for course allocations
 * @property string $academicYear
 * @property string $degreeCode
 * @property string $group
 * @property string $levelOfStudy
 * @property string $semester
 * @property string $courseCode
 * @property string $courseName
 * @property string $purpose
 * @property string $status
 * @property string $requestingDepartment
 * @property string $servicingDepartment
 */
class CourseAllocationFilter extends Model
{
    public $academicYear;
    public $degreeCode;
    public $group;
    public $levelOfStudy;
    public $semester;
    public $courseCode;
    public $courseName;
    public $purpose;
    public $status;
    public $requestingDepartment;
    public $servicingDepartment;

    /**
     * @return array the validation rules.
     */
    public function rules(): array
    {
        return [
            [['academicYear', 'degreeCode', 'group', 'levelOfStudy', 'semester', 'courseCode', 'courseName', 'purpose',
                'status', 'requestingDepartment', 'servicingDepartment'], 'string'],
            [['academicYear'], 'required'],
            [['academicYear', 'degreeCode', 'group', 'levelOfStudy', 'semester', 'courseCode', 'courseName', 'purpose',
                'status', 'requestingDepartment', 'servicingDepartment'], 'trim'],
            [['academicYear', 'degreeCode', 'group', 'levelOfStudy', 'semester', 'courseCode', 'courseName', 'purpose',
                'status', 'requestingDepartment', 'servicingDepartment'], 'default']
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
            'purpose' => 'Filters for',
            'status' => 'Status',
            'requestingDepartment' => 'Requesting department',
            'servicingDepartment' => 'Servicing department'
        ];
    }
}