<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 21-06-2021 10:51:52 
 * @modify date 21-06-2021 10:51:52 
 * @desc [description]
 */

use yii\bootstrap\Modal;

Modal::begin([
    'header' => '<b>Edit Marks</b>',
    'id' => 'edit-marks-modal',
    'size' => 'modal-md',
    'options' => ['data-backdrop'=>"static", 'data-keyboard'=>"false"],
]);
    echo "<div id='edit-marks-modal-content'></div>";
Modal::end();