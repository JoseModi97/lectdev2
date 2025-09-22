<?php

namespace app\models\search;

use app\models\CourseAssignment;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class NoOfAllocatedCoursesPerLecturerSearch extends CourseAssignment
{
    /**
     * {@inheritdoc}
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'staff.SURNAME',
            'staff.OTHER_NAMES'
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
                    'PAYROLL_NO',
                    'staff.SURNAME',
                    'staff.OTHER_NAMES'
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
     *
     * @param array $params
     * @param array $additionalParams
     * @return ActiveDataProvider
     */
    public function search(array $params, array $additionalParams): ActiveDataProvider
    {
        $query = CourseAssignment::find()->alias('CA')
            ->select([
                'CA.PAYROLL_NO',
                'COUNT(*) AS coursesNumber'
            ])
            ->where(['LIKE', 'CA.MRKSHEET_ID', $additionalParams['academicYear'] . '%', false])
            ->joinWith(['staff ST' => function(ActiveQuery $q){
                    $q->select([
                        'ST.PAYROLL_NO',
                        'ST.EMP_TITLE',
                        'ST.SURNAME',
                        'ST.OTHER_NAMES',
                        'ST.STATUS_DESC',
                        'ST.JOB_CADRE',
                        'ST.DEPT_CODE'
                    ]);
                }
            ], true, 'INNER JOIN')
            ->andWhere([
                'ST.STATUS_DESC' => 'ACTIVE',
                'ST.JOB_CADRE' => 'ACADEMIC',
                'ST.DEPT_CODE' => $additionalParams['deptCode']
            ])
            ->groupBy([
                'CA.PAYROLL_NO',
                'ST.PAYROLL_NO',
                'ST.EMP_TITLE',
                'ST.SURNAME',
                'ST.OTHER_NAMES'
            ])
            ->orderBy(['CA.PAYROLL_NO' => SORT_ASC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 20,
            ],
        ]);

        $this->load($params);

        $query->andFilterWhere(['CA.PAYROLL_NO' => $this->PAYROLL_NO]);
        $query->andFilterWhere(['LIKE', 'ST.SURNAME', $this->getAttribute('staff.SURNAME')]);
        $query->andFilterWhere(['LIKE', 'ST.OTHER_NAMES', $this->getAttribute('staff.SURNAME')]);

        return $dataProvider;
    }
}