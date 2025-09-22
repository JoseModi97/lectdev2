<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc This file gets programmes in a faculty
 */
namespace app\models\search;

use app\models\DegreeProgramme;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class ProgrammesInAFacultySearch extends DegreeProgramme
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [
                [
                    'DEGREE_CODE',
                    'DEGREE_NAME'
                ],
                'safe'
            ],
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
        $facCode = $additionalParams['facCode'];

        $query = DegreeProgramme::find()->alias('DEG')
            ->select(['DEG.DEGREE_CODE', 'DEG.DEGREE_NAME'])
            ->where(['DEG.FACUL_FAC_CODE' => $facCode])
            ->asArray();

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

        $query->orderBy(['DEG.DEGREE_CODE' => SORT_ASC]);

        $query->andFilterWhere(['like', 'DEG.DEGREE_CODE', $this->DEGREE_CODE]);
        $query->andFilterWhere(['like', 'DEG.DEGREE_NAME', $this->DEGREE_NAME]);

        return $dataProvider;
    }
}