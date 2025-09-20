<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

namespace app\models\search;

use app\models\Course;
use app\models\CourseAllocationFilter;
use app\models\MarksheetDef;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class MarksheetDefAllocationSearchNew extends MarksheetDef
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
        $semesterId = $courseFilter->academicYear . '_' . $courseFilter->degreeCode . '_' . $courseFilter->levelOfStudy
            . '_' . $courseFilter->semester . '_' . $courseFilter->group;

        $marksheets = MarksheetDef::find()->select(['MRKSHEET_ID','COURSE_ID'])
            ->where(['SEMESTER_ID' => $semesterId])->asArray()->all();

        $timetableCourses = [];
        foreach ($marksheets as $marksheet){
            $courseId = $marksheet['COURSE_ID'];
            $course = Course::find()->select(['DEPT_CODE', 'IS_COMMON'])->where(['COURSE_ID' => $courseId])->asArray()->one();

            if($course['DEPT_CODE'] === $deptCode){
                $timetableCourses[] = $courseId;
            }

            if(intval($course['IS_COMMON']) === 1){
                $timetableCourses[] = $courseId;
            }
        }

        $timetableCourses = array_unique($timetableCourses);

        $query = MarksheetDef::find()->alias('MD')
            ->select([
                'MD.MRKSHEET_ID',
                'MD.SEMESTER_ID',
                'MD.COURSE_ID',
                'MD.GROUP_CODE',
                'MD.PAYROLL_NO'
            ])
//            ->where(['MD.GROUP_CODE' => $courseFilter->group])
            ->where(['MD.SEMESTER_ID' => $semesterId])
            ->andWhere(['IN', 'MD.COURSE_ID', $timetableCourses])
            ->joinWith(['semester SM' => function(ActiveQuery $q){
                $q->select([
                    'SM.SEMESTER_ID',
                    'SM.SEMESTER_CODE',
                    'SM.DEGREE_CODE',
                    'SM.ACADEMIC_YEAR',
                    'SM.LEVEL_OF_STUDY',
                    'SM.DESCRIPTION_CODE',
                    'SM.SEMESTER_TYPE'
                ]);
            }], true, 'INNER JOIN');
//            ->andWhere([
//                'SM.SEMESTER_CODE' => $courseFilter->semester,
//                'SM.ACADEMIC_YEAR' => $courseFilter->academicYear,
//                'SM.LEVEL_OF_STUDY' => $courseFilter->levelOfStudy,
//                'DEG.DEGREE_CODE' => $courseFilter->degreeCode
//            ]);

        if($courseFilter->purpose === 'nonSuppCourses'){
            $query->andWhere(['NOT', ['SM.SEMESTER_TYPE' => 'SUPPLEMENTARY']]);
        }elseif($courseFilter->purpose === 'suppCourses'){
            $query->andWhere(['SM.SEMESTER_TYPE' => 'SUPPLEMENTARY']);
        }

        $query->joinWith(['semester.degreeProgramme DEG' => function(ActiveQuery $q){
                $q->select([
                    'DEG.DEGREE_CODE',
                    'DEG.DEGREE_NAME'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['semester.semesterDescription DESC' => function(ActiveQuery $q){
                $q->select([
                    'DESC.DESCRIPTION_CODE',
                    'DESC.SEMESTER_DESC'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['group GR' => function(ActiveQuery $q){
                $q->select([
                    'GR.GROUP_CODE',
                    'GR.GROUP_NAME'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['course CS' => function(ActiveQuery $q){
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

        $query->joinWith(['course.dept DEPT' => function(ActiveQuery $q){
            $q->select([
                'DEPT.DEPT_CODE',
                'DEPT.DEPT_NAME',
                'DEPT.FAC_CODE'
            ]);
        }], true, 'INNER JOIN')
        ->orderBy(['DEG.DEGREE_NAME' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 50,
            ],
        ]);
    }
}

