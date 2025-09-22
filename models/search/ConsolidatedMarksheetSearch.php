<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

namespace app\models\search;

use app\models\Marksheet;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class ConsolidatedMarksheetSearch extends Marksheet
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
     * @param array $params
     * @param array $additionalParams
     * @return ActiveDataProvider
     */
    public function search(array $params, array $additionalParams = []):ActiveDataProvider
    {
        $marksheetId = $additionalParams['marksheetId'];

        $query = Marksheet::find()->alias('MS')
            ->select([
                'MS.MRKSHEET_ID', 
                'MS.REGISTRATION_NUMBER', 
                'MS.EXAM_TYPE',
                'MS.COURSE_MARKS',
                'MS.EXAM_MARKS',
                'MS.FINAL_MARKS',
                'MS.GRADE'
            ])
            ->where(['MS.MRKSHEET_ID' => $marksheetId])
            ->orderBy(['MS.FINAL_MARKS' =>  SORT_DESC])
            ->joinWith(['student ST' => function(ActiveQuery $q){
                    $q->select([ 
                        'ST.REGISTRATION_NUMBER',
                        'ST.SURNAME',
                        'ST.OTHER_NAMES',
                        'ST.EMAIL',
                        'ST.TELEPHONE'
                    ]);
                }
            ], true, 'LEFT JOIN')
            ->joinWith(['examType ET' => function(ActiveQuery $q){
                    $q->select([ 
                        'ET.EXAMTYPE_CODE',
                        'ET.DESCRIPTION'
                    ]);
                }
            ], true, 'LEFT JOIN');

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