<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 26-01-2021 17:52:50 
 * @modify date 26-01-2021 17:52:50 
 * @desc [description]
 */

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
USE yii\db\ActiveQuery;

use app\models\Department;

class DepartmentsSearch extends Department 
{
      
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [
                [
                    'DEPT_CODE', 
                    'DEPT_NAME', 
                    'HR_DEPT_CODE', 
                    'DEPT_TYPE', 
                    'FAC_CODE'
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
     * @param array $additionalParams
     * @return ActiveDataProvider
     */
    public function search(array $params, array $additionalParams = []):ActiveDataProvider
    {
        $facCode = $additionalParams['facCode'];

        $query = Department::find()->alias('DEPT')
            ->select([
                'DEPT.DEPT_CODE', 
                'DEPT.DEPT_NAME'
            ])
            ->where([
                'DEPT.FAC_CODE' => $facCode,
                'DEPT.DEPT_TYPE' => 'ACADEMIC'
            ])
            ->asArray();
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 20,
            ],
        ]);

        $this->load($params);

        if(!$this->validate()) return $dataProvider;

        $query->andFilterWhere(['like', 'DEPT.DEPT_CODE', $this->DEPT_CODE]);
        $query->andFilterWhere(['like', 'DEPT.DEPT_NAME', $this->DEPT_NAME]);
        $query->andFilterWhere(['like', 'DEPT.HR_DEPT_CODE', $this->HR_DEPT_CODE]);
        $query->andFilterWhere(['like', 'DEPT.DEPT_TYPE', $this->DEPT_TYPE]);
        $query->andFilterWhere(['like', 'DEPT.FAC_CODE', $this->FAC_CODE]);

        return $dataProvider;
    }
}
