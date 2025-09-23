<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 13-01-2021 11:22:48 
 * @modify date 13-01-2021 11:22:48 
 * @desc [description]
 */

/* @var $this yii\web\View */
/* @var $lecturers app\models\CourseAssignment */
/* @var $title string */

use app\models\EmpVerifyView;

$this->title = $title;

dd('Hello');
?>
<!-- code from: https://codepen.io/lehonti/pen/OzoXVa -->
<div class="lec-assignments">
    <div class="delete-assigned">Select lectures to remove them from this course.</div><br>
    <div class="list-group">
        <?php
        foreach ($lecturers as $lec):
            $lecturer = EmpVerifyView::find()->select(['PAYROLL_NO', 'SURNAME', 'OTHER_NAMES', 'EMP_TITLE'])
                ->where(['PAYROLL_NO' => $lec->PAYROLL_NO])->one();
            $name =  $lecturer->EMP_TITLE . ' ' . $lecturer->SURNAME . ' ' . $lecturer->OTHER_NAMES;
        ?>
            <input type="checkbox" name="lecturer" value="<?= $lec->PAYROLL_NO; ?>" id="<?= $lec->PAYROLL_NO . '-checkbox'; ?>" />
            <label class="list-group-item" for="<?= $lec->PAYROLL_NO . '-checkbox'; ?>"><i class="fas fa-user"></i> <?= $name; ?></label>
        <?php endforeach; ?>
    </div>
    <br>
    <div>
        <button class="btn btn-spacer lec-assigned" id="remove-lec">Remove Selected Lecturers</button>
    </div>
</div>

<?php
$LecCourses = <<< CSS
    .delete-assigned {
        background: #008cba;
        color:  #FFF;
        padding: 20px;
        margin-bottom: 10px;
    }
    .list-group-item {
        user-select: none;
        padding: 20px;
    }

    .list-group input[type="checkbox"] {
        display: none;
    }

    .list-group input[type="checkbox"] + .list-group-item {
        cursor: pointer;
    }

    .list-group input[type="checkbox"] + .list-group-item:before {
        color: transparent;
        font-weight: bold;
        margin-right: 1em;
    }

    .list-group input[type="checkbox"]:checked + .list-group-item {
        background-color: #008cba;
        color: #FFF;
    }

    .list-group input[type="checkbox"]:checked + .list-group-item:before {
        color: inherit;
    }
CSS;
$this->registerCss($LecCourses);
