<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Semester;
use Yii;

/**
 * SemesterSearch represents the model behind the search form of `app\models\Semester`.
 */
class SemesterSearch extends Semester
{
    public $purpose; // New property

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['SEMESTER_ID', 'ACADEMIC_YEAR', 'DEGREE_CODE', 'INTAKE_CODE', 'START_DATE', 'END_DATE', 'FIRST_SEMESTER', 'SEMESTER_NAME', 'CLOSING_DATE', 'ADMIN_USER', 'GROUP_CODE', 'REGISTRATION_DEADLINE', 'DESCRIPTION_CODE', 'SESSION_TYPE', 'DISPLAY_DATE', 'REGISTRATION_DATE', 'SEMESTER_TYPE', 'purpose'], 'safe'],
            [['LEVEL_OF_STUDY', 'SEMESTER_CODE'], 'integer'],
            [['purpose'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = Semester::find()
            ->select([
                'MUTHONI.SEMESTERS.*',
                'MUTHONI.SEMESTERS.LEVEL_OF_STUDY AS SEMESTER_LEVEL_OF_STUDY',
                'MUTHONI.SEMESTERS.GROUP_CODE AS SEMESTER_GROUP_CODE',

                'MUTHONI.LEVEL_OF_STUDY.LEVEL_OF_STUDY AS LEVEL_CODE',
                'MUTHONI.LEVEL_OF_STUDY.NAME AS LEVEL_NAME',

                'MUTHONI.GROUPS.GROUP_CODE AS GROUP_CODE_ALIAS',
                'MUTHONI.GROUPS.GROUP_NAME AS GROUP_NAME',
            ])
            ->distinct()
            ->joinWith(['levelOfStudy' => function ($q) {
                $q->select([
                    'MUTHONI.LEVEL_OF_STUDY.LEVEL_OF_STUDY',
                    'MUTHONI.LEVEL_OF_STUDY.NAME',
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['group' => function ($q) {
                $q->select([
                    'MUTHONI.GROUPS.GROUP_CODE',
                    'MUTHONI.GROUPS.GROUP_NAME',
                ]);
            }], true, 'INNER JOIN')
            ->orderBy([
                'MUTHONI.SEMESTERS.GROUP_CODE' => SORT_ASC,
                'MUTHONI.SEMESTERS.LEVEL_OF_STUDY' => SORT_ASC,
                'MUTHONI.SEMESTERS.SEMESTER_CODE' => SORT_ASC,
            ]);



        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'MUTHONI.SEMESTERS.LEVEL_OF_STUDY' => $this->LEVEL_OF_STUDY,
            'MUTHONI.SEMESTERS.SEMESTER_CODE' => $this->SEMESTER_CODE,
        ]);

        $query->andFilterWhere(['like', 'MUTHONI.SEMESTERS.SEMESTER_ID', $this->SEMESTER_ID])
            ->andFilterWhere(['like', 'MUTHONI.SEMESTERS.ACADEMIC_YEAR', $this->ACADEMIC_YEAR])
            ->andFilterWhere(['like', 'MUTHONI.SEMESTERS.DEGREE_CODE', $this->DEGREE_CODE])
            ->andFilterWhere(['like', 'MUTHONI.SEMESTERS.INTAKE_CODE', $this->INTAKE_CODE])
            ->andFilterWhere(['like', 'MUTHONI.SEMESTERS.START_DATE', $this->START_DATE])
            ->andFilterWhere(['like', 'MUTHONI.SEMESTERS.END_DATE', $this->END_DATE])
            ->andFilterWhere(['like', 'MUTHONI.SEMESTERS.FIRST_SEMESTER', $this->FIRST_SEMESTER])
            ->andFilterWhere(['like', 'MUTHONI.SEMESTERS.SEMESTER_NAME', $this->SEMESTER_NAME])
            ->andFilterWhere(['like', 'MUTHONI.SEMESTERS.CLOSING_DATE', $this->CLOSING_DATE])
            ->andFilterWhere(['like', 'MUTHONI.SEMESTERS.ADMIN_USER', $this->ADMIN_USER])
            ->andFilterWhere(['like', 'MUTHONI.SEMESTERS.GROUP_CODE', $this->GROUP_CODE])
            ->andFilterWhere(['like', 'MUTHONI.SEMESTERS.REGISTRATION_DEADLINE', $this->REGISTRATION_DEADLINE])
            ->andFilterWhere(['like', 'MUTHONI.SEMESTERS.DESCRIPTION_CODE', $this->DESCRIPTION_CODE])
            ->andFilterWhere(['like', 'MUTHONI.SEMESTERS.SESSION_TYPE', $this->SESSION_TYPE])
            ->andFilterWhere(['like', 'MUTHONI.SEMESTERS.DISPLAY_DATE', $this->DISPLAY_DATE])
            ->andFilterWhere(['like', 'MUTHONI.SEMESTERS.REGISTRATION_DATE', $this->REGISTRATION_DATE])
            ->andFilterWhere(['like', 'MUTHONI.SEMESTERS.SEMESTER_TYPE', $this->SEMESTER_TYPE]);

        return $dataProvider;
    }
}
