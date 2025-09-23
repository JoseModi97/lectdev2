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

        $marksheets = MarksheetDef::find()->select(['MRKSHEET_ID', 'COURSE_ID'])
            ->where(['SEMESTER_ID' => $semesterId])->asArray()->all();

        $timetableCourses = [];
        foreach ($marksheets as $marksheet) {
            $courseId = $marksheet['COURSE_ID'];
            $course = Course::find()->select(['DEPT_CODE', 'IS_COMMON'])->where(['COURSE_ID' => $courseId])->asArray()->one();

            if ($course['DEPT_CODE'] === $deptCode) {
                $timetableCourses[] = $courseId;
            }

            if (intval($course['IS_COMMON']) === 1) {
                $timetableCourses[] = $courseId;
            }
        }

        $timetableCourses = array_unique($timetableCourses);
        // dd('Hello');
        $query = MarksheetDef::find()
            ->select([
                'MUTHONI.MARKSHEET_DEF.MRKSHEET_ID',
                'MUTHONI.MARKSHEET_DEF.SEMESTER_ID',
                'MUTHONI.MARKSHEET_DEF.COURSE_ID',
                'MUTHONI.MARKSHEET_DEF.GROUP_CODE',
                'MUTHONI.MARKSHEET_DEF.PAYROLL_NO'
            ])
            //            ->where(['MD.GROUP_CODE' => $courseFilter->group])
            ->where(['MUTHONI.MARKSHEET_DEF.SEMESTER_ID' => $semesterId])
            ->andWhere(['IN', 'MUTHONI.MARKSHEET_DEF.COURSE_ID', $timetableCourses])
            ->joinWith(['semester' => function (ActiveQuery $q) {
                $q->select([
                    'MUTHONI.SEMESTERS.SEMESTER_ID',
                    'MUTHONI.SEMESTERS.SEMESTER_CODE',
                    'MUTHONI.SEMESTERS.DEGREE_CODE',
                    'MUTHONI.SEMESTERS.ACADEMIC_YEAR',
                    'MUTHONI.SEMESTERS.LEVEL_OF_STUDY',
                    'MUTHONI.SEMESTERS.DESCRIPTION_CODE',
                    'MUTHONI.SEMESTERS.SEMESTER_TYPE'
                ]);
            }], true, 'INNER JOIN');
        //            ->andWhere([
        //                'SM.SEMESTER_CODE' => $courseFilter->semester,
        //                'SM.ACADEMIC_YEAR' => $courseFilter->academicYear,
        //                'SM.LEVEL_OF_STUDY' => $courseFilter->levelOfStudy,
        //                'DEG.DEGREE_CODE' => $courseFilter->degreeCode
        //            ]);

        if ($courseFilter->purpose === 'nonSuppCourses') {
            $query->andWhere(['NOT', ['MUTHONI.SEMESTERS.SEMESTER_TYPE' => 'SUPPLEMENTARY']]);
        } elseif ($courseFilter->purpose === 'suppCourses') {
            $query->andWhere(['MUTHONI.SEMESTERS.SEMESTER_TYPE' => 'SUPPLEMENTARY']);
        }

        $query->joinWith(['semester.degreeProgramme' => function (ActiveQuery $q) {
            $q->select([
                'MUTHONI.DEGREE_PROGRAMMES.DEGREE_CODE',
                'MUTHONI.DEGREE_PROGRAMMES.DEGREE_NAME'
            ]);
        }], true, 'INNER JOIN')
            ->joinWith(['semester.semesterDescription DESC' => function (ActiveQuery $q) {
                $q->select([
                    'DESC.DESCRIPTION_CODE',
                    'DESC.SEMESTER_DESC'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['group GR' => function (ActiveQuery $q) {
                $q->select([
                    'GR.GROUP_CODE',
                    'GR.GROUP_NAME'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['course' => function (ActiveQuery $q) {
                $q->select([
                    'MUTHONI.COURSES.COURSE_ID',
                    'MUTHONI.COURSES.COURSE_CODE',
                    'MUTHONI.COURSES.COURSE_NAME',
                    'MUTHONI.COURSES.DEPT_CODE'
                ]);
            }], true, 'INNER JOIN');

        if (!empty($courseFilter->courseCode)) {
            $query->andWhere(['like', 'MUTHONI.COURSES.COURSE_CODE', $courseFilter->courseCode]);
        }

        if (!empty($courseFilter->courseName)) {
            $query->andWhere(['like', 'MUTHONI.COURSES.COURSE_NAME', $courseFilter->courseName]);
        }

        $query->joinWith(['course.dept' => function (ActiveQuery $q) {
            $q->select([
                'MUTHONI.DEPARTMENTS.DEPT_CODE',
                'MUTHONI.DEPARTMENTS.DEPT_NAME',
                'MUTHONI.DEPARTMENTS.FAC_CODE'
            ]);
        }], true, 'INNER JOIN')
            ->orderBy(['MUTHONI.DEGREE_PROGRAMMES.DEGREE_NAME' => SORT_DESC]);


        // dd($query->createCommand()->getRawSql());

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 50,
            ],
        ]);
    }
}
