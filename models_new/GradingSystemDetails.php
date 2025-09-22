<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.GRADINGSYSDETAILS".
 *
 * @property int $GRADINGID
 * @property string $GRADE
 * @property float|null $UPPERBOUND
 * @property float|null $LOWERBOUND
 * @property string|null $LEGEND
 * @property string|null $EXTLEGEND
 * @property string|null $RECOMM_ID
 */
class GradingSystemDetails extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.GRADINGSYSDETAILS';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['GRADINGID', 'GRADE'], 'required'],
            [['GRADINGID'], 'integer'],
            [['UPPERBOUND', 'LOWERBOUND'], 'number'],
            [['GRADE'], 'string', 'max' => 6],
            [['LEGEND'], 'string', 'max' => 25],
            [['EXTLEGEND'], 'string', 'max' => 50],
            [['RECOMM_ID'], 'string', 'max' => 20],
            [['GRADINGID', 'GRADE'], 'unique', 'targetAttribute' => ['GRADINGID', 'GRADE']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'GRADINGID' => 'Gradingid',
            'GRADE' => 'Grade',
            'UPPERBOUND' => 'Upperbound',
            'LOWERBOUND' => 'Lowerbound',
            'LEGEND' => 'Legend',
            'EXTLEGEND' => 'Extlegend',
            'RECOMM_ID' => 'Recomm ID',
        ];
    }
}
