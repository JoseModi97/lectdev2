<?php

namespace app\models\search;

use app\models\MarksheetDef;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class ProgrammeCourseWorkSearch extends MarksheetDef
{
    /**
     * {@inheritdoc}
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'semester.degreeProgramme.DEGREE_NAME',
            'course.COURSE_CODE',
            'course.COURSE_NAME',
            'group.GROUP_NAME',
            'semester.SEMESTER_CODE',
            'semester.LEVEL_OF_STUDY'
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
                    'semester.degreeProgramme.DEGREE_NAME',
                    'course.COURSE_CODE',
                    'course.COURSE_NAME',
                    'group.GROUP_NAME',
                    'semester.SEMESTER_CODE',
                    'semester.LEVEL_OF_STUDY'
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
     * @param $params
     * @param $additionalParams
     * @return ActiveDataProvider
     */
    public function search($params, $additionalParams): ActiveDataProvider
    {
        $query = MarksheetDef::find()->alias('MD')
            ->select([
                'MD.MRKSHEET_ID',
                'MD.SEMESTER_ID',
                'MD.COURSE_ID',
                'MD.GROUP_CODE',
                'MD.PAYROLL_NO'
            ])
            ->joinWith(['semester SM' => function(ActiveQuery $q){
                $q->select([
                    'SM.SEMESTER_ID',
                    'SM.SEMESTER_CODE',
                    'SM.DEGREE_CODE',
                    'SM.ACADEMIC_YEAR',
                    'SM.LEVEL_OF_STUDY',
                    'SM.DESCRIPTION_CODE'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['semester.degreeProgramme DEG' => function(ActiveQuery $q){
                $q->select([
                    'DEG.DEGREE_CODE',
                    'DEG.DEGREE_NAME',
                    'DEG.FACUL_FAC_CODE'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['semester.degreeProgramme.faculty FAC' => function(ActiveQuery $q){
                $q->select([
                    'FAC.FAC_CODE',
                    'FAC.FACULTY_NAME'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['course CS' => function(ActiveQuery $q){
                $q->select([
                    'CS.COURSE_ID',
                    'CS.COURSE_CODE',
                    'CS.COURSE_NAME',
                    'CS.DEPT_CODE'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['course.dept DEPT' => function(ActiveQuery $q){
                $q->select([
                    'DEPT.DEPT_CODE',
                    'DEPT.DEPT_NAME',
                ]);
            }
            ], true, 'INNER JOIN')
            ->joinWith(['group GR' => function(ActiveQuery $q){
                $q->select([
                    'GR.GROUP_CODE',
                    'GR.GROUP_NAME'
                ]);
            }], true, 'INNER JOIN')
            ->where([
                'DEPT.DEPT_CODE' => $additionalParams['deptCode'],
                'SM.ACADEMIC_YEAR' => $additionalParams['academicYear']
            ])
            ->orderBy([
                'DEG.DEGREE_CODE' => SORT_ASC,
                'SM.SEMESTER_CODE' => SORT_ASC,
                'SM.LEVEL_OF_STUDY' => SORT_ASC
            ])
            ->asArray();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 50,
            ],
        ]);

        $this->load($params);

        $query->andFilterWhere(['like', 'CS.COURSE_CODE', $this->getAttribute('course.COURSE_CODE')]);
        $query->andFilterWhere(['like', 'CS.COURSE_NAME', $this->getAttribute('course.COURSE_NAME')]);
        $query->andFilterWhere(['like', 'DEG.DEGREE_NAME',
            $this->getAttribute('semester.degreeProgramme.DEGREE_NAME')]);
        $query->andFilterWhere(['like', 'GR.GROUP_NAME', $this->getAttribute('group.GROUP_NAME')]);
        $query->andFilterWhere(['SM.SEMESTER_CODE' => $this->getAttribute('semester.SEMESTER_CODE')]);
        $query->andFilterWhere(['SM.LEVEL_OF_STUDY' => $this->getAttribute('semester.LEVEL_OF_STUDY')]);

        return $dataProvider;
    }
}