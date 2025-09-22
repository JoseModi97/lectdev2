<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

namespace app\models\search;

use app\models\CourseAssignment;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class LecturerReportCourseAssignmentSearch extends CourseAssignment
{
    /**
     * {@inheritdoc}
     */
    public function attributes(): array
    {
        // add related fields to searchable attributes
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
     *
     * @param array $params
     * @param array $additionalParams
     * @return ActiveDataProvider
     */
    public function search(array $params, array $additionalParams = []):ActiveDataProvider
    {
        $payrollNo = $additionalParams['payrollNo'];
        $academicYear = $additionalParams['academicYear'];

        $query = CourseAssignment::find()->alias('CA')
            ->select([
                'CA.LEC_ASSIGNMENT_ID',
                'CA.MRKSHEET_ID',
                'CA.PAYROLL_NO',
                'CA.ASSIGNMENT_DATE'
            ])
            ->where(['CA.PAYROLL_NO' => $payrollNo])
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
            ], true, 'LEFT JOIN')
            ->joinWith(['marksheetDef.semester SM' => function(ActiveQuery $q){
                    $q->select([ 
                        'SM.SEMESTER_ID',
                        'SM.ACADEMIC_YEAR',
                        'SM.LEVEL_OF_STUDY',
                        'SM.SESSION_TYPE',
                        'SM.SEMESTER_CODE',
                        'SM.DEGREE_CODE',
                        'SM.DESCRIPTION_CODE'
                    ]);
                }
            ], true, 'LEFT JOIN');

            // limit the courses to the current academic year
            if(!is_null($academicYear))
                $query->andWhere(['SM.ACADEMIC_YEAR' => $academicYear]);

            $query->joinWith(['marksheetDef.group GR' => function(ActiveQuery $q){
                    $q->select([ 
                        'GR.GROUP_CODE',
                        'GR.GROUP_NAME'
                    ]);
                }
            ], true, 'LEFT JOIN')
            ->joinWith(['marksheetDef.course CS' => function(ActiveQuery $q){
                    $q->select([ 
                        'CS.COURSE_ID',
                        'CS.COURSE_CODE',
                        'CS.COURSE_NAME'
                    ]);
                }
            ], true, 'LEFT JOIN')
            ->joinWith(['marksheetDef.semester.degreeProgramme DEG' => function(ActiveQuery $q){
                    $q->select([ 
                        'DEG.DEGREE_CODE',
                        'DEG.DEGREE_NAME'
                    ]);
                }
            ], true, 'LEFT JOIN')
            ->joinWith(['marksheetDef.semester.semesterDescription DESC' => function(ActiveQuery $q){
                    $q->select([ 
                        'DESC.DESCRIPTION_CODE',
                        'DESC.SEMESTER_DESC'
                    ]);
                }
            ], true, 'LEFT JOIN');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 20,
            ],
        ]);

        $this->load($params);

        if(!$this->validate()) return $dataProvider;

        $query->andFilterWhere([
            'CS.COURSE_CODE' => $this->getAttribute('marksheetDef.course.COURSE_CODE'),
            'CS.COURSE_NAME' => $this->getAttribute('marksheetDef.course.COURSE_NAME')
        ]);

        $query->orderBy([
            'DEG.DEGREE_NAME' => SORT_ASC
        ]);

        return $dataProvider;
    }
}