<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 18-06-2021 10:20:07 
 * @modify date 18-06-2021 10:20:07 
 * @desc [description]
 */

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

use app\models\StudentMarksheetView;

class StudentMarksheetSearch extends StudentMarksheetView
{
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

        $query = StudentMarksheetView::find()->alias('MS')
            ->select([
                'MS.MRKSHEET_ID', 
                'MS.REGISTRATION_NUMBER',
                'MS.SURNAME',
                'MS.OTHER_NAMES',
                'MS.EMAIL',
                'MS.TELEPHONE',
                'MS.EXAMTYPE_CODE',
                'MS.DESCRIPTION'
            ])
            ->where(['MS.MRKSHEET_ID' => $marksheetId])
            ->orderBy(['MS.REGISTRATION_NUMBER' =>  SORT_ASC])
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

        return $dataProvider;
    }
}