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
                    <?php
                    $averageMax = max(100, (float)$averages['coursework'], (float)$averages['exam'], (float)$averages['final']);
                    $averageItems = [
                        ['label' => 'Coursework', 'value' => (float)$averages['coursework']],
                        ['label' => 'Exam', 'value' => (float)$averages['exam']],
                        ['label' => 'Final Score', 'value' => (float)$averages['final']],
                    ];
                    ?>
                    <?php foreach ($averageItems as $item): ?>
                        <?php $percent = $averageMax > 0 ? round(($item['value'] / $averageMax) * 100, 1) : 0; ?>
                        <div class="bar-row">
                            <div class="bar-label"><?= Html::encode($item['label']); ?></div>
                            <div class="bar-percent"><?= Html::encode(number_format($item['value'], 2)); ?></div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width: <?= (float)min(100, $percent); ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
