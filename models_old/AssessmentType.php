<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 15-12-2020 20:50:22 
 * @modify date 15-12-2020 20:50:22 
 * @desc [description]
 */

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.LEC_ASSESSMENT_TYPES".
 *
 * @property int $ASSESSMENT_TYPE_ID
 * @property string $ASSESSMENT_NAME
 * @property string|null $ASSESSMENT_DESCRIPTION
 * @property int|null $LOCKED
 */
class AssessmentType extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.LEC_ASSESSMENT_TYPES';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['ASSESSMENT_NAME'], 'required'],
            [['ASSESSMENT_TYPE_ID', 'LOCKED'], 'integer'],
            [['ASSESSMENT_NAME'], 'string', 'max' => 55],
            [['ASSESSMENT_DESCRIPTION'], 'string', 'max' => 255],
            [['ASSESSMENT_TYPE_ID'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'ASSESSMENT_TYPE_ID' => 'Assessment Type ID',
            'ASSESSMENT_NAME' => 'Assessment Name',
            'ASSESSMENT_DESCRIPTION' => 'Assessment Description',
            'LOCKED' => 'Locked',
        ];
    }
}
