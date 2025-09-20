<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 15-12-2020 20:50:22 
 * @modify date 22-11-2021
 * @desc [description]
 */

namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class MarksUpload extends Model
{
    /**
     * @var UploadedFile
     */
    public $marksFile;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['marksFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xlsx'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'marksFile' => 'Marks file'
        ];
    }
}