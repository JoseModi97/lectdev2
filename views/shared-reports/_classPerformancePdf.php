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

$metrics = [
    [
        'label' => 'Class size',
        'value' => number_format((float)$totalStudents),
        'caption' => 'Students assessed',
    ],
    [
        'label' => 'Average coursework',
        'value' => number_format((float)$averages['coursework'], 1),
        'caption' => 'Out of 100',
    ],
    [
        'label' => 'Average exam',
        'value' => number_format((float)$averages['exam'], 1),
        'caption' => 'Out of 100',
    ],
    [
        'label' => 'Average final',
        'value' => number_format((float)$averages['final'], 1),
        'caption' => 'Overall score',
    ],
];

$courseLabel = trim(($reportDetails['courseCode'] ?? '') . ' · ' . ($reportDetails['degreeName'] ?? ''));
$logo = Yii::getAlias('@webroot') . '/img/UoN_Logo.png';
$reportReference = $reportDetails['marksheetId'] ?? '';

$gradeLabels = array_map(static function ($row) {
    return (string)($row['label'] ?? '');
}, $gradeRows);

$gradeCounts = array_map(static function ($row) {
    return (int)($row['count'] ?? 0);
}, $gradeRows);

$gradePercentages = array_map(static function ($row) {
    return (float)($row['percentage'] ?? 0.0);
}, $gradeRows);

$gradePalette = [
    'A' => ['#0d7c66', '#13b87b'],
    'B' => ['#1e8d73', '#2cc48d'],
    'C' => ['#2e9d82', '#45d1a7'],
    'D' => ['#d77c1f', '#f0b552'],
    'E' => ['#c43f3f', '#ef6d6d'],
    'F' => ['#b12f3d', '#e05763'],
    'E*' => ['#5f6a7d', '#8b95a6'],
    'X' => ['#7c8797', '#aeb7c4'],
];

$gradeBarCount = max(count($gradeLabels), 1);
$gradeChartHeight = 184;
$gradeBarWidth = 34;
$gradeBarSpacing = 22;
$gradeAxisLeft = 48;
$gradeAxisBottom = $gradeChartHeight + 26;
$gradeSvgHeight = $gradeChartHeight + 72;
$gradeSvgWidth = (int)($gradeAxisLeft + ($gradeBarCount * ($gradeBarWidth + $gradeBarSpacing)) + 40);
$gradeMaxCount = max(array_merge($gradeCounts, [1]));
$gradeTickSteps = 4;
$gradePlotPadding = 24;
$gradePlotX = $gradeAxisLeft - $gradePlotPadding;
$gradePlotY = $gradeAxisBottom - $gradeChartHeight - 12;
$gradePlotWidth = max($gradeSvgWidth - $gradePlotX - 16, 0);
$gradePlotHeight = $gradeChartHeight + 36;
$gradeAxisRight = $gradePlotX + $gradePlotWidth - 12;

$gradeMaxIndex = null;
$gradeMaxValue = -1;
foreach ($gradeCounts as $index => $count) {
    if ($count > $gradeMaxValue) {
        $gradeMaxValue = $count;
        $gradeMaxIndex = $index;
    }
}

$averageChartHeight = 180;
$averageLabels = ['Coursework', 'Exam', 'Final Score'];
$averageValues = [
    (float)($averages['coursework'] ?? 0),
    (float)($averages['exam'] ?? 0),
    (float)($averages['final'] ?? 0),
];
$averageMaxValue = max(array_merge([100.0], $averageValues, [1]));
$averageBarWidth = 44;
$averageBarSpacing = 36;
$averageAxisLeft = 54;
$averageAxisBottom = $averageChartHeight + 28;
$averageSvgHeight = $averageChartHeight + 76;
$averageSvgWidth = (int)($averageAxisLeft + (count($averageLabels) * ($averageBarWidth + $averageBarSpacing)) + 48);
$averagePassThreshold = 40.0;
$averageStretchThreshold = 70.0;
$averagePassY = $averageAxisBottom - ($averagePassThreshold / $averageMaxValue) * $averageChartHeight;
$averageStretchY = $averageAxisBottom - ($averageStretchThreshold / $averageMaxValue) * $averageChartHeight;
$averagePlotPadding = 28;
$averagePlotX = $averageAxisLeft - $averagePlotPadding;
$averagePlotY = $averageAxisBottom - $averageChartHeight - 16;
$averagePlotWidth = max($averageSvgWidth - $averagePlotX - 18, 0);
$averagePlotHeight = $averageChartHeight + 44;
$averageAxisRight = $averagePlotX + $averagePlotWidth - 20;
$averageGradientStart = '#0c4fab';
$averageGradientEnd = '#1f80f0';
$averageTargetStart = '#d9ecff';
$averageTargetEnd = '#eef5ff';
?>
<div class="class-performance-pdf">
    <div class="letterhead">
        <img src="<?= $logo ?>" alt="UoN Logo" class="logo">
        <div class="institution">
            <p class="institution-name">UNIVERSITY OF NAIROBI</p>
            <p class="institution-tagline">A world-class university committed to scholarly excellence</p>
            <p class="institution-contact">University Way, P.O. Box 30197 - 00100 Nairobi, Kenya · www.uonbi.ac.ke</p>
        </div>
    </div>

    <div class="brand-divider"></div>

    <div class="document-title">
        <p class="title-main">Class Performance Report</p>
        <p class="title-sub"><?= Html::encode($courseLabel); ?></p>
    </div>

    <div class="meta-card">
        <div class="meta-header">Academic Affairs Division</div>
        <div class="meta-body">
            <table class="table table-sm summary-table mb-0">
                <tbody>
                <?php foreach ($reportSummary as $label => $value): ?>
                    <tr>
                        <th><?= Html::encode($label); ?></th>
                        <td><?= Html::encode($value); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="meta-footer">
            <div class="meta-item">
                Prepared by
                <span><?= Html::encode($user); ?></span>
            </div>
            <div class="meta-item">
                Report date
                <span><?= Html::encode($date); ?></span>
            </div>
            <div class="meta-item">
                Reference
                <span><?= Html::encode($reportReference); ?></span>
            </div>
        </div>
    </div>

    <table class="metrics-table">
        <tr>
            <?php foreach ($metrics as $metric): ?>
                <td>
                    <div class="metric-card">
                        <div class="metric-label"><?= Html::encode($metric['label']); ?></div>
                        <div class="metric-value"><?= Html::encode($metric['value']); ?></div>
                        <div class="metric-caption"><?= Html::encode($metric['caption']); ?></div>
                    </div>
                </td>
            <?php endforeach; ?>
        </tr>
    </table>

    <div class="row">
        <div class="col-6">
            <div class="panel-card">
                <div class="panel-header">Grade distribution</div>
                <div class="panel-body">
                    <div class="chart-container">
                        <svg class="chart-svg" width="<?= $gradeSvgWidth; ?>" height="<?= $gradeSvgHeight; ?>" viewBox="0 0 <?= $gradeSvgWidth; ?> <?= $gradeSvgHeight; ?>">
                            <defs>
                                <?php foreach ($gradeRows as $index => $row): ?>
                                    <?php
                                    $paletteKey = strtoupper((string)($row['label'] ?? ''));
                                    $gradientColours = $gradePalette[$paletteKey] ?? ['#008751', '#12b873'];
                                    $gradientId = 'gradeBarGradient' . $index;
                                    ?>
                                    <linearGradient id="<?= $gradientId; ?>" x1="0" y1="1" x2="0" y2="0">
                                        <stop offset="0%" stop-color="<?= $gradientColours[0]; ?>" />
                                        <stop offset="100%" stop-color="<?= $gradientColours[1]; ?>" />
                                    </linearGradient>
                                <?php endforeach; ?>
                            </defs>

                            <rect x="<?= $gradePlotX; ?>" y="<?= $gradePlotY; ?>" width="<?= $gradePlotWidth; ?>" height="<?= $gradePlotHeight; ?>" class="chart-plot" rx="12" />

                            <line x1="<?= $gradeAxisLeft; ?>" y1="<?= $gradeAxisBottom; ?>" x2="<?= $gradeAxisRight; ?>" y2="<?= $gradeAxisBottom; ?>" class="chart-axis" />
                            <line x1="<?= $gradeAxisLeft; ?>" y1="<?= $gradeAxisBottom; ?>" x2="<?= $gradeAxisLeft; ?>" y2="<?= $gradeAxisBottom - $gradeChartHeight; ?>" class="chart-axis" />

                            <?php for ($tick = 1; $tick <= $gradeTickSteps; $tick++): ?>
                                <?php
                                $ratio = $tick / $gradeTickSteps;
                                $tickValue = $gradeMaxCount * $ratio;
                                $tickY = $gradeAxisBottom - ($gradeChartHeight * $ratio);
                                ?>
                                <line x1="<?= $gradeAxisLeft; ?>" y1="<?= $tickY; ?>" x2="<?= $gradeAxisRight; ?>" y2="<?= $tickY; ?>" class="chart-grid" />
                                <text x="<?= $gradeAxisLeft - 10; ?>" y="<?= $tickY + 4; ?>" text-anchor="end" class="chart-tick"><?= Html::encode(number_format($tickValue)); ?></text>
                            <?php endfor; ?>

                            <text x="<?= $gradeAxisLeft - 36; ?>" y="<?= $gradePlotY + ($gradePlotHeight / 2); ?>" class="chart-axis-label" transform="rotate(-90 <?= $gradeAxisLeft - 36; ?> <?= $gradePlotY + ($gradePlotHeight / 2); ?>)">Students</text>
                            <text x="<?= $gradeAxisLeft + ($gradePlotWidth / 2); ?>" y="<?= $gradeAxisBottom + 34; ?>" class="chart-axis-label">Grade</text>

                            <?php foreach ($gradeCounts as $index => $count): ?>
                                <?php
                                $label = $gradeLabels[$index] ?? '';
                                $percentage = $gradePercentages[$index] ?? 0;
                                $gradientId = 'gradeBarGradient' . $index;
                                $barHeight = $gradeMaxCount > 0 ? ($count / $gradeMaxCount) * $gradeChartHeight : 0;
                                $barX = $gradeAxisLeft + ($index * ($gradeBarWidth + $gradeBarSpacing));
                                $barY = $gradeAxisBottom - $barHeight;
                                $valueY = $barHeight > 0 ? max($barY - 10, 18) : $gradeAxisBottom - 12;
                                $subValueY = min($valueY + 12, $gradeAxisBottom - 4);
                                ?>
                                <?php if ($barHeight > 0): ?>
                                    <rect x="<?= $barX; ?>" y="<?= $barY + 4; ?>" width="<?= $gradeBarWidth; ?>" height="<?= max($barHeight - 2, 0); ?>" class="chart-bar-shadow" rx="10" opacity="0.15" />
                                <?php endif; ?>
                                <rect x="<?= $barX; ?>" y="<?= $barY; ?>" width="<?= $gradeBarWidth; ?>" height="<?= $barHeight; ?>" fill="url(#<?= $gradientId; ?>)" class="chart-bar<?= $index === $gradeMaxIndex && $gradeMaxValue > 0 ? ' chart-bar-top' : ''; ?>" rx="10" />
                                <text x="<?= $barX + ($gradeBarWidth / 2); ?>" y="<?= $valueY; ?>" text-anchor="middle" class="chart-value chart-value--emerald"><?= Html::encode(number_format($count)); ?></text>
                                <text x="<?= $barX + ($gradeBarWidth / 2); ?>" y="<?= $subValueY; ?>" text-anchor="middle" class="chart-subvalue"><?= Html::encode(number_format($percentage, 1)); ?>%</text>
                                <text x="<?= $barX + ($gradeBarWidth / 2); ?>" y="<?= $gradeAxisBottom + 20; ?>" text-anchor="middle" class="chart-label"><?= Html::encode($label); ?></text>
                            <?php endforeach; ?>
                        </svg>
                    </div>
                    <table class="table table-sm table-bordered mb-0">
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
                                <td class="text-end fw-semibold"><?= Html::encode($row['count']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="totals-row">
                            <td colspan="2">Class size</td>
                            <td class="text-end fw-semibold"><?= Html::encode($totalStudents); ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="panel-card">
                <div class="panel-header">Grade insights</div>
                <div class="panel-body">
                    <p class="panel-intro">Relative contribution of each grade achieved in the unit.</p>
                    <?php foreach ($gradeRows as $row): ?>
                        <div class="bar-row">
                            <div class="bar-label"><?= Html::encode($row['label']); ?></div>
                            <div class="bar-percent"><?= Html::encode(number_format((float)$row['percentage'], 1)); ?>%</div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width: <?= (float)min(100, $row['percentage']); ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="key-figures">
                        <div class="key-figure">
                            <span>Highest grade</span>
                            <strong><?= Html::encode($highestGrade); ?></strong>
                        </div>
                        <div class="key-figure">
                            <span>Lowest grade</span>
                            <strong><?= Html::encode($lowestGrade); ?></strong>
                        </div>
                        <div class="key-figure">
                            <span>Report date</span>
                            <strong><?= Html::encode($date); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="panel-card">
                <div class="panel-header">Average performance</div>
                <div class="panel-body">
                    <p class="panel-intro">Average marks per assessment component.</p>
                    <div class="chart-container">
                        <svg class="chart-svg" width="<?= $averageSvgWidth; ?>" height="<?= $averageSvgHeight; ?>" viewBox="0 0 <?= $averageSvgWidth; ?> <?= $averageSvgHeight; ?>">
                            <defs>
                                <linearGradient id="averageBarGradient" x1="0" y1="1" x2="0" y2="0">
                                    <stop offset="0%" stop-color="<?= $averageGradientStart; ?>" />
                                    <stop offset="100%" stop-color="<?= $averageGradientEnd; ?>" />
                                </linearGradient>
                                <linearGradient id="averageTargetGradient" x1="0" y1="1" x2="0" y2="0">
                                    <stop offset="0%" stop-color="<?= $averageTargetStart; ?>" />
                                    <stop offset="100%" stop-color="<?= $averageTargetEnd; ?>" />
                                </linearGradient>
                            </defs>

                            <rect x="<?= $averagePlotX; ?>" y="<?= $averagePlotY; ?>" width="<?= $averagePlotWidth; ?>" height="<?= $averagePlotHeight; ?>" class="chart-plot" rx="12" />

                            <?php if ($averageStretchY < $averageAxisBottom): ?>
                                <?php
                                $targetBandY = max($averageStretchY, $averagePlotY);
                                $targetBandHeight = max($averageAxisBottom - $targetBandY, 0);
                                ?>
                                <rect x="<?= $averagePlotX + 6; ?>" y="<?= $targetBandY; ?>" width="<?= max($averagePlotWidth - 12, 0); ?>" height="<?= $targetBandHeight; ?>" fill="url(#averageTargetGradient)" class="chart-target-band" rx="12" />
                                <text x="<?= $averageAxisRight - 4; ?>" y="<?= max($targetBandY + 18, 16); ?>" text-anchor="end" class="chart-threshold-label">Excellence ≥ <?= Html::encode(number_format($averageStretchThreshold, 0)); ?></text>
                            <?php endif; ?>

                            <line x1="<?= $averageAxisLeft; ?>" y1="<?= $averageAxisBottom; ?>" x2="<?= $averageAxisRight; ?>" y2="<?= $averageAxisBottom; ?>" class="chart-axis" />
                            <line x1="<?= $averageAxisLeft; ?>" y1="<?= $averageAxisBottom; ?>" x2="<?= $averageAxisLeft; ?>" y2="<?= $averageAxisBottom - $averageChartHeight; ?>" class="chart-axis" />

                            <?php for ($tick = 1; $tick <= 4; $tick++): ?>
                                <?php
                                $ratio = $tick / 4;
                                $tickValue = $averageMaxValue * $ratio;
                                $tickY = $averageAxisBottom - ($averageChartHeight * $ratio);
                                ?>
                                <line x1="<?= $averageAxisLeft; ?>" y1="<?= $tickY; ?>" x2="<?= $averageAxisRight; ?>" y2="<?= $tickY; ?>" class="chart-grid" />
                                <text x="<?= $averageAxisLeft - 10; ?>" y="<?= $tickY + 4; ?>" text-anchor="end" class="chart-tick"><?= Html::encode(number_format($tickValue, 0)); ?></text>
                            <?php endfor; ?>

                            <line x1="<?= $averageAxisLeft; ?>" y1="<?= $averagePassY; ?>" x2="<?= $averageAxisRight; ?>" y2="<?= $averagePassY; ?>" class="chart-threshold" />
                            <text x="<?= $averageAxisLeft + 6; ?>" y="<?= $averagePassY - 6; ?>" class="chart-threshold-label">Pass mark <?= Html::encode(number_format($averagePassThreshold, 0)); ?></text>

                            <text x="<?= $averageAxisLeft - 38; ?>" y="<?= $averagePlotY + ($averagePlotHeight / 2); ?>" class="chart-axis-label" transform="rotate(-90 <?= $averageAxisLeft - 38; ?> <?= $averagePlotY + ($averagePlotHeight / 2); ?>)">Marks</text>
                            <text x="<?= $averageAxisLeft + ($averagePlotWidth / 2); ?>" y="<?= $averageAxisBottom + 36; ?>" class="chart-axis-label">Assessment component</text>

                            <?php foreach ($averageLabels as $index => $label): ?>
                                <?php
                                $value = $averageValues[$index] ?? 0;
                                $barHeight = $averageMaxValue > 0 ? ($value / $averageMaxValue) * $averageChartHeight : 0;
                                $barX = $averageAxisLeft + ($index * ($averageBarWidth + $averageBarSpacing));
                                $barY = $averageAxisBottom - $barHeight;
                                $valueY = $barHeight > 0 ? max($barY - 10, 18) : $averageAxisBottom - 12;
                                $delta = $value - $averagePassThreshold;
                                $deltaLabel = ($delta >= 0 ? '+' : '') . number_format($delta, 1);
                                $subValueY = min($valueY + 12, $averageAxisBottom - 4);
                                ?>
                                <?php if ($barHeight > 0): ?>
                                    <rect x="<?= $barX; ?>" y="<?= $barY + 4; ?>" width="<?= $averageBarWidth; ?>" height="<?= max($barHeight - 2, 0); ?>" class="chart-bar-shadow" rx="10" opacity="0.15" />
                                <?php endif; ?>
                                <rect x="<?= $barX; ?>" y="<?= $barY; ?>" width="<?= $averageBarWidth; ?>" height="<?= $barHeight; ?>" fill="url(#averageBarGradient)" class="chart-bar-secondary" rx="10" />
                                <text x="<?= $barX + ($averageBarWidth / 2); ?>" y="<?= $valueY; ?>" text-anchor="middle" class="chart-value chart-value--marine"><?= Html::encode(number_format($value, 1)); ?></text>
                                <text x="<?= $barX + ($averageBarWidth / 2); ?>" y="<?= $subValueY; ?>" text-anchor="middle" class="chart-subvalue"><?= Html::encode($deltaLabel); ?> vs pass</text>
                                <text x="<?= $barX + ($averageBarWidth / 2); ?>" y="<?= $averageAxisBottom + 22; ?>" text-anchor="middle" class="chart-label"><?= Html::encode($label); ?></text>
                            <?php endforeach; ?>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="panel-card">
                <div class="panel-header">Average score summary</div>
                <div class="panel-body">
                    <table class="table table-sm table-bordered mb-3">
                        <tbody>
                        <tr>
                            <th>Coursework</th>
                            <td class="text-end fw-semibold"><?= Html::encode(number_format((float)$averages['coursework'], 2)); ?></td>
                        </tr>
                        <tr>
                            <th>Exam</th>
                            <td class="text-end fw-semibold"><?= Html::encode(number_format((float)$averages['exam'], 2)); ?></td>
                        </tr>
                        <tr>
                            <th>Final score</th>
                            <td class="text-end fw-semibold"><?= Html::encode(number_format((float)$averages['final'], 2)); ?></td>
                        </tr>
                        </tbody>
                    </table>
                    <p class="notes">
                        Highlight notable performance patterns, flag units requiring academic support, and record any
                        moderation decisions for presentation to the School Academic Board.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="panel-card signatory-card">
        <div class="panel-header">Signatories</div>
        <div class="panel-body">
            <table class="table table-sm table-bordered mb-0">
                <tbody>
                <tr>
                    <td class="signatory-block">
                        <div class="label">Internal Examiner</div>
                        <div class="signature-line"></div>
                        <div class="signatory-name">Name:</div>
                        <div class="signatory-date">Date:</div>
                    </td>
                    <td class="signatory-block">
                        <div class="label">External Examiner</div>
                        <div class="signature-line"></div>
                        <div class="signatory-name">Name:</div>
                        <div class="signatory-date">Date:</div>
                    </td>
                    <td class="signatory-block">
                        <div class="label">Dean/Director</div>
                        <div class="signature-line"></div>
                        <div class="signatory-name">Name:</div>
                        <div class="signatory-date">Date:</div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
