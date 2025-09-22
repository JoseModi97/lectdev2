<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

namespace app\models\search;

use app\models\MarksheetDef;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class DepartmentCoursesSearch extends MarksheetDef
{
    /**
     * add related fields to searchable attributes
     * @return array [type]
     */
    public function attributes(): array
    {
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
    public function search(array $params, array $additionalParams = []): ActiveDataProvider
    {
        $query = MarksheetDef::find()->alias('MD')
            ->select([
                'MD.MRKSHEET_ID',
                'MD.SEMESTER_ID',
                'MD.COURSE_ID',
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
            ], true, 'INNER JOIN')
            ->where(['SM.ACADEMIC_YEAR' => $additionalParams['academicYear']])
            ->joinWith(['semester.degreeProgramme DEG' => function(ActiveQuery $q){
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
                    ]);
                }
            ], true, 'INNER JOIN')
            ->andWhere(['DEPT.DEPT_CODE' => $additionalParams['deptCode']])
            ->asArray();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 20,
            ],
        ]);

        $this->load($params);

        if(!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'CS.COURSE_CODE', $this->getAttribute('course.COURSE_CODE')]);
        $query->andFilterWhere(['like', 'CS.COURSE_NAME', $this->getAttribute('course.COURSE_NAME')]);

        return $dataProvider;
    }
}