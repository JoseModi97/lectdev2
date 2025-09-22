<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc Display courses that have marks awaiting HOD/Dean approvals
 */

namespace app\models\search;

use app\models\Course;
use app\models\Department;
use app\models\MarksheetDef;
use app\models\Semester;
use app\models\StudentCoursework;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class MarksApprovalSearch extends MarksheetDef
{
    /**
     * add related fields to searchable attributes
     * @return array
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'course.COURSE_CODE',
            'course.COURSE_NAME',
            'group.GROUP_CODE',
            'semester.levelOfStudy.LEVEL_OF_STUDY'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [
                [
                    'course.COURSE_CODE',
                    'course.COURSE_NAME',
                    'group.GROUP_CODE',
                    'semester.levelOfStudy.LEVEL_OF_STUDY'
                ],
                'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     * Bypass scenarios() implementation in the parent class
     */
    public function scenarios(): array
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     * @param array $params
     * @param array $additionalParams
     * @return ActiveDataProvider
     */
    public function search(array $params, array $additionalParams = []): ActiveDataProvider
    {
        $filter = $additionalParams['filter'];
        $deptCode = $additionalParams['deptCode'];
        $facCode = $additionalParams['facCode'];

        // get timetables with provided filters
        $semesters = Semester::find()->select(['SEMESTER_ID'])->where([
            'ACADEMIC_YEAR' => $filter->academicYear,
            'DEGREE_CODE' => $filter->degreeCode,
            'SEMESTER_CODE' => $filter->semester
        ])->asArray()->all();

        $semesterIds = [];

        foreach ($semesters as $semester){
            $semesterIds[] = $semester['SEMESTER_ID'];
        }

        // get marksheets in the timetables
        $marksheets = MarksheetDef::find()->select(['MRKSHEET_ID','COURSE_ID'])->where(['IN', 'SEMESTER_ID', $semesterIds])
            ->asArray()->all();

        // get department/faculty marksheets
        $timetableMarksheets = [];
        foreach ($marksheets as $marksheet){
            $courseId = $marksheet['COURSE_ID'];
            $course = Course::find()->select(['DEPT_CODE', 'IS_COMMON'])->where(['COURSE_ID' => $courseId])->asArray()->one();

            if($filter->approvalLevel === 'hod') {
                if ($course['DEPT_CODE'] === $deptCode) {
                    $timetableMarksheets[] = $marksheet['MRKSHEET_ID'];
                }
            }

            if($filter->approvalLevel === 'dean'){
                $dept = Department::find()->select(['FAC_CODE'])->where(['DEPT_CODE' => $course['DEPT_CODE']])->asArray()->one();

                if ($dept['FAC_CODE'] === $facCode) {
                    $timetableMarksheets[] = $marksheet['MRKSHEET_ID'];
                }
            }

            if (intval($course['IS_COMMON']) === 1) {
                $timetableMarksheets[] = $marksheet['MRKSHEET_ID'];
            }
        }

        $studentCourseWork = [];
        // Get only courses that have their marks entered and awaiting HOD approval
        if($filter->approvalLevel === 'hod') {
            $studentCourseWork = StudentCoursework::find()->alias('SW')
                ->select(['SW.ASSESSMENT_ID'])
                ->where(['LECTURER_APPROVAL_STATUS' => 'APPROVED'])
                ->joinWith(['assessment ASS' => function (ActiveQuery $q) {
                    $q->select([
                        'ASS.ASSESSMENT_ID',
                        'ASS.MARKSHEET_ID'
                    ]);
                }], true, 'INNER JOIN')
                ->andWhere(['IN', 'ASS.MARKSHEET_ID', array_unique($timetableMarksheets)])
                ->distinct()->asArray()->all();
        }

        // Get only courses that have their marks entered and awaiting dean approval
        if($filter->approvalLevel === 'dean'){
            $studentCourseWork = StudentCoursework::find()->alias('SW')->select(['SW.ASSESSMENT_ID'])
                ->where(['HOD_APPROVAL_STATUS' => 'APPROVED'])
                ->joinWith(['assessment ASS' => function(ActiveQuery $q){
                    $q->select([
                        'ASS.ASSESSMENT_ID',
                        'ASS.MARKSHEET_ID'
                    ]);
                }], true, 'INNER JOIN')
                ->andWhere(['IN', 'ASS.MARKSHEET_ID', array_unique($timetableMarksheets)])
                ->distinct()->asArray()->all();
        }

        $marksheetsWithMarks = [];
        foreach ($studentCourseWork as $st){
            $marksheetsWithMarks[] = $st['assessment']['MARKSHEET_ID'];
        }

        $query = MarksheetDef::find()->alias('MD')
            ->select([
                'MD.MRKSHEET_ID',
                'MD.SEMESTER_ID',
                'MD.COURSE_ID',
                'MD.GROUP_CODE',
            ])
            ->where(['IN', 'MD.MRKSHEET_ID', array_unique($marksheetsWithMarks)])
            ->joinWith(['group GR' => function(ActiveQuery $q){
                $q->select([
                    'GR.GROUP_CODE',
                    'GR.GROUP_NAME'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['semester SM' => function(ActiveQuery $q){
                $q->select([
                    'SM.SEMESTER_ID',
                    'SM.SEMESTER_CODE',
                    'SM.DEGREE_CODE',
                    'SM.ACADEMIC_YEAR',
                    'SM.LEVEL_OF_STUDY',
                    'SM.DESCRIPTION_CODE'
                ]);
            }], true, 'INNER JOIN')
            ->andWhere([
                'SM.ACADEMIC_YEAR' => $filter->academicYear,
                'SM.SEMESTER_CODE' => $filter->semester
            ])
            ->joinWith(['semester.levelOfStudy LVL' => function(ActiveQuery $q){
                $q->select([
                    'LVL.LEVEL_OF_STUDY',
                    'LVL.NAME'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['course CS' => function(ActiveQuery $q){
                $q->select([
                    'CS.COURSE_ID',
                    'CS.COURSE_CODE',
                    'CS.COURSE_NAME',
                    'CS.DEPT_CODE'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['course.dept DEPT' => function(ActiveQuery $q){
                $q->select([
                    'DEPT.DEPT_CODE',
                    'DEPT.FAC_CODE'
                ]);
            }], true, 'INNER JOIN');

        $query->asArray();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 50,
            ],
        ]);

        $this->load($params);

        if(!$this->validate()){
            return $dataProvider;
        }

        $query->orderBy(['CS.COURSE_CODE' => SORT_ASC]);

        $query->andFilterWhere(['like', 'CS.COURSE_CODE', $this->getAttribute('course.COURSE_CODE')]);
        $query->andFilterWhere(['like', 'CS.COURSE_NAME', $this->getAttribute('course.COURSE_NAME')]);
        $query->andFilterWhere(['GR.GROUP_CODE' => $this->getAttribute('group.GROUP_CODE')]);
        $query->andFilterWhere(['LVL.LEVEL_OF_STUDY' => $this->getAttribute('semester.levelOfStudy.LEVEL_OF_STUDY')]);

        return $dataProvider;
    }
}