<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @date: 7/30/2024
 * @time: 2:47 PM
 */

namespace app\models\search;

use app\models\MarksheetDef;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class ReceivedMissingMarksSearch extends MarksheetDef
{
    /**
     * {@inheritdoc}
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'course.COURSE_CODE',
            'course.COURSE_NAME',
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
                    'PAYROLL_NO',
                    'course.COURSE_CODE',
                    'course.COURSE_NAME',
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


    public function search(array $params): ActiveDataProvider
    {
        $filter = $params['filter'];

        $semesterId = $filter->academicYear . '_' . $filter->degreeCode . '_' . $filter->levelOfStudy
            . '_' . $filter->semester . '_' . $filter->group;
        
        $query = MarksheetDef::find()->alias('MD')
            ->select([
                'MD.MRKSHEET_ID',
                'MD.SEMESTER_ID',
                'MD.COURSE_ID',
                'MD.PAYROLL_NO',
                'MD.EXAM_DATE',
                'MD.GROUP_CODE',
            ])
            ->joinWith(['course CS' => function (ActiveQuery $q) {
                $q->select([
                    'CS.COURSE_ID',
                    'CS.COURSE_CODE',
                    'CS.COURSE_NAME'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['semester SM' => function (ActiveQuery $q) {
                $q->select([
                    'SM.SEMESTER_ID',
                    'SM.SEMESTER_CODE',
                    'SM.DEGREE_CODE',
                    'SM.ACADEMIC_YEAR',
                    'SM.DESCRIPTION_CODE',
                    'SM.LEVEL_OF_STUDY'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['semester.degreeProgramme DEG' => function (ActiveQuery $q) {
                $q->select([
                    'DEG.DEGREE_CODE',
                    'DEG.DEGREE_NAME'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['group GR' => function (ActiveQuery $q) {
                $q->select([
                    'GR.GROUP_CODE',
                    'GR.GROUP_NAME',
                ]);
            }], true, 'INNER JOIN')
            ->where(['SM.SEMESTER_ID' => $semesterId])
            ->asArray();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 50,
            ],
        ]);

        $this->load($params['queryParams']);

        $query->andFilterWhere(['like', 'MD.PAYROLL_NO', $this->PAYROLL_NO]);
        $query->andFilterWhere(['like', 'CS.COURSE_CODE', $this->getAttribute('course.COURSE_CODE')]);
        $query->andFilterWhere(['like', 'CS.COURSE_NAME', $this->getAttribute('course.COURSE_NAME')]);
        $query->andFilterWhere(['like', 'GR.GROUP_NAME', $this->getAttribute('group.GROUP_NAME')]);
        $query->andFilterWhere(['like', 'SM.LEVEL_OF_STUDY', $this->getAttribute('semester.LEVEL_OF_STUDY')]);

        return $dataProvider;
    }
}