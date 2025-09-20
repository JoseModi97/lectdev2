<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc Display assessments in a marksheet for purposes of viewing marks for approvals
 */

namespace app\models\search;

use app\models\CourseWorkAssessment;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class MarksApprovalAssessmentSearch extends CourseWorkAssessment
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
     * Bypass scenarios() implementation in the parent class
     */
    public function scenarios(): array
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     * @param string $marksheetId
     * @return ActiveDataProvider
     */
    public function search(string $marksheetId): ActiveDataProvider
    {
        $query = CourseWorkAssessment::find()->alias('CW')
            ->select([
                'CW.ASSESSMENT_ID',
                'CW.WEIGHT',
                'CW.MARKSHEET_ID',
                'CW.RESULT_DUE_DATE',
                'CW.DIVIDER',
                'CW.ASSESSMENT_TYPE_ID'
            ])
            ->where(['CW.MARKSHEET_ID' => $marksheetId])
            ->joinWith(['assessmentType AT' => function(ActiveQuery $q){
                $q->select([
                    'AT.ASSESSMENT_TYPE_ID',
                    'AT.ASSESSMENT_NAME',
                    'AT.ASSESSMENT_DESCRIPTION'
                ]);
            }], true, 'INNER JOIN')
            ->orderBy(['CW.ASSESSMENT_ID' => SORT_ASC])
            ->asArray();

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => false
        ]);
    }
}