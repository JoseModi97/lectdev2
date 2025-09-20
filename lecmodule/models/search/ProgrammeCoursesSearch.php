<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 27-01-2021 10:09:29 
 * @modify date 27-01-2021 10:09:29 
 * @desc [description]
 */

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

use app\models\MarksheetDef;

class ProgrammeCoursesSearch extends MarksheetDef
{

    public function attributes(): array
    {
        // add related fields to searchable attributes
        return array_merge(parent::attributes(), [
            'course.COURSE_CODE',
            'course.COURSE_NAME',
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
    public function search(array $params, array $additionalParams = []):ActiveDataProvider
    {
        $degreeCode = $additionalParams['degreeCode'];
        $academicYear = $additionalParams['academicYear'];
        $deptCode = $additionalParams['deptCode'];

        $query = MarksheetDef::find()->alias('MD')
            ->select([
                'MD.MRKSHEET_ID',
                'MD.SEMESTER_ID',
                'MD.COURSE_ID',
                'MD.GROUP_CODE',
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
                }
            ], true, 'LEFT JOIN')
            ->where(['SM.ACADEMIC_YEAR' => $academicYear])
            ->joinWith(['semester.degreeProgramme DEG' => function(ActiveQuery $q){
                    $q->select([
                        'DEG.DEGREE_CODE', 
                        'DEG.DEGREE_NAME'
                    ]);
                }
            ], true, 'LEFT JOIN')
            ->andWhere(['DEG.DEGREE_CODE' => $degreeCode])
            ->joinWith(['semester.semesterDescription DESC' => function(ActiveQuery $q){
                    $q->select([
                        'DESC.DESCRIPTION_CODE', 
                        'DESC.SEMESTER_DESC'
                    ]);
                }
            ], true, 'LEFT JOIN')
            ->joinWith(['group GR' => function(ActiveQuery $q){
                    $q->select([
                        'GR.GROUP_CODE', 
                        'GR.GROUP_NAME'
                    ]);
                }
            ], true, 'LEFT JOIN')
            ->joinWith(['course CS' => function(ActiveQuery $q){
                    $q->select([
                        'CS.COURSE_ID', 
                        'CS.COURSE_CODE',
                        'CS.COURSE_NAME',
                        'CS.DEPT_CODE'
                    ]);
                }
            ], true, 'LEFT JOIN')
            ->joinWith(['course.dept DEPT' => function(ActiveQuery $q){
                    $q->select([
                        'DEPT.DEPT_CODE', 
                        'DEPT.DEPT_NAME',
                    ]);
                }
            ], true, 'LEFT JOIN')
            ->andWhere(['DEPT.DEPT_CODE' => $deptCode]);

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'sort' => false,
                'pagination' => [
                    'pagesize' => 20,
                ],
            ]);

            $this->load($params);

            if(!$this->validate()) return $dataProvider;

            $query->andFilterWhere(['like', 'CS.COURSE_CODE', $this->getAttribute('course.COURSE_CODE')]);
            $query->andFilterWhere(['like', 'CS.COURSE_NAME', $this->getAttribute('course.COURSE_NAME')]);

            $query->orderBy([
                'SM.LEVEL_OF_STUDY' => SORT_ASC
            ]);
    
        return $dataProvider;
    }
}
