<?php

/**
 * @author Jack Jmm
 * @email jackmutiso37@gmail.com
 * @create date 12-9-2025 20:50:22 
 * @desc 
 */

namespace app\models\search;

use app\models\CourseAssignment;
use app\models\Semester;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class CourseAssignmentAltSearch extends CourseAssignment
{
    public $academicYear;
    public $programme;

    /**
     * {@inheritdoc}
     * 
     * @return array related fields to searchable attributes
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'marksheetDef.semester.ACADEMIC_YEAR',
            'academicYear',
            'marksheetDef.semester.LEVEL_OF_STUDY',
            'marksheetDef.group.GROUP_NAME',
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
                    'marksheetDef.semester.ACADEMIC_YEAR',
                    'academicYear',
                    'marksheetDef.semester.LEVEL_OF_STUDY',
                    'marksheetDef.group.GROUP_NAME',
                    'programme',
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
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     * @param array $params
     * @param array $additionalParams
     * @return ActiveDataProvider
     */
    public function search(array $params, array $additionalParams): ActiveDataProvider
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
            'sort' => false,
            'pagination' => [
                'pagesize' => 50,
            ],
        ]);

        $this->load($params);

        $query->andFilterWhere(['like', 'SM.LEVEL_OF_STUDY', $this->getAttribute('marksheetDef.semester.LEVEL_OF_STUDY')]);
        $query->andFilterWhere(['like', 'GR.GROUP_NAME', $this->getAttribute('marksheetDef.group.GROUP_NAME')]);


        if (!empty($this->academicYear)) {
            $query->andWhere(['SM.ACADEMIC_YEAR' => $this->academicYear]);
        }
        if (!empty($this->programme)) {
            $query->andWhere(['DEG.DEGREE_CODE' => $this->programme]);
        }

        // dd($query->createCommand()->getRawSql());
        return $dataProvider;
    }
}
