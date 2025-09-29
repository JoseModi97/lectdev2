<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Semester;
use Yii;

/**
 * SemesterSearch represents the model behind the search form of `app\models\Semester`.
 */

use app\models\MarksheetDef;
use app\models\SemesterDescription;

class SemesterSearch extends Semester
{
    public $purpose; // New property
    public $courseCode;
    public $courseName;
    public $SEMESTER_CODE_DESC;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['SEMESTER_ID', 'ACADEMIC_YEAR', 'DEGREE_CODE', 'INTAKE_CODE', 'START_DATE', 'END_DATE', 'FIRST_SEMESTER', 'SEMESTER_NAME', 'CLOSING_DATE', 'ADMIN_USER', 'GROUP_CODE', 'REGISTRATION_DEADLINE', 'DESCRIPTION_CODE', 'SESSION_TYPE', 'DISPLAY_DATE', 'REGISTRATION_DATE', 'SEMESTER_TYPE', 'purpose', 'courseCode', 'courseName'], 'safe'],
            [['LEVEL_OF_STUDY', 'SEMESTER_CODE'], 'integer'],
            [['SEMESTER_CODE_DESC'], 'safe'],
            // [['purpose', 'ACADEMIC_YEAR', 'DEGREE_CODE', 'SEMESTER_CODE', 'LEVEL_OF_STUDY'], 'required'],
            [['ACADEMIC_YEAR', 'DEGREE_CODE', 'SEMESTER_CODE'], 'required'],
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
        $this->load($params, $formName);

        $query = MarksheetDef::find();
        $query->joinWith(['semester', 'course']);

        $query->andFilterWhere([
            'MUTHONI.SEMESTERS.ACADEMIC_YEAR' => $this->ACADEMIC_YEAR,
            'MUTHONI.SEMESTERS.LEVEL_OF_STUDY' => $this->LEVEL_OF_STUDY,
            'MUTHONI.SEMESTERS.DEGREE_CODE' => $this->DEGREE_CODE,
        ])
            ->orderBy([
                'MUTHONI.SEMESTERS.ACADEMIC_YEAR' => SORT_ASC,
                'MUTHONI.SEMESTERS.LEVEL_OF_STUDY' => SORT_ASC,
                'MUTHONI.SEMESTERS.SEMESTER_CODE' => SORT_ASC,
            ]);



        // grid filtering conditions
        $query->andFilterWhere([
            // 'MUTHONI.SEMESTERS.DEGREE_CODE' => $this->DEGREE_CODE,
            // 'MUTHONI.SEMESTERS.ACADEMIC_YEAR' => $this->ACADEMIC_YEAR,
            'MUTHONI.SEMESTERS.SEMESTER_CODE' => $this->SEMESTER_CODE,
            'MUTHONI.SEMESTERS.GROUP_CODE' => $this->GROUP_CODE,
            'MUTHONI.SEMESTERS.DESCRIPTION_CODE' => $this->DESCRIPTION_CODE,
            'MUTHONI.SEMESTERS.SEMESTER_TYPE' => $this->SEMESTER_TYPE,
        ]);

        $query->andFilterWhere(['like', 'MUTHONI.COURSES.COURSE_CODE', $this->courseCode])
            ->andFilterWhere(['like', 'MUTHONI.COURSES.COURSE_NAME', $this->courseName]);



        if (empty($params['DEGREE_CODE']) || empty($params['SEMESTER_CODE']) || empty($params['LEVEL_OF_STUDY'])) {
            $dataProvider = new ActiveDataProvider([
                'query' => $query->limit(100),
                'pagination' => false,
            ]);
        } elseif (!empty($params)) {
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
            ]);
        }

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }







        // if ($params['filtersFor'] === 'nonSuppCourses') {
        //     $query->andWhere(['NOT', ['MUTHONI.SEMESTERS.SEMESTER_TYPE' => 'SUPPLEMENTARY']]);
        // } elseif ($params['filtersFor'] === 'suppCourses') {
        //     $query->andWhere(['MUTHONI.SEMESTERS.SEMESTER_TYPE' => 'SUPPLEMENTARY']);
        // }
        return $dataProvider;
    }


    public function year($params, $formName = null)
    {
        $academicYear =  [
            '2024/2025',
            '2023/2024',
            '2022/2023',
            '2021/2022',
            '2020/2021',
            '2019/2020'
        ];
        $query = Semester::find()
            ->select([
                'MUTHONI.SEMESTERS.ACADEMIC_YEAR',
            ])
            ->distinct()
            ->where(['MUTHONI.SEMESTERS.ACADEMIC_YEAR' => $academicYear]);
        if (!empty(Yii::$app->request->get('SemesterSearch')['ACADEMIC_YEAR'])) {
            $query->andWhere([
                'MUTHONI.SEMESTERS.ACADEMIC_YEAR' => Yii::$app->request->get('SemesterSearch')['ACADEMIC_YEAR'] ?? '',
            ]);
        }
        $query->orderBy([
            'MUTHONI.SEMESTERS.ACADEMIC_YEAR' => SORT_DESC,
        ]);

        // if (->purpose === 'nonSuppCourses') {
        //     $query->andWhere(['NOT', ['MUTHONI.SEMESTERS.SEMESTER_TYPE' => 'SUPPLEMENTARY']]);
        // } elseif ($courseFilter->purpose === 'suppCourses') {
        //     $query->andWhere(['MUTHONI.SEMESTERS.SEMESTER_TYPE' => 'SUPPLEMENTARY']);
        // }


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        return $dataProvider;
    }
}
