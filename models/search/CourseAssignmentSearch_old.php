<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 20-01-2021 09:35:23 
 * @modify date 20-01-2021 09:35:23 
 * @desc [description]
 */

namespace app\models\search;

use app\models\CourseAssignment;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class CourseAssignmentSearch extends CourseAssignment
{
    /**
     * {@inheritdoc}
     * 
     * @return array related fields to searchable attributes
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [ 
            'marksheetDef.course.COURSE_CODE',
            'marksheetDef.course.COURSE_NAME',
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
                    'marksheetDef.course.COURSE_CODE',
                    'marksheetDef.course.COURSE_NAME',
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
     * @param array $params
     * @param array $additionalParams
     * @return ActiveDataProvider
     */
    public function search(array $params, array $additionalParams): ActiveDataProvider
    {
        $query = CourseAssignment::find()->alias('CA')
            ->select([
                'CA.LEC_ASSIGNMENT_ID',
                'CA.MRKSHEET_ID',
                'CA.PAYROLL_NO',
                'CA.ASSIGNMENT_DATE'
            ])
            ->where(['CA.PAYROLL_NO' => $additionalParams['payrollNo']])
            ->joinWith(['marksheetDef MD' => function(ActiveQuery $q){ 
                    $q->select([ 
                        'MD.MRKSHEET_ID',
                        'MD.EXAM_ROOM',
                        'MD.SEMESTER_ID',
                        'MD.GROUP_CODE',
                        'MD.COURSE_ID',
                        'MD.FROM_HR',
                        'MD.FROM_MIN',
                        'MD.TO_HR',
                        'MD.TO_MIN'
                    ]);
                }
            ], true, 'INNER JOIN')
            ->joinWith(['marksheetDef.semester SM' => function(ActiveQuery $q){
                    $q->select([ 
                        'SM.SEMESTER_ID',
                        'SM.ACADEMIC_YEAR',
                        'SM.LEVEL_OF_STUDY',
                        'SM.SESSION_TYPE',
                        'SM.SEMESTER_CODE',
                        'SM.DEGREE_CODE',
                        'SM.DESCRIPTION_CODE',
                        'SM.SEMESTER_TYPE'
                    ]);
                }
            ], true, 'INNER JOIN');

        /**
         * @todo On course allocation, also indicate in which academic year was the course given.
         * This will work to avoid just pulling all courses for each academic years.
         * We only want courses that have been assigned in the current academic year, even if they belong to other
         * academic years.
         */
//        $academicYear = $additionalParams['academicYear'];
//        if(!is_null($academicYear)){
//            $query->andWhere(['SM.ACADEMIC_YEAR' => $academicYear]);
//        }

        $semesterType = $additionalParams['semesterType'];
        if($semesterType === 'other'){
            $query->andWhere(['NOT', ['SM.SEMESTER_TYPE' => 'SUPPLEMENTARY']]);
        }elseif($semesterType === 'supplementary'){
            $query->andWhere(['SM.SEMESTER_TYPE' => 'SUPPLEMENTARY']);
        }

        $query->joinWith(['marksheetDef.group GR' => function(ActiveQuery $q){
                    $q->select([ 
                        'GR.GROUP_CODE',
                        'GR.GROUP_NAME'
                    ]);
                }
            ], true, 'INNER JOIN')
            ->joinWith(['marksheetDef.course CS' => function(ActiveQuery $q){
                    $q->select([ 
                        'CS.COURSE_ID',
                        'CS.COURSE_CODE',
                        'CS.COURSE_NAME'
                    ]);
                }
            ], true, 'INNER JOIN')
            ->joinWith(['marksheetDef.semester.degreeProgramme DEG' => function(ActiveQuery $q){
                    $q->select([ 
                        'DEG.DEGREE_CODE',
                        'DEG.DEGREE_NAME'
                    ]);
                }
            ], true, 'INNER JOIN')
            ->joinWith(['marksheetDef.semester.semesterDescription DESC' => function(ActiveQuery $q){
                    $q->select([ 
                        'DESC.DESCRIPTION_CODE',
                        'DESC.SEMESTER_DESC'
                    ]);
                }
            ], true, 'INNER JOIN')
            ->orderBy(['SM.ACADEMIC_YEAR' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 20,
            ],
        ]);

        $this->load($params);
        $query->andFilterWhere(['like', 'CS.COURSE_CODE', $this->getAttribute('marksheetDef.course.COURSE_CODE')]);
        $query->andFilterWhere(['like', 'CS.COURSE_NAME', $this->getAttribute('marksheetDef.course.COURSE_NAME')]);

        return $dataProvider;
    }
}