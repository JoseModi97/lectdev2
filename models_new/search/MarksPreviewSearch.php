<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

namespace app\models\search;

use app\models\TempMarksheet;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class MarksPreviewSearch extends TempMarksheet
{
    /**
     * @return array
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'student.SURNAME',
            'student.OTHER_NAMES'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [[
                'REGISTRATION_NUMBER',
                'EXAM_TYPE',
                'GRADE',
                'student.SURNAME',
                'student.OTHER_NAMES'
            ], 'safe']
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
     * @return ActiveDataProvider
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = TempMarksheet::find()->alias('TM')
            ->select([
                'TM.MRKSHEET_ID',
                'TM.REGISTRATION_NUMBER',
                'TM.EXAM_TYPE',
                'TM.COURSE_MARKS',
                'TM.EXAM_MARKS',
                'TM.FINAL_MARKS',
                'TM.GRADE'
            ])
            ->joinWith(['student ST' => function(ActiveQuery $q){
                $q->select([
                    'ST.REGISTRATION_NUMBER',
                    'ST.SURNAME',
                    'ST.OTHER_NAMES'
                ]);
            }], true, 'INNER JOIN')
            ->where(['TM.MRKSHEET_ID' => $params['marksheetId']])
            ->orderBy(['TM.FINAL_MARKS' =>  SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 50,
            ]
        ]);

        $this->load($params);

        if(!$this->validate()){
            return $dataProvider;
        }

        $query->andFilterWhere(['LIKE', 'TM.REGISTRATION_NUMBER', $this->REGISTRATION_NUMBER]);
        $query->andFilterWhere(['LIKE', 'TM.GRADE', $this->GRADE]);
        $query->andFilterWhere(['LIKE', 'TM.EXAM_TYPE', $this->EXAM_TYPE]);
        $query->andFilterWhere(['LIKE', 'ST.SURNAME', $this->getAttribute('student.SURNAME')]);
        $query->andFilterWhere(['LIKE', 'ST.OTHER_NAMES', $this->getAttribute('student.OTHER_NAMES')]);

        return $dataProvider;
    }
}