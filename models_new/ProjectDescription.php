<?php
/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 */

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.PROJECT_DESCRIPTION".
 *
 * @property string $REGISTRATION_NUMBER
 * @property string|null $PROJECT_CODE
 * @property string|null $PROJECT_TITLE
 * @property string|null $GRADE
 * @property float|null $MARKS
 * @property int|null $HOURS
 * @property string|null $PROJECT_TYPE
 */
class ProjectDescription extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.PROJECT_DESCRIPTION';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['REGISTRATION_NUMBER'], 'required'],
            [['MARKS'], 'number'],
            [['HOURS'], 'integer'],
            [['REGISTRATION_NUMBER'], 'string', 'max' => 30],
            [['PROJECT_CODE'], 'string', 'max' => 20],
            [['PROJECT_TITLE'], 'string', 'max' => 500],
            [['GRADE'], 'string', 'max' => 2],
            [['PROJECT_TYPE'], 'string', 'max' => 50],
            [['REGISTRATION_NUMBER'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'REGISTRATION_NUMBER' => 'Registration Number',
            'PROJECT_CODE' => 'Project Code',
            'PROJECT_TITLE' => 'Project Title',
            'GRADE' => 'Grade',
            'MARKS' => 'Marks',
            'HOURS' => 'Hours',
            'PROJECT_TYPE' => 'Project Type',
        ];
    }
}
