<?php

/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

namespace app\models\search;

use app\models\AllocationRequest;
use app\models\CourseAllocationFilter;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class AllocationRequestsSearchNew extends AllocationRequest
{
    public $courseCode;
    public $statusName;
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['courseCode', 'statusName'], 'safe'],
        ];
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
        $this->load(\Yii::$app->request->queryParams);

        $query = AllocationRequest::find()
            ->select([
                'MUTHONI.LEC_ALLOCATION_REQUESTS.REQUEST_ID',
                'MUTHONI.LEC_ALLOCATION_REQUESTS.STATUS_ID',
                'MUTHONI.LEC_ALLOCATION_REQUESTS.MARKSHEET_ID',
                'MUTHONI.LEC_ALLOCATION_REQUESTS.REMARKS',
                'MUTHONI.LEC_ALLOCATION_REQUESTS.REQUESTING_DEPT',
                'MUTHONI.LEC_ALLOCATION_REQUESTS.SERVICING_DEPT',
                new \yii\db\Expression("TO_CHAR(LEC_ALLOCATION_REQUESTS.REQUEST_DATE, 'YYYY-MM-DD HH24:MI:SS') AS REQUEST_DATE"),
                'MUTHONI.LEC_ALLOCATION_REQUESTS.REQUEST_BY',
                'MUTHONI.LEC_ALLOCATION_REQUESTS.ATTENDED_BY',
                'MUTHONI.LEC_ALLOCATION_REQUESTS.ATTENDED_DATE'
            ]);

        if ($courseFilter->purpose === 'requestedCourses') {
            $query->where(['MUTHONI.LEC_ALLOCATION_REQUESTS.REQUESTING_DEPT' => $deptCode]);
        }

        if ($courseFilter->purpose === 'serviceCourses') {
            $query->where(['MUTHONI.LEC_ALLOCATION_REQUESTS.SERVICING_DEPT' => $deptCode]);
        }

        if (!empty($courseFilter->status)) {
            $query->andWhere(['MUTHONI.LEC_ALLOCATION_REQUESTS.STATUS_ID' => $courseFilter->status]);
        }

        $query->joinWith(['status' => function (ActiveQuery $q) {
            $q->select([
                'MUTHONI.LEC_ALLOCATION_STATUSES.STATUS_ID',
                'MUTHONI.LEC_ALLOCATION_STATUSES.STATUS_NAME'
            ]);
        }], true, 'INNER JOIN')
            ->joinWith(['requestingDept RDEPT' => function (ActiveQuery $q) {
                $q->select([
                    'RDEPT.DEPT_CODE',
                    'RDEPT.DEPT_NAME'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['servicingDept SDEPT' => function (ActiveQuery $q) {
                $q->select([
                    'SDEPT.DEPT_CODE',
                    'SDEPT.DEPT_NAME'
                ]);
            }], true, 'INNER JOIN');

        if ($courseFilter->purpose === 'requestedCourses' && !empty($courseFilter->servicingDepartment)) {
            $query->andWhere(['SDEPT.DEPT_CODE' => $courseFilter->servicingDepartment]);
        }

        if ($courseFilter->purpose === 'serviceCourses' && !empty($courseFilter->requestingDepartment)) {
            $query->andWhere(['RDEPT.DEPT_CODE' => $courseFilter->requestingDepartment]);
        }

        $query->joinWith(['marksheet MD' => function (ActiveQuery $q) {
            $q->select([
                'MD.MRKSHEET_ID',
                'MD.SEMESTER_ID',
                'MD.COURSE_ID',
                'MD.GROUP_CODE',
            ]);
        }], true, 'INNER JOIN')
            ->joinWith(['marksheet.semester SM' => function (ActiveQuery $q) {
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
                'SM.ACADEMIC_YEAR' => $courseFilter->academicYear
            ])
            ->joinWith(['marksheet.course CS' => function (ActiveQuery $q) {
                $q->select([
                    'CS.COURSE_ID',
                    'CS.COURSE_CODE',
                    'CS.COURSE_NAME',
                ]);
            }], true, 'INNER JOIN');

        if (!empty($courseFilter->courseCode)) {
            $query->andWhere(['like', 'CS.COURSE_CODE', $courseFilter->courseCode]);
        }

        if (!empty($courseFilter->courseName)) {
            $query->andWhere(['like', 'CS.COURSE_NAME', $courseFilter->courseName]);
        }

        // Filter by semester code if provided
        if (!empty($courseFilter->semester)) {
            $query->andWhere(['SM.SEMESTER_CODE' => $courseFilter->semester]);
            // If semester description code is provided (from top filter), add it as well
            if (!empty($courseFilter->semesterDesc)) {
                $query->andWhere(['SM.DESCRIPTION_CODE' => $courseFilter->semesterDesc]);
            }
        }

        // Filter by academic level (level of study) if provided
        if (!empty($courseFilter->levelOfStudy)) {
            $query->andWhere(['SM.LEVEL_OF_STUDY' => $courseFilter->levelOfStudy]);
        }

        // Apply in-grid filters (optional) if provided
        if (!empty($this->courseCode)) {
            $query->andWhere(['like', 'CS.COURSE_CODE', $this->courseCode]);
        }
        if (!empty($this->statusName)) {
            $query->andWhere(['MUTHONI.LEC_ALLOCATION_STATUSES.STATUS_NAME' => $this->statusName]);
        }

        $query->joinWith(['requestedBy REQ' => function (ActiveQuery $q) {
            $q->select([
                'REQ.PAYROLL_NO',
                'REQ.EMP_ID',
                'REQ.SURNAME',
                'REQ.OTHER_NAMES',
                'REQ.EMP_TITLE'
            ]);
        }], true, 'INNER JOIN')
            ->joinWith(['attendedBy ATT' => function (ActiveQuery $q) {
                $q->select([
                    'ATT.PAYROLL_NO',
                    'ATT.EMP_ID',
                    'ATT.SURNAME',
                    'ATT.OTHER_NAMES',
                    'ATT.EMP_TITLE'
                ]);
            }]);

        // Order by grouped department then degree code to match SERVICING/REQUESTING column with degree suffix
        if ($courseFilter->purpose === 'serviceCourses') {
            $query->orderBy([
                'RDEPT.DEPT_NAME' => SORT_ASC,
                'SM.DEGREE_CODE' => SORT_ASC,
                'CS.COURSE_CODE' => SORT_ASC,
                'MUTHONI.LEC_ALLOCATION_REQUESTS.REQUEST_ID' => SORT_ASC,
            ]);
        } else {
            $query->orderBy([
                'SDEPT.DEPT_NAME' => SORT_ASC,
                'SM.DEGREE_CODE' => SORT_ASC,
                'CS.COURSE_CODE' => SORT_ASC,
                'MUTHONI.LEC_ALLOCATION_REQUESTS.REQUEST_ID' => SORT_ASC,
            ]);
        }





        return new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 50,
            ],
        ]);
    }
}
