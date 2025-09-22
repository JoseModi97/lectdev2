<?php

namespace app\models\search;

use yii\db\Query;
use yii\base\Model;
use app\models\Marksheet;
use app\models\LecLateMark;
use yii\data\ActiveDataProvider;

class LateMarksStudentMarksheetSearch extends Marksheet
{
    public $HOD_APPROVAL;
    public $DEAN_APPROVAL;
    public $OTHER_NAMES;

    // public function rules()
    // {
    //     return [
    //         [['REGISTRATION_NUMBER', 'EXAM_TYPE', 'GRADE', 'HOD_APPROVAL', 'DEAN_APPROVAL', 'MRKSHEET_ID', 'OTHER_NAMES'], 'safe'],
    //         [['COURSE_MARKS', 'EXAM_MARKS', 'FINAL_MARKS'], 'integer'],
    //     ];
    // }

    /**
     * Build the base query for all searches.
     */
    protected function buildBaseQuery()
    {
        return LecLateMark::find()
            ->select([
                'MUTHONI.LEC_LATE_MARKS.LEC_LATE_MARKS_ID',
                'MUTHONI.LEC_LATE_MARKS.REGISTRATION_NUMBER as REGISTRATION_NUMBER',
                'MUTHONI.LEC_LATE_MARKS.MRKSHEET_ID',
                'MUTHONI.LEC_LATE_MARKS.EXAM_TYPE',
                'MUTHONI.LEC_LATE_MARKS.COURSE_MARKS',
                'MUTHONI.LEC_LATE_MARKS.EXAM_MARKS',
                'MUTHONI.LEC_LATE_MARKS.FINAL_MARKS',
                'MUTHONI.LEC_LATE_MARKS.GRADE',
                'MUTHONI.LEC_LATE_MARKS.ENTRY_DATE',
                'MUTHONI.LEC_LATE_MARKS.REMARKS',
                'MUTHONI.LEC_LATE_MARKS.LECTURER_APPROVAL',
                'MUTHONI.LEC_LATE_MARKS.HOD_APPROVAL',
                'MUTHONI.LEC_LATE_MARKS.DEAN_APPROVAL',
                'MUTHONI.LEC_LATE_MARKS.RECORD_STATUS',

                'MUTHONI.UON_STUDENTS.SURNAME AS STUDENT_SURNAME',
                'MUTHONI.UON_STUDENTS.OTHER_NAMES AS STUDENT_OTHER_NAMES',
                'MUTHONI.UON_STUDENTS.DEGREE_CODE',
                'MUTHONI.UON_STUDENTS.EMAIL',

                'MUTHONI.MARKSHEET_DEF.COURSE_ID AS MARKSHEET_COURSE_ID',

                'MUTHONI.COURSES.COURSE_CODE AS COURSE_CODE',
                'MUTHONI.COURSES.COURSE_NAME AS COURSE_NAME',
                'MUTHONI.LEC_LATE_MARKS.PUBLISH_STATUS',
            ])
            ->innerJoin('MUTHONI.UON_STUDENTS', 'MUTHONI.LEC_LATE_MARKS.REGISTRATION_NUMBER = MUTHONI.UON_STUDENTS.REGISTRATION_NUMBER')
            ->innerJoin('MUTHONI.MARKSHEET_DEF', 'MUTHONI.LEC_LATE_MARKS.MRKSHEET_ID = MUTHONI.MARKSHEET_DEF.MRKSHEET_ID')

            //
            ->innerJoin('MUTHONI.MARKSHEETS', 'MUTHONI.MARKSHEETS.MRKSHEET_ID = MUTHONI.LEC_LATE_MARKS.MRKSHEET_ID')

            ->innerJoin('MUTHONI.COURSES', 'MUTHONI.COURSES.COURSE_ID = MUTHONI.MARKSHEET_DEF.COURSE_ID')
            ->asArray();
    }

    /**
     * Apply common filters to the query.
     */
    protected function applyCommonFilters($query, $params)
    {
        $this->load($params);

        if (!$this->validate()) {
            return $query;
        }

        $query->andFilterWhere(['like', 'MUTHONI.LEC_LATE_MARKS.REGISTRATION_NUMBER', $this->REGISTRATION_NUMBER])
            ->andFilterWhere(['like', 'MUTHONI.LEC_LATE_MARKS.EXAM_TYPE', $this->EXAM_TYPE])
            ->andFilterWhere(['like', 'MUTHONI.LEC_LATE_MARKS.GRADE', $this->GRADE])
            ->andFilterWhere(['MUTHONI.LEC_LATE_MARKS.COURSE_MARKS' => $this->COURSE_MARKS])
            ->andFilterWhere(['MUTHONI.LEC_LATE_MARKS.EXAM_MARKS' => $this->EXAM_MARKS])
            ->andFilterWhere(['MUTHONI.LEC_LATE_MARKS.FINAL_MARKS' => $this->FINAL_MARKS])
            ->andFilterWhere(['like', 'MUTHONI.UON_STUDENTS.OTHER_NAMES', strtoupper("%{$this->OTHER_NAMES}%"), false]);

        // Filter by HOD_APPROVAL
        if (strtolower($this->HOD_APPROVAL) == 'disapproved') {
            $query->andWhere(['HOD_APPROVAL' => 2]);
        } elseif (strtolower($this->HOD_APPROVAL) == 'approved') {
            $query->andFilterWhere(['HOD_APPROVAL' => 1]);
        } elseif (strtolower($this->HOD_APPROVAL) == 'pending') {
            $query->andFilterWhere(['HOD_APPROVAL' => 0]);
        }

        // Filter by DEAN_APPROVAL
        if (strtolower($this->DEAN_APPROVAL) == 'disapproved') {
            $query->andWhere(['DEAN_APPROVAL' => 2]);
        } elseif (strtolower($this->DEAN_APPROVAL) == 'approved') {
            $query->andFilterWhere(['DEAN_APPROVAL' => 1]);
        } elseif (strtolower($this->DEAN_APPROVAL) == 'pending') {
            $query->andFilterWhere(['DEAN_APPROVAL' => 0]);
        }

        return $query;
    }

    /**
     * Create a data provider with the given query.
     */
    protected function createDataProvider($query)
    {
        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => false
        ]);
    }

    /**
     * HOD: Search for pending missing marks.
     */
    public function searchPendingMissingMarksHod($params): ActiveDataProvider
    {
        $query = $this->buildBaseQuery()
            ->where(['MUTHONI.LEC_LATE_MARKS.RECORD_VALIDITY' => 'VALID'])
            ->andWhere(['MUTHONI.LEC_LATE_MARKS.LECTURER_APPROVAL' => 1])
            ->andWhere(['MUTHONI.LEC_LATE_MARKS.HOD_APPROVAL' => 0]);

        $this->applyCommonFilters($query, $params);
        return $this->createDataProvider($query);
    }

    /**
     * HOD: Search for approved missing marks.
     */
    public function searchApprovedMissingMarksHod($params): ActiveDataProvider
    {
        $query = $this->buildBaseQuery()
            //->where(['MUTHONI.LEC_LATE_MARKS.RECORD_VALIDITY' => 'VALID'])
            ->where(['MUTHONI.LEC_LATE_MARKS.LECTURER_APPROVAL' => 1])
            ->andWhere(['MUTHONI.LEC_LATE_MARKS.HOD_APPROVAL' => 1]);

        $this->applyCommonFilters($query, $params);
        return $this->createDataProvider($query);
    }

    /**
     * HOD: Search for disapproved missing marks.
     */
    public function searchDisapprovedMissingMarksHod($params): ActiveDataProvider
    {
        $query = $this->buildBaseQuery()
            ->where(['MUTHONI.LEC_LATE_MARKS.RECORD_VALIDITY' => 'REJECTED'])
            ->andWhere(['MUTHONI.LEC_LATE_MARKS.LECTURER_APPROVAL' => 1])
            ->andWhere(['MUTHONI.LEC_LATE_MARKS.HOD_APPROVAL' => 2]);

        $this->applyCommonFilters($query, $params);
        return $this->createDataProvider($query);
    }

    /**
     * DEAN: Search for pending records.
     */
    public function searchDeanRecord($params): ActiveDataProvider
    {
        $query = $this->buildBaseQuery()
            ->where(['MUTHONI.LEC_LATE_MARKS.RECORD_VALIDITY' => 'VALID'])
            ->andWhere(['MUTHONI.LEC_LATE_MARKS.HOD_APPROVAL' => 1])
            ->andWhere(['MUTHONI.LEC_LATE_MARKS.DEAN_APPROVAL' => 0]);

        $this->applyCommonFilters($query, $params);
        return $this->createDataProvider($query);
    }
    public function searchPublishedRecord($params): ActiveDataProvider
    {
        $query = $this->buildBaseQuery()
            ->where(['MUTHONI.LEC_LATE_MARKS.RECORD_VALIDITY' => 'VALID'])
            ->andWhere(['MUTHONI.LEC_LATE_MARKS.HOD_APPROVAL' => 1])
            ->andWhere(['MUTHONI.LEC_LATE_MARKS.DEAN_APPROVAL' => 1])
            ->andWhere(['MUTHONI.LEC_LATE_MARKS.PUBLISH_STATUS' => 1]);

        $this->applyCommonFilters($query, $params);
        return $this->createDataProvider($query);
    }

    /**
     * DEAN: Search for approved missing marks.
     */
    public function searchApprovedMissingMarksDean($params): ActiveDataProvider
    {
        $query = $this->buildBaseQuery()
            ->where(['MUTHONI.LEC_LATE_MARKS.RECORD_VALIDITY' => 'VALID'])
            ->andWhere(['MUTHONI.LEC_LATE_MARKS.HOD_APPROVAL' => 1])
            ->andWhere(['MUTHONI.LEC_LATE_MARKS.DEAN_APPROVAL' => 1]);

        $this->applyCommonFilters($query, $params);
        return $this->createDataProvider($query);
    }
    /**
     * DEAN: Search for approved missing marks.
     */
    public function searchPublishMarks($params): ActiveDataProvider
    {
        $query = $this->buildBaseQuery()
            ->where(['MUTHONI.LEC_LATE_MARKS.RECORD_VALIDITY' => 'VALID'])
            //->andWhere(['MUTHONI.LEC_LATE_MARKS.HOD_APPROVAL' => 1])
            ->andWhere(['MUTHONI.LEC_LATE_MARKS.DEAN_APPROVAL' => 1])
            ->andWhere([
                'or',
                ['!=', 'MUTHONI.LEC_LATE_MARKS.PUBLISH_STATUS', 1],
                ['MUTHONI.LEC_LATE_MARKS.PUBLISH_STATUS' => null],
            ]);


        $this->applyCommonFilters($query, $params);
        return $this->createDataProvider($query);
    }

    /**
     * DEAN: Search for disapproved missing marks.
     */
    public function searchDisapprovedMissingMarksDean($params): ActiveDataProvider
    {
        $query = $this->buildBaseQuery()
            ->where(['MUTHONI.LEC_LATE_MARKS.RECORD_VALIDITY' => 'REJECTED'])
            ->andWhere(['MUTHONI.LEC_LATE_MARKS.HOD_APPROVAL' => 1])
            ->andWhere(['MUTHONI.LEC_LATE_MARKS.DEAN_APPROVAL' => 2]);

        $this->applyCommonFilters($query, $params);
        return $this->createDataProvider($query);
    }
}
