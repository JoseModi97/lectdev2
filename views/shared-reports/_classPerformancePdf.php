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
$reportReference = $reportDetails['marksheetId'] ?? '';

$gradeLabels = array_map(static function ($row) {
    return (string)($row['label'] ?? '');
}, $gradeRows);

$gradeCounts = array_map(static function ($row) {
    return (int)($row['count'] ?? 0);
}, $gradeRows);

$gradeBarCount = max(count($gradeLabels), 1);
$gradeChartHeight = 170;
$gradeBarWidth = 24;
$gradeBarSpacing = 12;
$gradeAxisLeft = 38;
$gradeAxisBottom = $gradeChartHeight + 20;
$gradeSvgHeight = $gradeChartHeight + 60;
$gradeSvgWidth = (int)($gradeAxisLeft + ($gradeBarCount * ($gradeBarWidth + $gradeBarSpacing)) + 16);
$gradeMaxCount = max(array_merge($gradeCounts, [1]));
$gradeTickSteps = 4;

$averageChartHeight = 170;
$averageLabels = ['Coursework', 'Exam', 'Final Score'];
$averageValues = [
    (float)($averages['coursework'] ?? 0),
    (float)($averages['exam'] ?? 0),
    (float)($averages['final'] ?? 0),
];
$averageMaxValue = max(array_merge([100.0], $averageValues, [1]));
$averageBarWidth = 32;
$averageBarSpacing = 22;
$averageAxisLeft = 44;
$averageAxisBottom = $averageChartHeight + 20;
$averageSvgHeight = $averageChartHeight + 60;
$averageSvgWidth = (int)($averageAxisLeft + (count($averageLabels) * ($averageBarWidth + $averageBarSpacing)) + 24);

$summaryChunks = array_chunk($reportSummary, 3, true);
?>
<div class="class-performance-pdf">
    <div class="report-title">
        <h1>Class Performance Report</h1>
        <p class="report-subtitle"><?= Html::encode($courseLabel); ?></p>
        <p class="report-meta">
            Printed by <?= Html::encode($user); ?> · <?= Html::encode($date); ?><?php if (!empty($reportReference)): ?> · Ref: <?= Html::encode($reportReference); ?><?php endif; ?>
        </p>
    </div>

    <div class="summary-card">
        <div class="summary-card__header">Class details</div>
        <div class="summary-card__body">
            <table class="summary-grid">
                <?php foreach ($summaryChunks as $chunk): ?>
                    <tr>
                        <?php
                        $cells = 0;
                        foreach ($chunk as $label => $value):
                            $cells++;
                        ?>
                            <td>
                                <span class="summary-grid__label"><?= Html::encode($label); ?></span>
                                <span class="summary-grid__value"><?= Html::encode($value); ?></span>
                            </td>
                        <?php endforeach; ?>
                        <?php while ($cells < 3): $cells++; ?>
                            <td>&nbsp;</td>
                        <?php endwhile; ?>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <table class="layout-grid">
        <colgroup>
            <col class="layout-grid__col layout-grid__col--wide" />
            <col class="layout-grid__col layout-grid__col--narrow" />
        </colgroup>
        <tbody>
            <tr>
                <td class="layout-grid__cell">
                    <div class="panel-card">
                        <div class="panel-body">
                            <p class="panel-intro">Distribution of grades attained by the class.</p>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>% range</th>
                                        <th>Grade</th>
                                        <th class="text-end"># of students</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($gradeRows as $row): ?>
                                        <tr>
                                            <td><?= Html::encode($row['range']); ?></td>
                                            <td><?= Html::encode($row['label']); ?></td>
                                            <td class="text-end"><?= Html::encode($row['count']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="totals-row">
                                        <td colspan="2">Class size</td>
                                        <td class="text-end"><?= Html::encode($totalStudents); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </td>
                <td class="layout-grid__cell layout-grid__cell--stack">
                    <table class="stack-grid">
                        <tbody>
                            <tr>
                                <td class="stack-grid__cell">
                                    <div class="panel-card">
                                        <div class="panel-body">
                                            <p class="panel-intro">Visual overview of grade performance.</p>
                                            <div class="chart-container">
                                                <svg class="chart-svg" width="<?= $gradeSvgWidth; ?>" height="<?= $gradeSvgHeight; ?>" viewBox="0 0 <?= $gradeSvgWidth; ?> <?= $gradeSvgHeight; ?>">
                                                    <line x1="<?= $gradeAxisLeft; ?>" y1="<?= $gradeAxisBottom; ?>" x2="<?= $gradeSvgWidth - 12; ?>" y2="<?= $gradeAxisBottom; ?>" class="chart-axis" />
                                                    <line x1="<?= $gradeAxisLeft; ?>" y1="<?= $gradeAxisBottom; ?>" x2="<?= $gradeAxisLeft; ?>" y2="<?= $gradeAxisBottom - $gradeChartHeight; ?>" class="chart-axis" />

                                                    <?php for ($tick = 1; $tick <= $gradeTickSteps; $tick++): ?>
                                                        <?php
                                                        $ratio = $tick / $gradeTickSteps;
                                                        $tickValue = $gradeMaxCount * $ratio;
                                                        $tickY = $gradeAxisBottom - ($gradeChartHeight * $ratio);
                                                        ?>
                                                        <line x1="<?= $gradeAxisLeft; ?>" y1="<?= $tickY; ?>" x2="<?= $gradeSvgWidth - 12; ?>" y2="<?= $tickY; ?>" class="chart-grid" />
                                                        <text x="<?= $gradeAxisLeft - 8; ?>" y="<?= $tickY + 4; ?>" text-anchor="end" class="chart-tick"><?= Html::encode(number_format($tickValue)); ?></text>
                                                    <?php endfor; ?>

                                                    <?php foreach ($gradeCounts as $index => $count): ?>
                                                        <?php
                                                        $label = $gradeLabels[$index] ?? '';
                                                        $barHeight = $gradeMaxCount > 0 ? ($count / $gradeMaxCount) * $gradeChartHeight : 0;
                                                        $barX = $gradeAxisLeft + ($index * ($gradeBarWidth + $gradeBarSpacing)) + ($gradeBarSpacing / 2);
                                                        $barY = $gradeAxisBottom - $barHeight;
                                                        $valueY = $barHeight > 0 ? $barY - 6 : $gradeAxisBottom - 6;
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
                            </tr>
                            <tr>
                                <td class="stack-grid__cell">
                                    <div class="panel-card panel-card--compact">
                                        <div class="panel-body">
                                            <p class="panel-intro">Quick reference metrics for review.</p>
                                            <table class="info-table">
                                                <tr>
                                                    <th>Highest grade</th>
                                                    <td><?= Html::encode($highestGrade); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Lowest grade</th>
                                                    <td><?= Html::encode($lowestGrade); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Report date</th>
                                                    <td><?= Html::encode($date); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="layout-grid__cell">
                    <div class="panel-card">
                        <div class="panel-body">
                            <p class="panel-intro">Average marks per assessment component.</p>
                            <div class="chart-container">
                                <svg class="chart-svg" width="<?= $averageSvgWidth; ?>" height="<?= $averageSvgHeight; ?>" viewBox="0 0 <?= $averageSvgWidth; ?> <?= $averageSvgHeight; ?>">
                                    <line x1="<?= $averageAxisLeft; ?>" y1="<?= $averageAxisBottom; ?>" x2="<?= $averageSvgWidth - 18; ?>" y2="<?= $averageAxisBottom; ?>" class="chart-axis" />
                                    <line x1="<?= $averageAxisLeft; ?>" y1="<?= $averageAxisBottom; ?>" x2="<?= $averageAxisLeft; ?>" y2="<?= $averageAxisBottom - $averageChartHeight; ?>" class="chart-axis" />

                                    <?php for ($tick = 1; $tick <= 4; $tick++): ?>
                                        <?php
                                        $ratio = $tick / 4;
                                        $tickValue = $averageMaxValue * $ratio;
                                        $tickY = $averageAxisBottom - ($averageChartHeight * $ratio);
                                        ?>
                                        <line x1="<?= $averageAxisLeft; ?>" y1="<?= $tickY; ?>" x2="<?= $averageSvgWidth - 18; ?>" y2="<?= $tickY; ?>" class="chart-grid" />
                                        <text x="<?= $averageAxisLeft - 8; ?>" y="<?= $tickY + 4; ?>" text-anchor="end" class="chart-tick"><?= Html::encode(number_format($tickValue, 0)); ?></text>
                                    <?php endfor; ?>

                                    <?php foreach ($averageLabels as $index => $label): ?>
                                        <?php
                                        $value = $averageValues[$index] ?? 0;
                                        $barHeight = $averageMaxValue > 0 ? ($value / $averageMaxValue) * $averageChartHeight : 0;
                                        $barX = $averageAxisLeft + ($index * ($averageBarWidth + $averageBarSpacing)) + ($averageBarSpacing / 2);
                                        $barY = $averageAxisBottom - $barHeight;
                                        $valueY = $barHeight > 0 ? $barY - 8 : $averageAxisBottom - 6;
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
                <td class="layout-grid__cell layout-grid__cell--stack">
                    <table class="stack-grid">
                        <tbody>
                            <tr>
                                <td class="stack-grid__cell">
                                    <div class="panel-card panel-card--compact">
                                        <div class="panel-body">
                                            <table class="info-table">
                                                <tr>
                                                    <th>Coursework</th>
                                                    <td><?= Html::encode(number_format((float)$averages['coursework'], 2)); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Exam</th>
                                                    <td><?= Html::encode(number_format((float)$averages['exam'], 2)); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Final score</th>
                                                    <td><?= Html::encode(number_format((float)$averages['final'], 2)); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="stack-grid__cell">
                                    <div class="panel-card panel-card--notes">
                                        <div class="panel-header">Notes</div>
                                        <div class="panel-body">
                                            <p class="notes-text">
                                                Use this report to highlight key trends and prepare quick talking points before meetings or when
                                                exporting the PDF for departmental reviews.
                                            </p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="panel-card signatory-card">
        <div class="panel-header">Signatories</div>
        <div class="panel-body">
            <table class="signatory-table">
                <tr>
                    <td>
                        <div class="signatory-label">Internal Examiner</div>
                        <div class="signature-line"></div>
                        <div class="signatory-meta">Name:</div>
                        <div class="signatory-meta">Date:</div>
                    </td>
                    <td>
                        <div class="signatory-label">External Examiner</div>
                        <div class="signature-line"></div>
                        <div class="signatory-meta">Name:</div>
                        <div class="signatory-meta">Date:</div>
                    </td>
                    <td>
                        <div class="signatory-label">Dean/Director</div>
                        <div class="signature-line"></div>
                        <div class="signatory-meta">Name:</div>
                        <div class="signatory-meta">Date:</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>