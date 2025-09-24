<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 21-06-2021 10:51:52 
 * @modify date 21-06-2021 10:51:52 
 * @desc [description]
 */

use yii\bootstrap5\Modal;

Modal::begin([
    'title' => '<b class="text-primary">Edit Marks</b>',
    'id' => 'edit-marks-modal',
    'size' => 'modal-md',
    'options' => ['data-backdrop' => "static", 'data-keyboard' => "false"],
]);
echo "<div id='edit-marks-modal-content'></div>";
Modal::end();
