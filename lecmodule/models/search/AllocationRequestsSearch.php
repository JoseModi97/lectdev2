<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 12-01-2021 14:26:29
 * @modify date 12-01-2021 14:26:29
 * @desc [description]
 */

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

use app\models\AllocationRequest;

class AllocationRequestsSearch extends AllocationRequest
{
    /**
     * {@inheritdoc}
     */
    public function attributes(): array
    {
        // add related fields to searchable attributes
        return array_merge(parent::attributes(), [
            'marksheet.course.COURSE_CODE',
            'marksheet.course.COURSE_NAME',
            'requestingDept.DEPT_NAME',
            'servicingDept.DEPT_NAME',
            'status.STATUS_NAME'
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
                    'marksheet.course.COURSE_CODE',
                    'marksheet.course.COURSE_NAME',
                    'requestingDept.DEPT_NAME',
                    'servicingDept.DEPT_NAME',
                    'status.STATUS_NAME'
                ],
                'safe'],
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
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $additionalParams = []): ActiveDataProvider
    {
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

        if($additionalParams['type'] === 'requesting')
            $query->where(['AR.REQUESTING_DEPT' => $additionalParams['deptCode']]);
        else
            $query->where(['AR.SERVICING_DEPT' => $additionalParams['deptCode']]);

        $query->joinWith(['status ST' => function(ActiveQuery $q){
            $q->select([
                'ST.STATUS_ID',
                'ST.STATUS_NAME'
            ]);
        }
        ], true, 'LEFT JOIN')
            ->joinWith(['requestingDept RDEPT' => function(ActiveQuery $q){
                $q->select([
                    'RDEPT.DEPT_CODE',
                    'RDEPT.DEPT_NAME'
                ]);
            }
            ], true, 'LEFT JOIN')
            ->joinWith(['servicingDept SDEPT' => function(ActiveQuery $q){
                $q->select([
                    'SDEPT.DEPT_CODE',
                    'SDEPT.DEPT_NAME'
                ]);
            }
            ], true, 'LEFT JOIN')
            ->joinWith(['marksheet MD' => function(ActiveQuery $q){
                $q->select([
                    'MD.MRKSHEET_ID',
                    'MD.SEMESTER_ID',
                    'MD.COURSE_ID',
                    'MD.GROUP_CODE',
                ]);
            }
            ], true, 'LEFT JOIN')
            ->joinWith(['marksheet.semester SM' => function(ActiveQuery $q){
                $q->select([
                    'SM.SEMESTER_ID',
                    'SM.SEMESTER_CODE',
                    'SM.DEGREE_CODE',
                    'SM.ACADEMIC_YEAR',
                    'SM.LEVEL_OF_STUDY',
                    'SM.DESCRIPTION_CODE'
                ]);
            }
            ], true, 'LEFT JOIN')
            ->andWhere(['SM.ACADEMIC_YEAR' => $additionalParams['academicYear']])
            ->joinWith(['marksheet.semester.degreeProgramme DEG' => function(ActiveQuery $q){
                $q->select([
                    'DEG.DEGREE_CODE',
                    'DEG.DEGREE_NAME'
                ]);
            }
            ], true, 'LEFT JOIN')
            ->joinWith(['marksheet.semester.semesterDescription DESC' => function(ActiveQuery $q){
                $q->select([
                    'DESC.DESCRIPTION_CODE',
                    'DESC.SEMESTER_DESC'
                ]);
            }
            ], true, 'LEFT JOIN')
            ->joinWith(['marksheet.group GR' => function(ActiveQuery $q){
                $q->select([
                    'GR.GROUP_CODE',
                    'GR.GROUP_NAME'
                ]);
            }
            ], true, 'LEFT JOIN')
            ->joinWith(['marksheet.course CS' => function(ActiveQuery $q){
                $q->select([
                    'CS.COURSE_ID',
                    'CS.COURSE_CODE',
                    'CS.COURSE_NAME',
                    'CS.DEPT_CODE'
                ]);
            }
            ], true, 'LEFT JOIN')
            ->joinWith(['requestedBy REQ' => function(ActiveQuery $q){
                $q->select([
                    'REQ.PAYROLL_NO',
                    'REQ.EMP_ID',
                    'REQ.SURNAME',
                    'REQ.OTHER_NAMES',
                    'REQ.EMP_TITLE'
                ]);
            }
            ], true, 'LEFT JOIN')
            ->joinWith(['attendedBy ATT' => function(ActiveQuery $q){
                $q->select([
                    'ATT.PAYROLL_NO',
                    'ATT.EMP_ID',
                    'ATT.SURNAME',
                    'ATT.OTHER_NAMES',
                    'ATT.EMP_TITLE'
                ]);
            }
            ], true, 'LEFT JOIN');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 20,
            ],
        ]);

        $this->load($params);

        $query->andFilterWhere([
            'CS.COURSE_CODE' => $this->getAttribute('marksheet.course.COURSE_CODE'),
            'CS.COURSE_NAME' => $this->getAttribute('marksheet.course.COURSE_NAME'),
            'RDEPT.DEPT_NAME' => $this->getAttribute('requestingDept.DEPT_NAME'),
            'SDEPT.DEPT_NAME' => $this->getAttribute('servicingDept.DEPT_NAME'),
            'ST.STATUS_NAME' => $this->getAttribute('status.STATUS_NAME'),
        ]);

        $query->orderBy([
            'AR.REQUEST_ID' =>  SORT_DESC
        ]);

        return $dataProvider;
    }
}

