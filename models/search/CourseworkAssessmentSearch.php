<?php
/**
 * @author Jack Jmm
 * @email jackmutiso37@gmail.com
 * @create date 15-09-2025 17:12:49 
 * @desc Get created course works / exam components
 */

namespace app\models\search;

use app\models\CourseWorkAssessment;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class CourseworkAssessmentSearch extends CourseWorkAssessment
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
     * @param array $additionalParams
     * @return ActiveDataProvider
     */
    public function search(array $additionalParams): ActiveDataProvider
    {
        $query = CourseWorkAssessment::find()->alias('CW')
            ->joinWith([
                'assessmentType AT',
                'marksheetDef.course CS'
            ])
            ->where(['CW.MARKSHEET_ID' => $additionalParams['marksheetId']]);

        if($additionalParams['type'] === 'component'){
            $query->andWhere(['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT']);
        }else{
            $query->andWhere(['NOT', ['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT']])
                ->andWhere(['NOT', ['AT.ASSESSMENT_NAME' => 'EXAM']]);
        }

        $query->orderBy(['CW.ASSESSMENT_ID' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 20,
            ],
        ]);
    }
}