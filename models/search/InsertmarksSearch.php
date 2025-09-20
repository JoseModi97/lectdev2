<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 17-12-2020 17:12:49 
 * @modify date 17-12-2020 17:12:49 
 * @desc [description]
 */

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query; 

use app\models\CourseWorkAssessment;

class InsertmarksSearch extends CourseWorkAssessment{

    public function attributes(): array
    {
        // add related fields to searchable attributes
        return array_merge(parent::attributes(), [
            'marksheet.REGISTRATION_NUMBER',
            'assessmentType.ASSESSMENT_NAME', 
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
                    'ASSESSMENT_ID',
                    'WEIGHT',
                    'RESULT_DUE_DATE',
                    'marksheet.REGISTRATION_NUMBER',
                    'assessmentType.ASSESSMENT_NAME', 
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
    public function search(array $params, array $additionalParams = []): ActiveDataProvider
    {
        $query = CourseWorkAssessment::find()->alias('CW')
                ->joinWith(['assessmentType AT', 'marksheet MS'])
                ->where(['MS.MRKSHEET_ID' => $additionalParams['marksheetId']])
                ->andWhere(['CW.ASSESSMENT_ID' => $additionalParams['assessmentId']]);
               
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 20,
            ],
        ]);

        $this->load($params);

        if(!$this->validate()) return $dataProvider;

        $query->orderBy([
            'MS.REGISTRATION_NUMBER' =>  SORT_ASC
        ]);

        return $dataProvider;
    }
}