<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 15-12-2020 20:50:22 
 * @modify date 15-12-2020 20:50:22 
 * @desc Get all courses for a department allocated for the academic year
 */

namespace app\models\search;

use app\models\MarksheetDef;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class MarksheetDefAllocationSearch extends MarksheetDef
{
    /**
     * add related fields to searchable attributes
     *
     * @return array
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'course.COURSE_CODE',
            'course.COURSE_NAME',
            'semester.SEMESTER_CODE',
            'semester.degreeProgramme.DEGREE_NAME',
            'semester.semesterDescription.SEMESTER_DESC',
            'group.GROUP_NAME',
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
                    'course.COURSE_CODE',
                    'course.COURSE_NAME',
                    'semester.SEMESTER_CODE',
                    'semester.degreeProgramme.DEGREE_NAME',
                    'semester.semesterDescription.SEMESTER_DESC',
                    'group.GROUP_NAME',
                    'semester.LEVEL_OF_STUDY'
                ], 
            'safe'],
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
    public function search(array $params, array $additionalParams = []): ActiveDataProvider
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
                        'SM.DESCRIPTION_CODE',
                        'SM.SEMESTER_TYPE'
                    ]);
                }
            ], true, 'INNER JOIN')
            ->where(['SM.ACADEMIC_YEAR' => $additionalParams['academicYear']]);

        if($additionalParams['semesterType'] === 'other'){
            $query->andWhere(['NOT', ['SM.SEMESTER_TYPE' => 'SUPPLEMENTARY']]);
        }elseif($additionalParams['semesterType'] === 'supplementary'){
            $query->andWhere(['SM.SEMESTER_TYPE' => 'SUPPLEMENTARY']);
        }

        $query->joinWith(['semester.degreeProgramme DEG' => function(ActiveQuery $q){
                    $q->select([
                        'DEG.DEGREE_CODE',
                        'DEG.DEGREE_NAME'
                    ]);
                }
            ], true, 'INNER JOIN')
            ->joinWith(['semester.semesterDescription DESC' => function(ActiveQuery $q){
                    $q->select([
                        'DESC.DESCRIPTION_CODE', 
                        'DESC.SEMESTER_DESC'
                    ]);
                }
            ], true, 'INNER JOIN')
            ->joinWith(['group GR' => function(ActiveQuery $q){
                    $q->select([
                        'GR.GROUP_CODE', 
                        'GR.GROUP_NAME'
                    ]);
                }
            ], true, 'INNER JOIN')
            ->joinWith(['course CS' => function(ActiveQuery $q){
                    $q->select([
                        'CS.COURSE_ID', 
                        'CS.COURSE_CODE',
                        'CS.COURSE_NAME',
                        'CS.DEPT_CODE'
                    ]);
                }
            ], true, 'INNER JOIN')
            ->joinWith(['course.dept DEPT' => function(ActiveQuery $q){
                    $q->select([
                        'DEPT.DEPT_CODE', 
                        'DEPT.DEPT_NAME',
                        'DEPT.FAC_CODE'
                    ]);
                }
            ], true, 'INNER JOIN')
            ->andWhere(['DEPT.DEPT_CODE' => $additionalParams['deptCode']])
//            ->andWhere(['IN', 'DEPT.FAC_CODE', ['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'L', 'M']])
            ->orderBy(['DEG.DEGREE_NAME' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 20,
            ],
        ]);

        $this->load($params);

        $query->andFilterWhere(['like', 'CS.COURSE_CODE', $this->getAttribute('course.COURSE_CODE')]);
        $query->andFilterWhere(['like', 'CS.COURSE_NAME', $this->getAttribute('course.COURSE_NAME')]);
        $query->andFilterWhere(['SM.SEMESTER_CODE' => $this->getAttribute('semester.SEMESTER_CODE')]);
        $query->andFilterWhere(['DEG.DEGREE_NAME' => $this->getAttribute('semester.degreeProgramme.DEGREE_NAME')]);
        $query->andFilterWhere(['DESC.SEMESTER_DESC' => $this->getAttribute('semester.semesterDescription.SEMESTER_DESC')]);
        $query->andFilterWhere(['GR.GROUP_NAME' => $this->getAttribute('group.GROUP_NAME')]);
        $query->andFilterWhere(['SM.LEVEL_OF_STUDY' => $this->getAttribute('semester.LEVEL_OF_STUDY')]);

        return $dataProvider;
    }
}

