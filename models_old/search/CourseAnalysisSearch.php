<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc Display courses with marks for the lecturer/hod/dean analysis report
 */

namespace app\models\search;

use app\models\Course;
use app\models\CourseAnalysisFilter;
use app\models\CourseAssignment;
use app\models\CourseWorkAssessment;
use app\models\Department;
use app\models\MarksheetDef;
use app\models\StudentCoursework;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class CourseAnalysisSearch extends MarksheetDef
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
     * @param CourseAnalysisFilter $courseFilter
     * @param string $deptCode
     * @param string $facCode
     * @return ActiveDataProvider
     */
    public function search(CourseAnalysisFilter $courseFilter, string $deptCode, string $facCode): ActiveDataProvider
    {
        $semesterId = $courseFilter->academicYear . '_' . $courseFilter->degreeCode . '_' . $courseFilter->levelOfStudy
            . '_' . $courseFilter->semester . '_' . $courseFilter->group;

        // get marksheets in the timetables
        $marksheets = MarksheetDef::find()->select(['MRKSHEET_ID', 'COURSE_ID'])->where(['SEMESTER_ID' => $semesterId])
            ->asArray()->all();

        $studentCourseWork = [];
        if ($courseFilter->approvalLevel === 'lecturer') {
            // Get lecturer allocated courses in the marksheets
            $lecturerMarksheets = CourseAssignment::find()
                ->select(['MRKSHEET_ID'])
                ->where(['PAYROLL_NO' => Yii::$app->user->identity->PAYROLL_NO])
                ->andWhere(['IN', 'MRKSHEET_ID', $marksheets]);

            // Of all the allocated marksheets, get those with marks
            $studentCourseWork = StudentCoursework::find()->alias('SW')->select(['SW.ASSESSMENT_ID'])
                ->joinWith(['assessment ASS' => function (ActiveQuery $q) {
                    $q->select(['ASS.ASSESSMENT_ID', 'ASS.MARKSHEET_ID']);
                }], true, 'INNER JOIN')
                ->andWhere(['IN', 'ASS.MARKSHEET_ID', $lecturerMarksheets])
                ->distinct()->asArray()->all();
        }

        $approvalLevel = $courseFilter->approvalLevel;
        if ($approvalLevel === 'hod' || $approvalLevel === 'dean') {
            // get department/faculty marksheets
            $timetableMarksheets = [];
            foreach ($marksheets as $marksheet) {
                $courseId = $marksheet['COURSE_ID'];
                $course = Course::find()->select(['DEPT_CODE', 'IS_COMMON'])->where(['COURSE_ID' => $courseId])->asArray()->one();

                if ($approvalLevel === 'hod') {
                    if ($course['DEPT_CODE'] === $deptCode) {
                        $timetableMarksheets[] = $marksheet['MRKSHEET_ID'];
                    }
                }

                if ($approvalLevel === 'dean') {
                    $dept = Department::find()->select(['FAC_CODE'])->where(['DEPT_CODE' => $course['DEPT_CODE']])->asArray()->one();

                    if ($dept['FAC_CODE'] === $facCode) {
                        $timetableMarksheets[] = $marksheet['MRKSHEET_ID'];
                    }
                }

                if (intval($course['IS_COMMON']) === 1) {
                    $timetableMarksheets[] = $marksheet['MRKSHEET_ID'];
                }
            }

            // Of all the marksheets, get those with marks
            $studentCourseWork = StudentCoursework::find()->alias('SW')->select(['SW.ASSESSMENT_ID'])
                ->joinWith(['assessment ASS' => function (ActiveQuery $q) {
                    $q->select(['ASS.ASSESSMENT_ID', 'ASS.MARKSHEET_ID']);
                }], true, 'INNER JOIN')
                ->andWhere(['IN', 'ASS.MARKSHEET_ID', $timetableMarksheets])
                ->distinct()->asArray()->all();
        }

        $marksheetsWithMarks = [];
        foreach ($studentCourseWork as $sw) {
            $marksheetsWithMarks[] = $sw['assessment']['MARKSHEET_ID'];
        }

        $marksheetsWithSubmittedMarks = [];
        if ($courseFilter->restrictedTo === 'submitted') {
            foreach (array_unique($marksheetsWithMarks, SORT_REGULAR) as $key => $marksheetId) {
                // Get exam assessments
                $examAssessments = CourseWorkAssessment::find()->alias('CW')
                    ->joinWith(['assessmentType AT'])
                    ->where(['CW.MARKSHEET_ID' => $marksheetId])
                    ->andWhere(['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM%', false])
                    ->all();

                // Get all marks in these exam assessments
                foreach ($examAssessments as $examAssessment) {
                    $assessmentId = $examAssessment['ASSESSMENT_ID'];

                    $approvedCount = 0;

                    if ($approvalLevel === 'hod') {
                        $approvedCount = StudentCoursework::find()
                            ->where([
                                'ASSESSMENT_ID' => $assessmentId,
                                'LECTURER_APPROVAL_STATUS' => 'APPROVED'
                            ])
                            ->count();
                    } elseif ($approvalLevel === 'dean') {
                        $approvedCount = StudentCoursework::find()
                            ->where([
                                'ASSESSMENT_ID' => $assessmentId,
                                'LECTURER_APPROVAL_STATUS' => 'APPROVED',
                                'HOD_APPROVAL_STATUS' => 'APPROVED'
                            ])
                            ->count();
                    }

                    if ((int)$approvedCount > 0) {
                        $marksheetsWithSubmittedMarks[] = $marksheetId;
                    }
                }
            }
        }

        $query = MarksheetDef::find()->alias('MD')
            ->select([
                'MD.MRKSHEET_ID',
                'MD.COURSE_ID'
            ]);

        if ($courseFilter->restrictedTo === 'submitted') {
            $query->where(['IN', 'MD.MRKSHEET_ID', array_unique($marksheetsWithSubmittedMarks, SORT_REGULAR)]);
        } else {
            $query->where(['IN', 'MD.MRKSHEET_ID', array_unique($marksheetsWithMarks, SORT_REGULAR)]);
        }

        $query->joinWith(['course CS' => function (ActiveQuery $q) {
            $q->select([
                'CS.COURSE_ID',
                'CS.COURSE_CODE',
                'CS.COURSE_NAME'
            ]);
        }], true, 'INNER JOIN');

        if (!empty($courseFilter->courseCode)) {
            $query->andWhere(['LIKE', 'CS.COURSE_CODE', $courseFilter->courseCode]);
        }

        $query->orderBy(['COURSE_CODE' => SORT_ASC])->asArray();

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 50,
            ],
        ]);
    }
}