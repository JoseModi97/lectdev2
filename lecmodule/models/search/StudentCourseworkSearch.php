<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc This file gets students marks for an exam or assessment amrks
 */

namespace app\models\search;

use app\models\StudentCoursework;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class StudentCourseworkSearch extends StudentCoursework
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
    public function search(array $params, array $additionalParams): ActiveDataProvider
    {
        $query = StudentCoursework::find()->alias('SC')
            ->select([
                'SC.COURSE_WORK_ID',
                'SC.REGISTRATION_NUMBER',
                'SC.ASSESSMENT_ID',
                'SC.MARK',
                'SC.MARK_TYPE',
                'SC.RAW_MARK',
                'SC.REMARKS',
                'SC.USER_ID',
                'SC.DATE_ENTERED',
                'SC.LECTURER_APPROVAL_STATUS'
            ])
            ->where(['SC.ASSESSMENT_ID' => $additionalParams['assessmentId']])
            ->joinWith(['student ST' => function(ActiveQuery $q){
                    $q->select([ 
                        'ST.REGISTRATION_NUMBER',
                        'ST.SURNAME',
                        'ST.OTHER_NAMES'
                    ]);
                }
            ], true, 'INNER JOIN')
            ->joinWith(['assessment AS' => function(ActiveQuery $q){
                    $q->select([ 
                        'AS.ASSESSMENT_ID',
                        'AS.MARKSHEET_ID'
                    ]);
                }
            ], true, 'INNER JOIN')
            ->orderBy(['SC.REGISTRATION_NUMBER' => SORT_ASC])
            ->asArray();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 50,
            ],
        ]);

        $this->load($params);

        $query->andFilterWhere(['LIKE', 'SC.REGISTRATION_NUMBER', $this->REGISTRATION_NUMBER,]);
        $query->andFilterWhere(["TO_CHAR(SC.DATE_ENTERED, 'DD-MON-YYYY')" => strtoupper($this->DATE_ENTERED)]);

        return $dataProvider;
    }
}