<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

namespace app\models\search;

use app\models\StudentCoursework;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class MarksToApproveSearch extends StudentCoursework
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
        $approvalStatus = 'APPROVED';
        $assessmentName = $additionalParams['assessmentName'];
        $isExamComponent = $additionalParams['isExamComponent'];

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
                ]);
            }], true, 'INNER JOIN');

        if($level === 'hod'){
            $query->andWhere([
                'CW.LECTURER_APPROVAL_STATUS' => $approvalStatus,
            ]);
        }

        if($level === 'dean'){
            if($assessmentName === 'EXAM' || $isExamComponent){
                $query->andWhere([
                    'CW.LECTURER_APPROVAL_STATUS' => $approvalStatus,
                    'CW.HOD_APPROVAL_STATUS' => $approvalStatus,
                ]);
            }
            else{
                $query->andWhere([
                    'CW.LECTURER_APPROVAL_STATUS' => $approvalStatus,
                ]);
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 50,
            ],
        ]);

        $this->load($params);

        if(!$this->validate()) return $dataProvider;

        $query->orderBy(['CW.COURSE_WORK_ID' => SORT_DESC]);

        $query->andFilterWhere([
            'CW.REGISTRATION_NUMBER' => $this->REGISTRATION_NUMBER,
            "TO_CHAR(CW.DATE_ENTERED, 'DD-MON-YYYY')" => strtoupper($this->DATE_ENTERED)
        ]);

        return $dataProvider;
    }
}