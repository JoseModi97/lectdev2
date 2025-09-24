<?php
/**
 * Class performance PDF partial.
 */

/**
 * @var array $reportDetails
 * @var string $user
 * @var string $date
 * @var array $gradeRows
 * @var int $totalStudents
 * @var array $averages
 * @var string $highestGrade
 * @var string $lowestGrade
 */

use yii\helpers\Html;

$reportSummary = [
    'Academic Year' => $reportDetails['academicYear'] ?? '',
    'Programme' => ($reportDetails['degreeName'] ?? '') . ' (' . ($reportDetails['degreeCode'] ?? '') . ')',
    'Level of Study' => strtoupper($reportDetails['level'] ?? ''),
    'Semester' => $reportDetails['semesterFullName'] ?? '',
    'Group' => strtoupper($reportDetails['group'] ?? ''),
];

$courseLabel = trim(($reportDetails['courseCode'] ?? '') . ' · ' . ($reportDetails['degreeName'] ?? ''));
$logo = Yii::getAlias('@webroot') . '/img/UoN_Logo.png';
?>
<div class="class-performance-pdf">
    <div class="letterhead">
        <img src="<?= $logo ?>" alt="UoN Logo" class="logo">
        <div class="school-details">
            <h1 class="school-name">UNIVERSITY OF NAIROBI</h1>
            <p class="school-address">P.O. Box 30197, G.P.O., Nairobi, 00100, Kenya</p>
        </div>
    </div>

    <div class="mb-3">
        <h1 class="fs-4 mb-1">Class Performance Report</h1>
        <p class="text-muted mb-0">
            <?= Html::encode($courseLabel); ?>
        </p>
    </div>

    <div class="border rounded mb-3">
        <div class="report-header px-3 py-2 d-flex justify-content-between align-items-center small text-uppercase">
            <span>University of Nairobi</span>
            <span><?= Html::encode($reportDetails['courseCode'] ?? ''); ?></span>
            <span>Printed by <?= Html::encode($user); ?></span>
            <span><?= Html::encode($date); ?></span>
        </div>
        <div class="p-3">
            <table class="table table-sm mb-0 summary-table">
                <tbody>
                <?php foreach ($reportSummary as $label => $value): ?>
                    <tr>
                        <th class="text-muted text-uppercase small"><?= Html::encode($label); ?></th>
                        <td class="fw-semibold text-dark"><?= Html::encode($value); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-5">
            <div class="section-title">Grade distribution</div>
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                <tr>
                    <th class="text-uppercase small text-muted">% Range</th>
                    <th class="text-uppercase small text-muted">Grade</th>
                    <th class="text-uppercase small text-muted text-end"># of students</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($gradeRows as $row): ?>
                    <tr>
                        <td><?= Html::encode($row['range']); ?></td>
                        <td><?= Html::encode($row['label']); ?></td>
                        <td class="text-end fw-semibold"><?= Html::encode($row['count']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="table-light">
                    <td colspan="2">Class size</td>
                    <td class="text-end fw-semibold"><?= Html::encode($totalStudents); ?></td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="col-4">
            <div class="section-title">Grade distribution chart</div>
            <div class="mb-2 small text-muted">Relative share of each grade.</div>
            <?php foreach ($gradeRows as $row): ?>
                <div class="mb-2">
                    <div class="d-flex justify-content-between small">
                        <span><?= Html::encode($row['label']); ?></span>
                        <span><?= Html::encode(number_format((float)$row['percentage'], 1)); ?>%</span>
                    </div>
                    <div class="bar-track">
                        <div class="bar-fill" style="width: <?= (float)min(100, $row['percentage']); ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="col-3">
            <div class="section-title">Key figures</div>
            <ul class="list-unstyled small mb-0">
                <li class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted text-uppercase">Highest grade</span>
                    <span class="fw-semibold"><?= Html::encode($highestGrade); ?></span>
                </li>
                <li class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted text-uppercase">Lowest grade</span>
                    <span class="fw-semibold"><?= Html::encode($lowestGrade); ?></span>
                </li>
                <li class="d-flex justify-content-between align-items-center">
                    <span class="text-muted text-uppercase">Report date</span>
                    <span class="fw-semibold"><?= Html::encode($date); ?></span>
                </li>
            </ul>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-5">
            <div class="section-title">Average scores chart</div>
            <div class="mb-2 small text-muted">Average performance per component.</div>
            <?php
            $averageMax = max(100, (float)$averages['coursework'], (float)$averages['exam'], (float)$averages['final']);
            $averageItems = [
                ['label' => 'Coursework', 'value' => (float)$averages['coursework']],
                ['label' => 'Exam', 'value' => (float)$averages['exam']],
                ['label' => 'Final', 'value' => (float)$averages['final']],
            ];
            ?>
            <?php foreach ($averageItems as $item): ?>
                <div class="mb-2">
                    <div class="d-flex justify-content-between small">
                        <span><?= Html::encode($item['label']); ?></span>
                        <span><?= Html::encode(number_format($item['value'], 2)); ?></span>
                    </div>
                    <?php
                    $percent = $averageMax > 0 ? round(($item['value'] / $averageMax) * 100, 1) : 0;
                    ?>
                    <div class="bar-track">
                        <div class="bar-fill" style="width: <?= (float)min(100, $percent); ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="col-4">
            <div class="section-title">Average scores summary</div>
            <table class="table table-sm table-bordered mb-0">
                <tbody>
                <tr>
                    <th class="text-uppercase small text-muted">Coursework</th>
                    <td class="text-end fw-semibold"><?= Html::encode(number_format((float)$averages['coursework'], 2)); ?></td>
                </tr>
                <tr>
                    <th class="text-uppercase small text-muted">Exam</th>
                    <td class="text-end fw-semibold"><?= Html::encode(number_format((float)$averages['exam'], 2)); ?></td>
                </tr>
                <tr>
                    <th class="text-uppercase small text-muted">Final</th>
                    <td class="text-end fw-semibold"><?= Html::encode(number_format((float)$averages['final'], 2)); ?></td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="col-3">
            <div class="section-title">Notes</div>
            <p class="notes mb-0">
                Use this report to capture performance highlights, share discussion points with the examination team,
                and provide supporting evidence when filing the official class performance summary.
            </p>
        </div>
    </div>

    <div class="section-title">Signatories</div>
    <table class="table table-sm table-bordered">
        <tbody>
        <tr>
            <td class="signatory-block">
                <div class="label mb-2">Internal Examiner</div>
                <div class="signature-line"></div>
                <div class="signatory-name">[Name]</div>
                <div class="signatory-date">Date:</div>
            </td>
            <td class="signatory-block">
                <div class="label mb-2">External Examiner</div>
                <div class="signature-line"></div>
                <div class="signatory-name">[Name]</div>
                <div class="signatory-date">Date:</div>
            </td>
            <td class="signatory-block">
                <div class="label mb-2">Dean/Director</div>
                <div class="signature-line"></div>
                <div class="signatory-name">[Name]</div>
                <div class="signatory-date">Date:</div>
            </td>
        </tr>
        </tbody>
    </table>
</div>
