<?php

/**
 * @author Jack Jmm
 * @email jackmutiso37@gmail.com
 * @create date 12-9-2025 20:50:22 
 * @desc 
 */

namespace app\models\search;

use app\models\CourseAssignment;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class CourseAssignmentSearch extends CourseAssignment
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
            'marksheetDef.course.COURSE_CODE',
            'marksheetDef.course.COURSE_NAME',
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
                    'marksheetDef.course.COURSE_CODE',
                    'marksheetDef.course.COURSE_NAME',
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
        $query = CourseAssignment::find()->alias('CA')
            ->select([
                'CA.LEC_ASSIGNMENT_ID',
                'CA.MRKSHEET_ID',
                'CA.PAYROLL_NO',
                'CA.ASSIGNMENT_DATE'
            ])
            ->where(['CA.PAYROLL_NO' => $additionalParams['payrollNo']])
            ->joinWith([
                'marksheetDef MD' => function (ActiveQuery $q) {
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
            ->joinWith([
                'marksheetDef.semester SM' => function (ActiveQuery $q) {
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

        $semesterType = $additionalParams['semesterType'];
        if ($semesterType === 'other') {
            $query->andWhere(['NOT', ['SM.SEMESTER_TYPE' => 'SUPPLEMENTARY']]);
        } elseif ($semesterType === 'supplementary') {
            $query->andWhere(['SM.SEMESTER_TYPE' => 'SUPPLEMENTARY']);
        }

        $query->joinWith([
            'marksheetDef.group GR' => function (ActiveQuery $q) {
                $q->select([
                    'GR.GROUP_CODE',
                    'GR.GROUP_NAME'
                ]);
            }
        ], true, 'INNER JOIN')
            ->joinWith([
                'marksheetDef.course CS' => function (ActiveQuery $q) {
                    $q->select([
                        'CS.COURSE_ID',
                        'CS.COURSE_CODE',
                        'CS.COURSE_NAME'
                    ]);
                }
            ], true, 'INNER JOIN')
            ->joinWith([
                'marksheetDef.semester.degreeProgramme DEG' => function (ActiveQuery $q) {
                    $q->select([
                        'DEG.DEGREE_CODE',
                        'DEG.DEGREE_NAME'
                    ]);
                }
            ], true, 'INNER JOIN')
            ->joinWith([
                'marksheetDef.semester.semesterDescription DESC' => function (ActiveQuery $q) {
                    $q->select([
                        'DESC.DESCRIPTION_CODE',
                        'DESC.SEMESTER_DESC'
                    ]);
                }
            ], true, 'INNER JOIN')

            ->orderBy([
                'SM.ACADEMIC_YEAR' => SORT_DESC,
                'SM.SEMESTER_CODE' => SORT_ASC,
                'DEG.DEGREE_NAME' => SORT_ASC,
                'DEG.DEGREE_CODE' => SORT_ASC,
                'SM.LEVEL_OF_STUDY' => SORT_ASC,
                'CS.COURSE_CODE' => SORT_ASC,
            ]);


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 50,
            ],
        ]);

        $this->load($params);

        $query->andFilterWhere(['like', 'CS.COURSE_CODE', $this->getAttribute('marksheetDef.course.COURSE_CODE')]);
        $query->andFilterWhere(['like', 'CS.COURSE_NAME', $this->getAttribute('marksheetDef.course.COURSE_NAME')]);
        $query->andFilterWhere(['like', 'SM.LEVEL_OF_STUDY', $this->getAttribute('marksheetDef.semester.LEVEL_OF_STUDY')]);
        $query->andFilterWhere(['like', 'GR.GROUP_NAME', $this->getAttribute('marksheetDef.group.GROUP_NAME')]);


        if (!empty($this->academicYear)) {
            $query->andWhere(['SM.ACADEMIC_YEAR' => $this->academicYear]);
        }
        if (!empty($this->programme)) {
            $query->andWhere(['DEG.DEGREE_CODE' => $this->programme]);
        }
        return $dataProvider;
    }
}
