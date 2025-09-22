<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc This file gets students registered in a marksheet for purposes of entering their marks. Only students with no
 * marks for the assessment provided will be returned.
 */

namespace app\models\search;

use app\models\Marksheet;
use app\models\StudentCoursework;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class CreateMarksSearch extends Marksheet
{
    /**
     * {@inheritdoc}
     * Add related fields to searchable attributes
     */
    public function attributes(): array
    {
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
        $assessmentId = $additionalParams['assessmentId'];
        $marksheetId = $additionalParams['marksheetId'];
        $semesterType = $additionalParams['semesterType'];

        $subQuery = StudentCoursework::find()->alias('SC')->select(['REGISTRATION_NUMBER'])
            ->where(['SC.ASSESSMENT_ID' => $assessmentId]);

        $query = Marksheet::find()->alias('MS')
            ->select([
                'MS.MRKSHEET_ID',
                'MS.REGISTRATION_NUMBER',
                'MS.EXAM_TYPE'
            ])
            ->where(['MS.MRKSHEET_ID' => $marksheetId])
            ->andWhere(['NOT', ['IN', 'MS.REGISTRATION_NUMBER', $subQuery]]);

        /**
         * This system requires that students with supplementary courses have their own marksheets.
         * Before this, a single marksheet included also the students with supplementary courses too.
         * Because of this we want to make sure that only students with no supplementary are
         * pulled for the old marksheets too.
         */
        if($semesterType !== 'SUPPLEMENTARY'){
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
        }], true, 'INNER JOIN')
            ->joinWith(['examType ET' => function(ActiveQuery $q){
                $q->select([
                    'ET.EXAMTYPE_CODE',
                    'ET.DESCRIPTION'
                ]);
            }], true, 'INNER JOIN')
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

        $query->orderBy(['MS.REGISTRATION_NUMBER' => SORT_ASC]);

        $query->andFilterWhere(['like', 'MS.REGISTRATION_NUMBER', $this->REGISTRATION_NUMBER]);
        $query->andFilterWhere(['like', 'ST.SURNAME', $this->getAttribute('student.SURNAME')]);
        $query->andFilterWhere(['like', 'ST.OTHER_NAMES', $this->getAttribute('student.OTHER_NAMES')]);
        $query->andFilterWhere(['like', 'ET.EXAMTYPE_CODE', $this->getAttribute('examType.EXAMTYPE_CODE')]);

        return $dataProvider;
    }
}