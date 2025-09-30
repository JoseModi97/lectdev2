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

        $query = AllocationRequest::find()->alias('AR')
            ->select([
                'AR.REQUEST_ID',
                'AR.STATUS_ID',
                'AR.MARKSHEET_ID',
                'AR.REMARKS',
                'AR.REQUESTING_DEPT',
                'AR.SERVICING_DEPT',
                'AR.REQUEST_DATE',
                'AR.REQUEST_BY',
                'AR.ATTENDED_BY',
                'AR.ATTENDED_DATE'
            ]);

        if($courseFilter->purpose === 'requestedCourses'){
            $query->where(['AR.REQUESTING_DEPT' => $deptCode]);
        }

        if($courseFilter->purpose === 'serviceCourses'){
            $query->where(['AR.SERVICING_DEPT' => $deptCode]);
        }

        if(!empty($courseFilter->status)){
            $query->andWhere(['AR.STATUS_ID' => $courseFilter->status]);
        }

        $query->joinWith(['status ST' => function(ActiveQuery $q){
            $q->select([
                'ST.STATUS_ID',
                'ST.STATUS_NAME'
            ]);
        }], true, 'INNER JOIN')
        ->joinWith(['requestingDept RDEPT' => function(ActiveQuery $q){
            $q->select([
                'RDEPT.DEPT_CODE',
                'RDEPT.DEPT_NAME'
            ]);
        }], true, 'INNER JOIN')
        ->joinWith(['servicingDept SDEPT' => function(ActiveQuery $q){
            $q->select([
                'SDEPT.DEPT_CODE',
                'SDEPT.DEPT_NAME'
            ]);
        }], true, 'INNER JOIN');

        if($courseFilter->purpose === 'requestedCourses' && !empty($courseFilter->servicingDepartment)){
            $query->andWhere(['SDEPT.DEPT_CODE' => $courseFilter->servicingDepartment]);
        }

        if($courseFilter->purpose === 'serviceCourses' && !empty($courseFilter->requestingDepartment)){
            $query->andWhere(['RDEPT.DEPT_CODE' => $courseFilter->requestingDepartment]);
        }

        $query->joinWith(['marksheet MD' => function(ActiveQuery $q){
            $q->select([
                'MD.MRKSHEET_ID',
                'MD.SEMESTER_ID',
                'MD.COURSE_ID',
                'MD.GROUP_CODE',
            ]);
        }], true, 'INNER JOIN')
        ->joinWith(['marksheet.semester SM' => function(ActiveQuery $q){
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
        ->joinWith(['marksheet.course CS' => function(ActiveQuery $q){
            $q->select([
                'CS.COURSE_ID',
                'CS.COURSE_CODE',
                'CS.COURSE_NAME',
            ]);
        }], true, 'INNER JOIN');

        if(!empty($courseFilter->courseCode)){
            $query->andWhere(['like', 'CS.COURSE_CODE', $courseFilter->courseCode]);
        }

        if(!empty($courseFilter->courseName)){
            $query->andWhere(['like', 'CS.COURSE_NAME', $courseFilter->courseName]);
        }

        // Apply in-grid filters (optional) if provided
        if (!empty($this->courseCode)) {
            $query->andWhere(['like', 'CS.COURSE_CODE', $this->courseCode]);
        }
        if (!empty($this->statusName)) {
            $query->andWhere(['ST.STATUS_NAME' => $this->statusName]);
        }

        $query->joinWith(['requestedBy REQ' => function(ActiveQuery $q){
            $q->select([
                'REQ.PAYROLL_NO',
                'REQ.EMP_ID',
                'REQ.SURNAME',
                'REQ.OTHER_NAMES',
                'REQ.EMP_TITLE'
            ]);
        }], true, 'INNER JOIN')
        ->joinWith(['attendedBy ATT' => function(ActiveQuery $q){
            $q->select([
                'ATT.PAYROLL_NO',
                'ATT.EMP_ID',
                'ATT.SURNAME',
                'ATT.OTHER_NAMES',
                'ATT.EMP_TITLE'
            ]);
        }])
        ->orderBy(['AR.REQUEST_ID' =>  SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 50,
            ],
        ]);
    }
}

