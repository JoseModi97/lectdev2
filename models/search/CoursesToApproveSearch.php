<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc Display courses that have marks to be viewed by the hod/deans for approvals or marking back to the lecturers
 */
namespace app\models\search;

use app\components\SmisHelper;
use app\models\Course;
use app\models\CourseWorkAssessment;
use app\models\Department;
use app\models\MarksApprovalFilter;
use app\models\MarksheetDef;
use app\models\StudentCoursework;
use Exception;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class CoursesToApproveSearch extends MarksheetDef
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
     * @param MarksApprovalFilter $courseFilter
     * @param string $type
     * @param string $filtersInterface
     * @param $deptCode
     * @param $facCode
     * @return ActiveDataProvider
     * @throws Exception
     */
    public function search(MarksApprovalFilter $courseFilter, string $type, string $filtersInterface, $deptCode, $facCode): ActiveDataProvider
    {
        $academicYear = $courseFilter->academicYear;
        $degreeCode = $courseFilter->degreeCode;
        $levelOfStudy = $courseFilter->levelOfStudy;
        $semester = $courseFilter->semester;
        $group = $courseFilter->group;
        $courseCode = $courseFilter->courseCode;
        $approvalLevel = $courseFilter->approvalLevel;

        // get marksheets in the timetables using the set filters
        if ($filtersInterface === '1') {
            $semesterId = $academicYear . '_' . $degreeCode . '_' . $levelOfStudy . '_' . $semester . '_' . $group;
            $marksheets = MarksheetDef::find()->select(['MRKSHEET_ID', 'COURSE_ID'])->where(['SEMESTER_ID' => $semesterId])
                ->asArray()->all();
        } else {
            $marksheets = MarksheetDef::find()->select(['MRKSHEET_ID', 'COURSE_ID'])
                ->where(['like', 'MRKSHEET_ID', $academicYear . '_' . $degreeCode . '_%', false])->asArray()->all();
        }

        $timetableMarksheets = [];
        foreach ($marksheets as $marksheet) {
            $courseId = $marksheet['COURSE_ID'];
            $course = Course::find()->select(['DEPT_CODE', 'IS_COMMON'])->where(['COURSE_ID' => $courseId])->asArray()
                ->one();

            /**
             * Get courses in the department
             * @note While the HOD is required to see only courses that belong to their department, they might also see
             * common courses depending on the timetable selected, even though, these courses might belong to other
             * departments.This is because, programs belong to a faculty. Not to a department.
             */
            if ($approvalLevel === 'hod') {
                if ($course['DEPT_CODE'] === $deptCode) {
                    $timetableMarksheets[] = $marksheet['MRKSHEET_ID'];
                }
            }

            // Get courses in the faculty
            /**
             * The dean will see all courses in the departments for their faculty.
             * The courses returned, depends on the timetable selected.
             */
            if ($approvalLevel === 'dean') {
                $dept = Department::find()->select(['FAC_CODE'])->where(['DEPT_CODE' => $course['DEPT_CODE']])->asArray()->one();

                if ($dept['FAC_CODE'] === $facCode) {
                    $timetableMarksheets[] = $marksheet['MRKSHEET_ID'];
                }
            }

            // Get common courses
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

        $marksheetsWithMarks = [];
        foreach ($studentCourseWork as $sw) {
            $marksheetsWithMarks[] = $sw['assessment']['MARKSHEET_ID'];
        }

        $pendingMarksheets = [];
        $approvedMarksheets = [];

        for ($i = 0; $i < count($marksheetsWithMarks); $i++) {
            $marksheetId = $marksheetsWithMarks[$i];

            /**
             * Marksheets with multiple exam components
             */
            if (SmisHelper::hasMultipleExamComponents($marksheetId)) {
                $examComponents = CourseWorkAssessment::find()->alias('CW')->joinWith(['assessmentType AT'])
                    ->where(['CW.MARKSHEET_ID' => $marksheetId])
                    ->andWhere(['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT'])->asArray()->all();

                $examComponentIds = [];
                foreach ($examComponents as $examComponent) {
                    $examComponentIds[] = $examComponent['ASSESSMENT_ID'];
                }

                /**
                 * For a marksheet to be eligible for HOD/Dean actions, all its exam components must have been submitted
                 * by the lecturer. This is because these actions are performed on the entire marksheet, not just one component.
                 */
                $notSubmittedCount = StudentCoursework::find()
                    ->where(['LECTURER_APPROVAL_STATUS' => 'PENDING'])
                    ->andWhere(['IN', 'ASSESSMENT_ID', $examComponentIds])
                    ->count();

                if ($notSubmittedCount > 0) {
                    continue;
                }

                // We get the approved/pending marksheets only for lecturer submitted marksheets
                if ($approvalLevel === 'hod') {
                    $pendingMarksCount = StudentCoursework::find()
                        ->where([
                            'LECTURER_APPROVAL_STATUS' => 'APPROVED',
                            'HOD_APPROVAL_STATUS' => 'PENDING'
                        ])
                        ->andWhere(['IN', 'ASSESSMENT_ID', $examComponentIds])
                        ->count();

                    if ($pendingMarksCount > 0) {
                        $pendingMarksheets[] = $marksheetId;
                    }

                    $approvedMarksCount = StudentCoursework::find()
                        ->where([
                            'LECTURER_APPROVAL_STATUS' => 'APPROVED',
                            'HOD_APPROVAL_STATUS' => 'APPROVED'
                        ])
                        ->andWhere(['IN', 'ASSESSMENT_ID', $examComponentIds])
                        ->count();

                    if ($approvedMarksCount > 0) {
                        $approvedMarksheets[] = $marksheetId;
                    }

                    // If a marksheet is in both pending and approved states, remove it from the approved.
                    $approvedMarksheets = array_diff($approvedMarksheets, $pendingMarksheets);

                } elseif ($approvalLevel === 'dean') {
                    $pendingMarksCount = StudentCoursework::find()
                        ->where([
                            'LECTURER_APPROVAL_STATUS' => 'APPROVED',
                            'HOD_APPROVAL_STATUS' => 'APPROVED',
                            'DEAN_APPROVAL_STATUS' => 'PENDING'
                        ])
                        ->andWhere(['IN', 'ASSESSMENT_ID', $examComponentIds])
                        ->count();

                    if ($pendingMarksCount > 0) {
                        $pendingMarksheets[] = $marksheetId;
                    }

                    $approvedMarksCount = StudentCoursework::find()
                        ->where([
                            'LECTURER_APPROVAL_STATUS' => 'APPROVED',
                            'HOD_APPROVAL_STATUS' => 'APPROVED',
                            'DEAN_APPROVAL_STATUS' => 'APPROVED'
                        ])
                        ->andWhere(['IN', 'ASSESSMENT_ID', $examComponentIds])
                        ->count();

                    if ($approvedMarksCount > 0) {
                        $approvedMarksheets[] = $marksheetId;
                    }

                    // If a marksheet is in both pending and approved states, remove it from the approved.
                    $approvedMarksheets = array_diff($approvedMarksheets, $pendingMarksheets);
                }
            } /**
             * Marksheets with just single exam component
             */
            else {
                // Find assessment of type EXAM
                $examAssessment = CourseWorkAssessment::find()->alias('CW')->joinWith(['assessmentType AT'])
                    ->where(['CW.MARKSHEET_ID' => $marksheetId, 'AT.ASSESSMENT_NAME' => 'EXAM'])->asArray()->one();

                // Check if the marksheet has all marks approved
                if ($approvalLevel === 'hod') {
                    $pendingMarksCount = StudentCoursework::find()
                        ->where([
                            'ASSESSMENT_ID' => $examAssessment['ASSESSMENT_ID'],
                            'LECTURER_APPROVAL_STATUS' => 'APPROVED',
                            'HOD_APPROVAL_STATUS' => 'PENDING'
                        ])->count();

                    if ($pendingMarksCount > 0) {
                        $pendingMarksheets[] = $marksheetId;
                    }

                    $approvedMarksCount = StudentCoursework::find()
                        ->where([
                            'ASSESSMENT_ID' => $examAssessment['ASSESSMENT_ID'],
                            'LECTURER_APPROVAL_STATUS' => 'APPROVED',
                            'HOD_APPROVAL_STATUS' => 'APPROVED'
                        ])->count();

                    if ($approvedMarksCount > 0) {
                        $approvedMarksheets[] = $marksheetId;
                    }

                    // If a marksheet is in both pending and approved states, remove it from the approved.
                    $approvedMarksheets = array_diff($approvedMarksheets, $pendingMarksheets);

                } elseif ($approvalLevel === 'dean') {
                    $pendingMarksCount = StudentCoursework::find()
                        ->where([
                            'ASSESSMENT_ID' => $examAssessment['ASSESSMENT_ID'],
                            'LECTURER_APPROVAL_STATUS' => 'APPROVED',
                            'HOD_APPROVAL_STATUS' => 'APPROVED',
                            'DEAN_APPROVAL_STATUS' => 'PENDING'
                        ])->count();

                    if ($pendingMarksCount > 0) {
                        $pendingMarksheets[] = $marksheetId;
                    }

                    $approvedMarksCount = StudentCoursework::find()
                        ->where([
                            'ASSESSMENT_ID' => $examAssessment['ASSESSMENT_ID'],
                            'LECTURER_APPROVAL_STATUS' => 'APPROVED',
                            'HOD_APPROVAL_STATUS' => 'APPROVED',
                            'DEAN_APPROVAL_STATUS' => 'APPROVED'
                        ])->count();

                    if ($approvedMarksCount > 0) {
                        $approvedMarksheets[] = $marksheetId;
                    }

                    // If a marksheet is in both pending and approved states, remove it from the approved.
                    $approvedMarksheets = array_diff($approvedMarksheets, $pendingMarksheets);
                }
            }
        }

        $query = MarksheetDef::find()->alias('MD')
            ->select([
                'MD.MRKSHEET_ID',
                'MD.SEMESTER_ID',
                'MD.GROUP_CODE',
                'MD.COURSE_ID',
            ]);

        if ($type === 'PENDING') {
            $query->where(['IN', 'MD.MRKSHEET_ID', array_unique($pendingMarksheets, SORT_REGULAR)]);
        } elseif ($type === 'APPROVED') {
            $query->where(['IN', 'MD.MRKSHEET_ID', array_unique($approvedMarksheets, SORT_REGULAR)]);
        }

        if ($filtersInterface === '2') {
            $query->joinWith(['semester SM' => function (ActiveQuery $q) {
                $q->select([
                    'SM.SEMESTER_ID',
                    'SM.SEMESTER_CODE',
                    'SM.LEVEL_OF_STUDY',
                    'SM.DESCRIPTION_CODE'
                ]);
            }], true, 'INNER JOIN')
                ->joinWith(['semester.semesterDescription DESC' => function (ActiveQuery $q) {
                    $q->select([
                        'DESC.DESCRIPTION_CODE',
                        'DESC.SEMESTER_DESC'
                    ]);
                }], true, 'INNER JOIN')
                ->joinWith(['semester.levelOfStudy LVL' => function (ActiveQuery $q) {
                    $q->select([
                        'LVL.LEVEL_OF_STUDY',
                        'LVL.NAME'
                    ]);
                }], true, 'INNER JOIN')
                ->joinWith(['group GR' => function (ActiveQuery $q) {
                    $q->select([
                        'GR.GROUP_CODE',
                        'GR.GROUP_NAME'
                    ]);
                }], true, 'INNER JOIN');
        }

        $query->joinWith(['course CS' => function (ActiveQuery $q) {
            $q->select([
                'CS.COURSE_ID',
                'CS.COURSE_CODE',
                'CS.COURSE_NAME'
            ]);
        }], true, 'INNER JOIN');

        if($filtersInterface === '2') {
            if (!empty($levelOfStudy)) {
                $query->andWhere(['SM.LEVEL_OF_STUDY' => $levelOfStudy]);
            }

            if (!empty($group)) {
                $query->andWhere(['MD.GROUP_CODE' => $group]);
            }

            if (!empty($semester)) {
                $query->andWhere(['SM.SEMESTER_CODE' => $semester]);
            }
        }

        if(!empty($courseCode)){
            $query->andWhere(['LIKE', 'CS.COURSE_CODE', $courseCode]);
        }

        if($filtersInterface === '2'){
            $query->orderBy([
                'SM.LEVEL_OF_STUDY' => SORT_DESC,
                'MD.GROUP_CODE' => SORT_ASC,
                'SM.SEMESTER_CODE' => SORT_ASC,
                'CS.COURSE_CODE' => SORT_ASC
            ])->asArray();
        }else{
            $query->orderBy(['COURSE_CODE' => SORT_ASC])->asArray();
        }

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 20,
            ]
        ]);
    }
}