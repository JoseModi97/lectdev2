<?php if ($student): ?>
    <div class="d-flex flex-wrap align-items-center" style="margin: 3px 0;">
        <div class="me-3">
            <span><strong>Registration Number:</strong> <?= $student->REGISTRATION_NUMBER ?></span>
        </div>
        <div class="me-3">
            <span><strong>Name:</strong> <?= $student->SURNAME . ' ' . $student->OTHER_NAMES ?></span>
        </div>
        <div class="me-3">
            <span><strong>Program:</strong> <?= $student->D_PROG_DEGREE_CODE ?></span>
        </div>
        <div class="me-3">
            <span><strong>Academic Year:</strong> <?= $student->ACADEMIC_YEAR ?></span>
        </div>
    </div>
<?php endif; ?>