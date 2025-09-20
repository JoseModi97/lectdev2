<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @date: 7/26/2024
 * @time: 7:58 PM
 */

namespace app\models\search;

use app\models\CourseAssignment;
use app\models\CourseWorkAssessment;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class MarksSubmissionSearch extends CourseWorkAssessment
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
            'marksheetDef.group.GROUP_NAME'
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
                    'marksheetDef.group.GROUP_NAME'
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
        $payrollNumber = $additionalParams['payrollNo'];
        $academicYear = $additionalParams['academicYear'];

        $query = CourseWorkAssessment::find()->alias('ASS')
            ->select([
                'ASS.ASSESSMENT_ID',
                'ASS.MARKSHEET_ID',
                'ASS.ASSESSMENT_TYPE_ID'
            ])
            ->joinWith(['assessmentType AT' => function (ActiveQuery $q) {
                $q->select([
                    'AT.ASSESSMENT_TYPE_ID',
                    'AT.ASSESSMENT_NAME'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['assignment ASS_MD' => function (ActiveQuery $q) {
                $q->select([
                    'ASS_MD.MRKSHEET_ID',
                    'ASS_MD.PAYROLL_NO'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['marksheetDef MD' => function (ActiveQuery $q) {
                $q->select([
                    'MD.MRKSHEET_ID',
                    'MD.EXAM_ROOM',
                    'MD.SEMESTER_ID',
                    'MD.GROUP_CODE',
                    'MD.COURSE_ID'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['marksheetDef.semester SM' => function(ActiveQuery $q){
                $q->select([
                    'SM.SEMESTER_ID',
                    'SM.ACADEMIC_YEAR',
                    'SM.LEVEL_OF_STUDY',
                    'SM.SEMESTER_CODE',
                    'SM.DEGREE_CODE',
                    'SM.DESCRIPTION_CODE',
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['marksheetDef.semester.degreeProgramme DEG' => function(ActiveQuery $q){
                $q->select([
                    'DEG.DEGREE_CODE',
                    'DEG.DEGREE_NAME'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['marksheetDef.semester.semesterDescription DESC' => function(ActiveQuery $q){
                $q->select([
                    'DESC.DESCRIPTION_CODE',
                    'DESC.SEMESTER_DESC'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['marksheetDef.group GR' => function (ActiveQuery $q) {
                $q->select([
                    'GR.GROUP_CODE',
                    'GR.GROUP_NAME',
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['marksheetDef.course CS' => function (ActiveQuery $q) {
                $q->select([
                    'CS.COURSE_ID',
                    'CS.COURSE_CODE',
                    'CS.COURSE_NAME'
                ]);
            }], true, 'INNER JOIN')
            ->where(['ASS_MD.PAYROLL_NO' => $payrollNumber])
            ->andWhere(['LIKE', 'ASS.MARKSHEET_ID', $academicYear . '%', false])
            ->asArray();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 20
            ],
        ]);

        $query->orderBy([
            'ASS_MD.LEC_ASSIGNMENT_ID' => SORT_DESC,
            'ASS.ASSESSMENT_TYPE_ID' => SORT_ASC
        ]);

        $this->load($params);
        $query->andFilterWhere(['like', 'CS.COURSE_CODE', $this->getAttribute('marksheetDef.course.COURSE_CODE')]);
        $query->andFilterWhere(['like', 'CS.COURSE_NAME', $this->getAttribute('marksheetDef.course.COURSE_NAME')]);
        $query->andFilterWhere(['like', 'GR.GROUP_NAME', $this->getAttribute('marksheetDef.group.GROUP_NAME')]);

        return $dataProvider;
    }
}