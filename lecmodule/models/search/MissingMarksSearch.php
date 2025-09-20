<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

namespace app\models\search;

use app\models\Marksheet;
use app\models\StudentCoursework;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class MissingMarksSearch extends Marksheet
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
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
     * @param array $additionalParams
     * @return ActiveDataProvider
     */
    public function search(array $additionalParams = []):ActiveDataProvider
    {
        $assessmentId = $additionalParams['assessmentId'];
        $marksheetId = $additionalParams['marksheetId'];

        $subQuery = StudentCoursework::find()->alias('SC')->select(['REGISTRATION_NUMBER'])
            ->where(['SC.ASSESSMENT_ID' => $assessmentId]);

        $query = Marksheet::find()->alias('MS')
            ->select([
                'MS.MRKSHEET_ID', 
                'MS.REGISTRATION_NUMBER', 
                'MS.EXAM_TYPE'
            ])
            ->joinWith(['student ST' => function(ActiveQuery $q){
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
            ->where(['MS.MRKSHEET_ID' => $marksheetId])
            ->andWhere(['NOT', ['IN', 'MS.REGISTRATION_NUMBER', $subQuery]])
            ->orderBy(['MS.REGISTRATION_NUMBER' => SORT_ASC])
            ->asArray();

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 50,
            ],
        ]);
    }
}


