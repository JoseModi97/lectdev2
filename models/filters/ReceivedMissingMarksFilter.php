<?php

/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @date: 7/30/2024
 * @time: 2:53 PM
 */
namespace app\models\filters;

/**
 * This is the model behind the filters for course analysis
 * @property string $academicYear
 * @property string $degreeCode
 * @property string $group
 * @property string $levelOfStudy
 * @property string $semester
 */
class ReceivedMissingMarksFilter extends \yii\base\Model
{
    public $academicYear;
    public $degreeCode;
    public $group;
    public $levelOfStudy;
    public $semester;

    /**
     * @return array the validation rules.
     */
    public function rules(): array
    {
        return [
            [['academicYear', 'degreeCode', 'group', 'levelOfStudy', 'semester'], 'string'],
            [['academicYear', 'degreeCode', 'group', 'levelOfStudy', 'semester'], 'trim'],
            [['academicYear', 'degreeCode', 'group', 'levelOfStudy', 'semester'], 'default'],
            [['academicYear', 'degreeCode'], 'required'],
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
            'semester' => 'Semester'
        ];
    }
}