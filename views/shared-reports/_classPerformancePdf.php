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

$courseCode = $reportDetails['courseCode'] ?? '';
$degreeName = $reportDetails['degreeName'] ?? '';

$gradeLabels = array_map(static function ($row) {
    return (string)($row['label'] ?? '');
}, $gradeRows);

$gradeCounts = array_map(static function ($row) {
    return (int)($row['count'] ?? 0);
}, $gradeRows);

$gradeBarCount = max(count($gradeLabels), 1);
$gradeChartHeight = 200;
$gradeBarWidth = 28;
$gradeBarSpacing = 18;
$gradeAxisLeft = 42;
$gradeAxisBottom = $gradeChartHeight + 24;
$gradeSvgHeight = $gradeChartHeight + 70;
$gradeSvgWidth = (int)($gradeAxisLeft + ($gradeBarCount * ($gradeBarWidth + $gradeBarSpacing)) + 24);
$gradeMaxCount = max(array_merge($gradeCounts, [1]));
$gradeTickSteps = 4;

$averageChartHeight = 200;
$averageLabels = ['Coursework', 'Exam', 'Final Score'];
$averageValues = [
    (float)($averages['coursework'] ?? 0),
    (float)($averages['exam'] ?? 0),
    (float)($averages['final'] ?? 0),
];
$averageMaxValue = max(array_merge([100.0], $averageValues, [1]));
$averageBarWidth = 42;
$averageBarSpacing = 32;
$averageAxisLeft = 50;
$averageAxisBottom = $averageChartHeight + 24;
$averageSvgHeight = $averageChartHeight + 70;
$averageSvgWidth = (int)($averageAxisLeft + (count($averageLabels) * ($averageBarWidth + $averageBarSpacing)) + 32);
?>
<div class="class-performance-pdf">
    <div class="card summary-card">
        <div class="card-header card-header--primary">
            <table class="summary-header-grid">
                <tr>
                    <td class="brand">University of Nairobi</td>
                    <td class="course"><?= Html::encode($courseCode); ?></td>
                    <td class="printed-by">Printed by <?= Html::encode($user); ?></td>
                    <td class="report-date"><?= Html::encode($date); ?></td>
                </tr>
            </table>
        </div>
        <div class="card-body">
            <table class="summary-table">
                <?php foreach ($reportSummary as $label => $value): ?>
                    <tr>
                        <th><?= Html::encode($label); ?></th>
                        <td><?= Html::encode($value); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <table class="layout layout--grade">
        <tr>
            <td class="layout__col layout__col--grade-table">
                <div class="card">
                    <div class="card-header">Grade distribution</div>
                    <div class="card-body">
                        <table class="table grade-table">
                            <thead>
                            <tr>
                                <th>% Range</th>
                                <th>Grade</th>
                                <th class="text-end"># of students</th>
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
                            <tr class="total-row">
                                <td>Class size</td>
                                <td></td>
                                <td class="text-end fw-semibold"><?= Html::encode($totalStudents); ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </td>
            <td class="layout__col layout__col--grade-chart">
                <div class="card card--center">
                    <div class="card-header">Grade distribution chart</div>
                    <div class="card-body card-body--center">
                        <div class="chart-wrapper">
                            <svg class="chart-svg" width="<?= $gradeSvgWidth; ?>" height="<?= $gradeSvgHeight; ?>" viewBox="0 0 <?= $gradeSvgWidth; ?> <?= $gradeSvgHeight; ?>">
                                <line x1="<?= $gradeAxisLeft; ?>" y1="<?= $gradeAxisBottom; ?>" x2="<?= $gradeSvgWidth - 18; ?>" y2="<?= $gradeAxisBottom; ?>" class="chart-axis" />
                                <line x1="<?= $gradeAxisLeft; ?>" y1="<?= $gradeAxisBottom; ?>" x2="<?= $gradeAxisLeft; ?>" y2="<?= $gradeAxisBottom - $gradeChartHeight; ?>" class="chart-axis" />

                                <?php for ($tick = 1; $tick <= $gradeTickSteps; $tick++): ?>
                                    <?php
                                    $ratio = $tick / $gradeTickSteps;
                                    $tickValue = $gradeMaxCount * $ratio;
                                    $tickY = $gradeAxisBottom - ($gradeChartHeight * $ratio);
                                    ?>
                                    <line x1="<?= $gradeAxisLeft; ?>" y1="<?= $tickY; ?>" x2="<?= $gradeSvgWidth - 18; ?>" y2="<?= $tickY; ?>" class="chart-grid" />
                                    <text x="<?= $gradeAxisLeft - 8; ?>" y="<?= $tickY + 4; ?>" text-anchor="end" class="chart-tick"><?= Html::encode(number_format($tickValue)); ?></text>
                                <?php endfor; ?>

                                <?php foreach ($gradeCounts as $index => $count): ?>
                                    <?php
                                    $label = $gradeLabels[$index] ?? '';
                                    $barHeight = $gradeMaxCount > 0 ? ($count / $gradeMaxCount) * $gradeChartHeight : 0;
                                    $barX = $gradeAxisLeft + ($index * ($gradeBarWidth + $gradeBarSpacing)) + ($gradeBarSpacing / 2);
                                    $barY = $gradeAxisBottom - $barHeight;
                                    $valueY = $barHeight > 0 ? $barY - 6 : $gradeAxisBottom - 8;
                                    ?>
                                    <rect x="<?= $barX; ?>" y="<?= $barY; ?>" width="<?= $gradeBarWidth; ?>" height="<?= $barHeight; ?>" class="chart-bar" />
                                    <text x="<?= $barX + ($gradeBarWidth / 2); ?>" y="<?= $valueY; ?>" text-anchor="middle" class="chart-value"><?= Html::encode(number_format($count)); ?></text>
                                    <text x="<?= $barX + ($gradeBarWidth / 2); ?>" y="<?= $gradeAxisBottom + 18; ?>" text-anchor="middle" class="chart-label"><?= Html::encode($label); ?></text>
                                <?php endforeach; ?>
                            </svg>
                        </div>
                    </div>
                </div>
            </td>
            <td class="layout__col layout__col--key-figures">
                <div class="card">
                    <div class="card-header">Key figures</div>
                    <div class="card-body">
                        <p class="card-subtitle">Quick reference for print and review.</p>
                        <ul class="figures-list">
                            <li>
                                <span class="label">Highest grade</span>
                                <span class="value"><?= Html::encode($highestGrade); ?></span>
                            </li>
                            <li>
                                <span class="label">Lowest grade</span>
                                <span class="value"><?= Html::encode($lowestGrade); ?></span>
                            </li>
                            <li>
                                <span class="label">Report date</span>
                                <span class="value"><?= Html::encode($date); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <table class="layout layout--averages">
        <tr>
            <td class="layout__col layout__col--average-chart">
                <div class="card card--center">
                    <div class="card-header">Average scores chart</div>
                    <div class="card-body card-body--center">
                        <div class="chart-wrapper">
                            <svg class="chart-svg" width="<?= $averageSvgWidth; ?>" height="<?= $averageSvgHeight; ?>" viewBox="0 0 <?= $averageSvgWidth; ?> <?= $averageSvgHeight; ?>">
                                <line x1="<?= $averageAxisLeft; ?>" y1="<?= $averageAxisBottom; ?>" x2="<?= $averageSvgWidth - 24; ?>" y2="<?= $averageAxisBottom; ?>" class="chart-axis" />
                                <line x1="<?= $averageAxisLeft; ?>" y1="<?= $averageAxisBottom; ?>" x2="<?= $averageAxisLeft; ?>" y2="<?= $averageAxisBottom - $averageChartHeight; ?>" class="chart-axis" />

                                <?php for ($tick = 1; $tick <= 4; $tick++): ?>
                                    <?php
                                    $ratio = $tick / 4;
                                    $tickValue = $averageMaxValue * $ratio;
                                    $tickY = $averageAxisBottom - ($averageChartHeight * $ratio);
                                    ?>
                                    <line x1="<?= $averageAxisLeft; ?>" y1="<?= $tickY; ?>" x2="<?= $averageSvgWidth - 24; ?>" y2="<?= $tickY; ?>" class="chart-grid" />
                                    <text x="<?= $averageAxisLeft - 8; ?>" y="<?= $tickY + 4; ?>" text-anchor="end" class="chart-tick"><?= Html::encode(number_format($tickValue, 0)); ?></text>
                                <?php endfor; ?>

                                <?php foreach ($averageLabels as $index => $label): ?>
                                    <?php
                                    $value = $averageValues[$index] ?? 0;
                                    $barHeight = $averageMaxValue > 0 ? ($value / $averageMaxValue) * $averageChartHeight : 0;
                                    $barX = $averageAxisLeft + ($index * ($averageBarWidth + $averageBarSpacing)) + ($averageBarSpacing / 2);
                                    $barY = $averageAxisBottom - $barHeight;
                                    $valueY = $barHeight > 0 ? $barY - 8 : $averageAxisBottom - 8;
                                    ?>
                                    <rect x="<?= $barX; ?>" y="<?= $barY; ?>" width="<?= $averageBarWidth; ?>" height="<?= $barHeight; ?>" class="chart-bar-secondary" />
                                    <text x="<?= $barX + ($averageBarWidth / 2); ?>" y="<?= $valueY; ?>" text-anchor="middle" class="chart-value"><?= Html::encode(number_format($value, 1)); ?></text>
                                    <text x="<?= $barX + ($averageBarWidth / 2); ?>" y="<?= $averageAxisBottom + 18; ?>" text-anchor="middle" class="chart-label"><?= Html::encode($label); ?></text>
                                <?php endforeach; ?>
                            </svg>
                        </div>
                    </div>
                </div>
            </td>
            <td class="layout__col layout__col--average-summary">
                <div class="card">
                    <div class="card-header">Average scores summary</div>
                    <div class="card-body">
                        <ul class="average-list">
                            <li>
                                <span class="label">Coursework</span>
                                <span class="value"><?= Html::encode(number_format((float)($averages['coursework'] ?? 0), 2)); ?></span>
                            </li>
                            <li>
                                <span class="label">Exam</span>
                                <span class="value"><?= Html::encode(number_format((float)($averages['exam'] ?? 0), 2)); ?></span>
                            </li>
                            <li>
                                <span class="label">Final</span>
                                <span class="value"><?= Html::encode(number_format((float)($averages['final'] ?? 0), 2)); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </td>
            <td class="layout__col layout__col--notes">
                <div class="card">
                    <div class="card-header">Notes</div>
                    <div class="card-body">
                        <p class="notes-text">
                            Use this report to highlight key trends and share quick talking points before meetings
                            or when exporting the PDF.
                        </p>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="card signatories-card">
        <div class="card-header">Signatories</div>
        <div class="card-body">
            <table class="signatories-table">
                <tr>
                    <td>
                        <div class="signatory-label">Internal Examiner</div>
                        <div class="signature-line"></div>
                        <p class="signatory-meta">Name:</p>
                        <p class="signatory-meta">Date:</p>
                    </td>
                    <td>
                        <div class="signatory-label">External Examiner</div>
                        <div class="signature-line"></div>
                        <p class="signatory-meta">Name:</p>
                        <p class="signatory-meta">Date:</p>
                    </td>
                    <td>
                        <div class="signatory-label">Dean/Director</div>
                        <div class="signature-line"></div>
                        <p class="signatory-meta">Name:</p>
                        <p class="signatory-meta">Date:</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
