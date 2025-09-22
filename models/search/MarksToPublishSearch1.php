<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @date: 8/6/2025
 * @time: 11:55 AM
 */

namespace app\models\search;

use app\models\CourseWorkAssessment;
use app\models\StudentBalanceAll;
use app\models\TempMarksheet;
use app\models\UonStudent;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

class MarksToPublishSearch extends TempMarksheet
{
    public function attributes(): array
    {
        return [];
    }

    /**
     * @return array[]
     */
    public function rules(): array
    {
        return [];
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = (new Query())
            ->select([
                'LMK.MRKSHEET_ID',
                'LMK.REGISTRATION_NUMBER',
                'LMK.COURSE_CODE',
                'LMK.EXAM_TYPE',
                'LMK.COURSE_MARKS',
                'LMK.EXAM_MARKS',
                'LMK.FINAL_MARKS',
                'LMK.GRADE',
                'LMK.REMARKS',
                'LMK.PUBLISH_STATUS',
                'LMK.MARKS_COMPLETE',
                'BAL.BALANCE',
                'ST.SURNAME',
                'ST.OTHER_NAMES',
            ])
            ->from(TempMarksheet::tableName() . ' LMK')
            ->innerJoin('MUTHONI.UON_STUDENTS_BALANCE_ALL_UFB BAL', 'BAL.REGISTRATION_NUMBER=LMK.REGISTRATION_NUMBER')
            ->innerJoin(UonStudent::tableName() . ' ST', 'ST.REGISTRATION_NUMBER=LMK.REGISTRATION_NUMBER')
            ->where(['LMK.MRKSHEET_ID' => $params['marksheetId']]);

    	print_r($query->createCommand()->getRawSql());

	exit();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => false
        ]);

        $this->load($params);

        return $dataProvider;
    }
}