<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 13-10-2021 18:24:06 
 * @modify date 13-10-2021 18:24:06 
 * @desc Read created marksheets 
 */

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

use app\models\CourseAssignment;

/**
 * Read created marksheets 
 */
class AllocatedCoursesSearch extends CourseAssignment
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'marksheetDef.semester.degreeProgramme.DEGREE_NAME',
            'marksheetDef.course.COURSE_CODE',
            'marksheetDef.course.COURSE_NAME',
            'marksheetDef.course.dept.DEPT_CODE'
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
                    'marksheetDef.semester.degreeProgramme.DEGREE_NAME',
                    'marksheetDef.course.COURSE_CODE',
                    'marksheetDef.course.COURSE_NAME',
                    'marksheetDef.course.dept.DEPT_CODE'
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
    public function search(array $params, array $additionalParams = []):ActiveDataProvider
    {
        $query = CourseAssignment::find()->alias('CW')
            ->select([
                'CW.LEC_ASSIGNMENT_ID',
                'CW.MRKSHEET_ID',
                'CW.PAYROLL_NO',
                'CW.ASSIGNMENT_DATE'
            ])
            ->joinWith(['marksheetDef MD' => function(ActiveQuery $q){
                $q->select([
                    'MD.MRKSHEET_ID',
                    'MD.SEMESTER_ID',
                    'MD.COURSE_ID',
                    'MD.GROUP_CODE',
                    'MD.PAYROLL_NO'
                ]);
            }],true,'INNER JOIN')
            ->joinWith(['marksheetDef.semester SM' => function(ActiveQuery $q){
                    $q->select([
                        'SM.SEMESTER_ID', 
                        'SM.SEMESTER_CODE',
                        'SM.DEGREE_CODE',
                        'SM.ACADEMIC_YEAR',
                        'SM.LEVEL_OF_STUDY',
                        'SM.DESCRIPTION_CODE'
                    ]);
                }
            ], true, 'INNER JOIN')
            ->joinWith(['marksheetDef.semester.degreeProgramme DEG' => function(ActiveQuery $q){
                    $q->select([
                        'DEG.DEGREE_CODE', 
                        'DEG.DEGREE_NAME',
                        'DEG.FACUL_FAC_CODE'
                    ]);
                }
            ], true, 'INNER JOIN')
            ->joinWith(['marksheetDef.semester.degreeProgramme.faculty FAC' => function(ActiveQuery $q){
                    $q->select([
                        'FAC.FAC_CODE', 
                        'FAC.FACULTY_NAME'
                    ]);
                }
            ], true, 'INNER JOIN')
            ->joinWith(['marksheetDef.course CS' => function(ActiveQuery $q){
                    $q->select([
                        'CS.COURSE_ID', 
                        'CS.COURSE_CODE',
                        'CS.COURSE_NAME',
                        'CS.DEPT_CODE'
                    ]);
                }
            ], true, 'INNER JOIN')
            ->joinWith(['marksheetDef.course.dept DEPT' => function(ActiveQuery $q){
                    $q->select([
                        'DEPT.DEPT_CODE', 
                        'DEPT.DEPT_NAME',
                    ]);
                }
            ], true, 'INNER JOIN')
            ->asArray();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 20,
            ],
        ]);

        $this->load($params);

        if(!$this->validate()){
            return $dataProvider;
        } 
        $query->andFilterWhere(['like', 'CS.COURSE_CODE', $this->getAttribute('marksheetDef.course.COURSE_CODE')]);
        $query->andFilterWhere(['like', 'CS.COURSE_NAME', $this->getAttribute('marksheetDef.course.COURSE_NAME')]);
        $query->andFilterWhere(['like', 'DEG.DEGREE_NAME', $this->getAttribute('marksheetDef.semester.degreeProgramme.DEGREE_NAME')]);
        $query->andFilterWhere(['like', 'DEPT.DEPT_CODE', $this->getAttribute('marksheetDef.course.dept.DEPT_CODE')]);

        $query->orderBy([
            'FAC.FAC_CODE' => SORT_ASC,
            'DEG.DEGREE_CODE' => SORT_ASC,
        ]);

        return $dataProvider; 
    }
}

