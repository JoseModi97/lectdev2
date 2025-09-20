<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 08-03-2021 12:04:20 
 * @modify date 08-03-2021 12:04:20 
 * @desc [description]
 */

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

use app\models\StudentCoursework;

/**
 * Get all submitted exam marks for a marksheet belonging to a department/facluty
 */
class SubmittedMarksSearch extends StudentCoursework{

  
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [];
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
        $deptCode = $additionalParams['deptCode'];
        $facCode = $additionalParams['facCode'];
        $marksheetId = $additionalParams['marksheetId'];
        $level = $additionalParams['level'];

        $query = StudentCoursework::find()->alias('SC')
            ->select([
                'SC.COURSE_WORK_ID',
                'SC.ASSESSMENT_ID',
                'SC.LECTURER_APPROVAL_STATUS',
                'SC.HOD_APPROVAL_STATUS',
                'SC.DATE_ENTERED',
                'SC.USER_ID',
                'SC.REGISTRATION_NUMBER',
                'SC.MARK',
                'SC.REMARKS'
            ])
            ->joinWith(['assessment AS' =>function(ActiveQuery $q){
                    $q->select([
                        'AS.ASSESSMENT_ID',
                        'AS.ASSESSMENT_TYPE_ID',
                        'AS.MARKSHEET_ID'
                    ]);   
                }
            ], true, 'LEFT JOIN')
            ->joinWith(['assessment.assessmentType AT' => function(ActiveQuery $q){
                    $q->select([
                        'AT.ASSESSMENT_TYPE_ID',
                        'AT.ASSESSMENT_NAME'
                    ]);
                }
            ], true, 'LEFT JOIN')
            ->joinWith(['assessment.marksheetDef MD' => function(ActiveQuery $q){
                    $q->select([
                        'MD.MRKSHEET_ID',
                        'MD.COURSE_ID'
                    ]);
                }
            ], true, 'LEFT JOIN')
            ->joinWith(['assessment.marksheetDef.course CS' => function(ActiveQuery $q){
                    $q->select([
                        'CS.COURSE_ID',
                        'CS.DEPT_CODE'
                    ]);
                }
            ], true, 'LEFT JOIN')
            ->joinWith(['assessment.marksheetDef.course.dept DEPT' => function(ActiveQuery $q){
                    $q->select([
                        'DEPT.DEPT_CODE',
                        'DEPT.FAC_CODE'
                    ]);
                }
            ], true, 'LEFT JOIN')
            ->where([
                'AS.MARKSHEET_ID' => $marksheetId,
                'AT.ASSESSMENT_NAME' => 'EXAM',
            ]);
            if($level === 'hod'){
                $query->andWhere([
                    'SC.LECTURER_APPROVAL_STATUS' => 'APPROVED',
                    'DEPT.DEPT_CODE' => $deptCode
                ]);
            }
            elseif($level === 'dean'){
                $query->andWhere([
                    'SC.HOD_APPROVAL_STATUS' => 'APPROVED',
                    'DEPT.FAC_CODE' => $facCode
                ]);
            }

            $query->asArray();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pagesize' => 20,
            ],
        ]);

        $this->load($params);

        if(!$this->validate()) return $dataProvider;

        $query->orderBy([
            'SC.COURSE_WORK_ID' => SORT_DESC
        ]);

        return $dataProvider;
    }
}
