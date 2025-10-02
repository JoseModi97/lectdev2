<?php

/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var string $regNumber
 * @var string $studentName
 * @var string $academicYear
 * @var string $degreeCode
 * @var string $degreeName
 * @var string $levelOfStudy
 * @var string $levelName
 * @var string $group
 * @var string $groupName
 * @var string $recommendation
 * @var string $GPA
 * @var int $studentCoursesTotal
 * @var array $courses
 */

use yii\helpers\Html;

$this->title = 'Student Details: ' . $regNumber . ' - ' . $studentName;
?>

<div class="modal-header p-3">
    <h6 class="modal-title" id="studentDetailsModalLabel">Student Details: <?= Html::encode($regNumber); ?> - <?= Html::encode($studentName); ?></h6>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <button type="button" class="close custom-modal-close" aria-label="Close">
        <span aria-hidden="true" style="color: white; font-weight: bold;">Close&nbsp;&nbsp; &times;</span>
    </button>
</div>
<div class="modal-body" style="max-height: calc(100vh - 200px); overflow-y: auto;">
    <p><b>Academic Year:</b> <?= Html::encode($academicYear); ?></p>
    <p><b>Programme:</b> <?= Html::encode($degreeName); ?> (<?= Html::encode($degreeCode); ?>)</p>
    <p><b>Level of Study:</b> <?= Html::encode($levelName); ?> (<?= Html::encode($levelOfStudy); ?>)</p>
    <p><b>Group:</b> <?= Html::encode($groupName); ?> (<?= Html::encode($group); ?>)</p>
    <p><b>Recommendation:</b> <?= Html::encode($recommendation); ?></p>
    <p><b>GPA:</b> <?= Html::encode($GPA); ?></p>
    <p><b>Total Units:</b> <?= Html::encode($studentCoursesTotal); ?></p>

    <hr>

    <h6>Courses:</h6>
    <table class="table table-bordered table-condensed">
        <thead>
            <tr>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Marks</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($courses)): ?>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><?= Html::encode($course['code']); ?></td>
                        <td><?= Html::encode($course['name']); ?></td>
                        <td><?= Html::encode($course['finalMarks']); ?></td>
                        <td><?= Html::encode($course['grade']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No courses found for this student.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary custom-modal-close">Close</button>
</div>