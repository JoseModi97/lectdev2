<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 28-01-2021 12:01:04 
 * @modify date 28-01-2021 12:01:04 
 * @desc [description]
 */

namespace app\models\search;

use app\models\StudentCoursework;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class MarksSearch extends StudentCoursework
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [
                [
                    'REGISTRATION_NUMBER',
                    'DATE_ENTERED'
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
     * @param array $params
     * @param array $additionalParams
     * @return ActiveDataProvider
     */
    public function search(array $params, array $additionalParams = []): ActiveDataProvider
    {
        $assessmentId = $additionalParams['assessmentId'];
        $level = $additionalParams['level'];
        $deptCode = $additionalParams['deptCode'];
        $facCode = $additionalParams['facCode'];
        $approvalStatus = 'APPROVED';
        $assessmentName = $additionalParams['assessmentName'];

        $query = StudentCoursework::find()->alias('CW')
            ->select([
                'CW.COURSE_WORK_ID',
                'CW.REGISTRATION_NUMBER',
                'CW.ASSESSMENT_ID',
                'CW.MARK',
                'CW.MARK_TYPE',
                'CW.RAW_MARK',
                'CW.REMARKS',
                'CW.USER_ID',
                'CW.DATE_ENTERED',
                'CW.LECTURER_APPROVAL_STATUS',
                'CW.HOD_APPROVAL_STATUS',
                'CW.DEAN_APPROVAL_STATUS'
            ])
            ->where(['CW.ASSESSMENT_ID' => $assessmentId])
            ->joinWith(['student ST' => function(ActiveQuery $q){
                    $q->select([
                        'ST.REGISTRATION_NUMBER', 
                        'ST.DEGREE_CODE',
                    ]);
                }
            ], true, 'LEFT JOIN')
            ->joinWith(['student.degreeProgramme DEG' => function(ActiveQuery $q){
                    $q->select([
                        'DEG.DEGREE_CODE', 
                        'DEG.FACUL_FAC_CODE'
                    ]);
                }
            ], true, 'LEFT JOIN')
            ->joinWith(['student.degreeProgramme.department DEPT' => function(ActiveQuery $q){
                    $q->select([
                        'DEPT.DEPT_CODE', 
                        'DEPT.FAC_CODE'
                    ]);
                }
            ], true, 'LEFT JOIN');

        if($level === 'hod'){
            $query->andWhere([
                'CW.LECTURER_APPROVAL_STATUS' => $approvalStatus,
                'DEPT.DEPT_CODE' => $deptCode
            ]);
        } elseif($level === 'dean'){
            if($assessmentName === 'EXAM'){
                $query->andWhere([
                    'CW.LECTURER_APPROVAL_STATUS' => $approvalStatus,
                    'CW.HOD_APPROVAL_STATUS' => $approvalStatus,
                    'DEPT.FAC_CODE' => $facCode,
                    'DEPT.DEPT_CODE' => $deptCode
                ]);
            }
            else{
                $query->andWhere([
                    'CW.LECTURER_APPROVAL_STATUS' => $approvalStatus,
                    'DEPT.FAC_CODE' => $facCode,
                    'DEPT.DEPT_CODE' => $deptCode
                ]);
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 20,
            ],
        ]);

        $this->load($params);

        if(!$this->validate()) return $dataProvider;

        $query->andFilterWhere([
            'CW.REGISTRATION_NUMBER' => $this->REGISTRATION_NUMBER,
            "TO_CHAR(CW.DATE_ENTERED, 'DD-MON-YYYY')" => strtoupper($this->DATE_ENTERED)
        ]);

        if($level === 'hod'){
            $query->orderBy(['CW.COURSE_WORK_ID' => SORT_ASC]);
        } elseif($level === 'deanDirector'){
            $query->orderBy(['DEG.DEGREE_CODE' => SORT_ASC]);
        }

        return $dataProvider;
    }
}