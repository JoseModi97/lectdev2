<?php
/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 17-12-2020 17:12:49 
 * @modify date 17-12-2020 17:12:49 
 * @desc [description]
 */

namespace app\models\search;

use app\models\Marksheet;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class MarksheetSearch extends Marksheet
{
    /**
     * {@inheritdoc}
     */
    public function attributes(): array
    {
        // add related fields to searchable attributes
        return array_merge(parent::attributes(), [
            'student.SURNAME',
            'student.OTHER_NAMES',
            'examType.EXAMTYPE_CODE'
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
                    'REGISTRATION_NUMBER',
                    'student.SURNAME',
                    'student.OTHER_NAMES',
                    'examType.EXAMTYPE_CODE'
                ],
            'safe'
            ],
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
        $query = Marksheet::find()->alias('MS')
            ->select([
                'MS.MRKSHEET_ID', 
                'MS.REGISTRATION_NUMBER', 
                'MS.EXAM_TYPE'
            ])
            ->where(['MS.MRKSHEET_ID' => $additionalParams['marksheetId']]);
        
        /**
         * This system requires that students with supplementary courses have their own marksheets.
         * Before this, a single marksheet included also the students with supplementary courses.
         * Because of this we want to make sure that only students with no supplementary are
         * pulled for the old marksheets.
         */
        if($additionalParams['semesterType'] !== 'SUPPLEMENTARY'){
            $query->andWhere(['NOT', ['MS.EXAM_TYPE' => 'SUPP']]);        
        }

        $query->joinWith(['student ST' => function(ActiveQuery $q){
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
            ], true, 'LEFT JOIN')
            ->asArray();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 20,
            ],
        ]);

        $this->load($params);

        if(!$this->validate()){
            return $dataProvider;
        } 

        $query->andFilterWhere(['like', 'MS.REGISTRATION_NUMBER', $this->REGISTRATION_NUMBER]);
        $query->andFilterWhere(['like', 'ST.SURNAME', $this->getAttribute('student.SURNAME')]);
        $query->andFilterWhere(['like', 'ST.OTHER_NAMES', $this->getAttribute('student.OTHER_NAMES')]);
        $query->andFilterWhere(['like', 'ET.EXAMTYPE_CODE', $this->getAttribute('examType.EXAMTYPE_CODE')]);

        $query->orderBy(['MS.REGISTRATION_NUMBER' => SORT_ASC]);

        return $dataProvider;
    }
}