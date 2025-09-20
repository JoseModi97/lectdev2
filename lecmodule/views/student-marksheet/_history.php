<?php

use yii\helpers\Html;

if (empty($history)) {
    echo "<p class='text-muted'>No history available.</p>";
    return;
}
?>


<table class="table table-bordered table-striped">
    <thead class="sticky-top" style="background-color: #f8f9fa;">
        <tr>
            <!-- <th>#</th> -->
            <!-- <th>id</th> -->
            <th>Course Marks</th>
            <th>Exam Marks</th>
            <th>Final Marks</th>
            <th>Remarks</th>
            <th>Entry Date</th>
            <th>Updated At</th>
            <th>Record Status</th>
            <th>Lec Approval</th>
            <th>HoD Approval</th>
            <th>Dean Approval</th>
            <th>Publish</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php $num = 0; ?>
        <?php foreach ($history as $record): ?>
            <tr>
                <td><?= Html::encode($record->COURSE_MARKS) ?></td>
                <td><?= Html::encode($record->EXAM_MARKS) ?></td>
                <td><?= Html::encode($record->FINAL_MARKS) ?></td>
                <td><?= Html::encode($record->REMARKS) ?></td>
                <td style="white-space: nowrap;"><?= Html::encode($record->ENTRY_DATE) ?></td>
                <td style="white-space: nowrap;"><?= Html::encode($record->LAST_UPDATE) ?></td>
                <td><?= Html::encode($record->RECORD_VALIDITY) ?></td>
                <td>
                    <?php if ($record->LECTURER_APPROVAL == 1):
                    ?>
                        <span class="badge bg-success">Approved</span>
                    <?php elseif ($record->LECTURER_APPROVAL == 0):
                    ?>
                        0
                    <?php else:
                    ?>
                        <span class="badge bg-danger">Rejected</span>
                    <?php endif;
                    ?>
                </td>
                <td>
                    <?php if ($record->HOD_APPROVAL == 1): ?>
                        <span class="badge bg-success">Approved</span>
                    <?php elseif ($record->HOD_APPROVAL == 0): ?>
                        0
                    <?php else: ?>
                        <span class="badge bg-danger">Rejected</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($record->DEAN_APPROVAL == 1): ?>
                        <span class="badge bg-success">Approved</span>
                    <?php elseif ($record->DEAN_APPROVAL == 0): ?>
                        0
                    <?php else: ?>
                        <span class="badge bg-danger">Rejected</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($record->PUBLISH_STATUS == 0): ?>
                        <span class="badge bg-danger">0 </span>
                    <?php elseif ($record->PUBLISH_STATUS == 1): ?>
                        <span class="badge bg-success"><i class="fa fa-check"></i></span>
                    <?php endif; ?>
                </td>
                <td>

                    <?php if ($record->RECORD_STATUS == 0): ?>
                        <span class="badge bg-danger">CLOSED </span>
                    <?php elseif ($record->RECORD_STATUS == 1): ?>
                        <span class="badge bg-success">OPEN</span>
                    <?php else: ?>
                        <span class="badge bg-success">EDITING</span>
                    <?php endif; ?>
                </td>

            </tr>
            <?php $num++ ?>
        <?php endforeach; ?>
    </tbody>
</table>