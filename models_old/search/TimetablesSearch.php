<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

namespace app\models\search;

use app\models\CourseAllocationFilter;
use app\models\Timetable;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class TimetablesSearch extends Timetable
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param string $deptCode
     * @param CourseAllocationFilter $courseFilter
     * @return ActiveDataProvider
     */
    public function search(string $deptCode, CourseAllocationFilter $courseFilter): ActiveDataProvider
    {
        $query = Timetable::find()->alias('TT')->select(['TT.MRKSHEET_ID'])
            ->joinWith(['marksheetDef MD' => function(ActiveQuery $q){
                $q->select([
                    'MD.MRKSHEET_ID',
                    'MD.SEMESTER_ID',
                    'MD.COURSE_ID',
                    'MD.GROUP_CODE',
                    'MD.PAYROLL_NO'
                ]);
            }], true, 'INNER JOIN')
            ->where(['MD.GROUP_CODE' => $courseFilter->group])
            ->joinWith(['marksheetDef.semester SM' => function(ActiveQuery $q){
                $q->select([
                    'SM.SEMESTER_ID',
                    'SM.SEMESTER_CODE',
                    'SM.DEGREE_CODE',
                    'SM.ACADEMIC_YEAR',
                    'SM.LEVEL_OF_STUDY',
                    'SM.DESCRIPTION_CODE',
                    'SM.SEMESTER_TYPE'
                ]);
            }], true, 'INNER JOIN')
            ->andWhere([
                'SM.SEMESTER_CODE' => $courseFilter->semester,
                'SM.ACADEMIC_YEAR' => $courseFilter->academicYear,
                'SM.LEVEL_OF_STUDY' => $courseFilter->levelOfStudy,
                'DEG.DEGREE_CODE' => $courseFilter->degreeCode
            ]);

        if($courseFilter->purpose === 'nonSuppCourses'){
            $query->andWhere(['NOT', ['SM.SEMESTER_TYPE' => 'SUPPLEMENTARY']]);
        }elseif($courseFilter->purpose === 'suppCourses'){
            $query->andWhere(['SM.SEMESTER_TYPE' => 'SUPPLEMENTARY']);
        }

        $query->joinWith(['marksheetDef.semester.degreeProgramme DEG' => function(ActiveQuery $q){
            $q->select([
                'DEG.DEGREE_CODE',
                'DEG.DEGREE_NAME'
            ]);
        }], true, 'INNER JOIN')
        ->joinWith(['marksheetDef.semester.semesterDescription DESC' => function(ActiveQuery $q){
            $q->select([
                'DESC.DESCRIPTION_CODE',
                'DESC.SEMESTER_DESC'
            ]);
        }], true, 'INNER JOIN')
        ->joinWith(['marksheetDef.group GR' => function(ActiveQuery $q){
            $q->select([
                'GR.GROUP_CODE',
                'GR.GROUP_NAME'
            ]);
        }], true, 'INNER JOIN')
        ->joinWith(['marksheetDef.course CS' => function(ActiveQuery $q){
            $q->select([
                'CS.COURSE_ID',
                'CS.COURSE_CODE',
                'CS.COURSE_NAME',
                'CS.DEPT_CODE'
            ]);
        }], true, 'INNER JOIN');

        if(!empty($courseFilter->courseCode)){
            $query->andWhere(['like', 'CS.COURSE_CODE', $courseFilter->courseCode]);
        }

        if(!empty($courseFilter->courseName)){
            $query->andWhere(['like', 'CS.COURSE_NAME', $courseFilter->courseName]);
        }

        $query->joinWith(['marksheetDef.course.dept DEPT' => function(ActiveQuery $q){
            $q->select([
                'DEPT.DEPT_CODE',
                'DEPT.DEPT_NAME',
                'DEPT.FAC_CODE'
            ]);
        }], true, 'INNER JOIN')
        ->andWhere(['LIKE', 'DEPT.DEPT_CODE', $deptCode . '%', false])
        ->orderBy(['DEG.DEGREE_NAME' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 50,
            ]
        ]);
    }
}