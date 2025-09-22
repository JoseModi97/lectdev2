<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query; 
USE yii\db\ActiveQuery;

use app\models\ReturnedScript;

class ReturnedScriptsSearch extends ReturnedScript {

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        /** Remove searchable attributes */
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
     *
     * @param array $params
     * @param array $additionalParams
     * @return ActiveDataProvider
     */
    public function search(array $params, array $additionalParams = []):ActiveDataProvider
    {
        $marksheetId = $additionalParams['marksheetId'];
        $returnedStatus = (int)1;

        $query = ReturnedScript::find()->alias('RS')
            ->select([
                'RS.RETURNED_SCRIPT_ID',
                'RS.RETURNED_DATE',
                'RS.REMARKS',
                'RS.MARKSHEET_ID',
                'RS.REGISTRATION_NUMBER',
                'RS.RECEIVED_BY',
                'RS.RETURNED_BY',
                'RS.STATUS'
            ])
            ->where([
                'RS.STATUS' => $returnedStatus, 
                'RS.MARKSHEET_ID' => $marksheetId
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 35,
            ],
        ]);

        $this->load($params);

        if(!$this->validate()) return $dataProvider;

        return $dataProvider;
    }
}