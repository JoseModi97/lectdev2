<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "MUTHONI.LEVEL_OF_STUDY".
 *
 * @property int $LEVEL_OF_STUDY
 * @property string|null $NAME
 */
class LevelOfStudy extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'MUTHONI.LEVEL_OF_STUDY';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['LEVEL_OF_STUDY'], 'required'],
            [['LEVEL_OF_STUDY'], 'integer'],
            [['NAME'], 'string', 'max' => 40],
            [['LEVEL_OF_STUDY'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'LEVEL_OF_STUDY' => 'Level Of Study',
            'NAME' => 'Name',
        ];
    }
}
