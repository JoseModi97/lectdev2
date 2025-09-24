<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $assessmentsDataProvider
 * @var string $title
 * @var string $panelHeading
 * @var array|null $reportDetails
 */

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JqueryAsset;

$this->title = $title;
$this->params['breadcrumbs'][] = [
    'label' => 'Grade analysis',
    'url' => ['/shared-reports/course-analysis']
];
$this->params['breadcrumbs'][] = $this->title;

$assessments = $assessmentsDataProvider->getModels();
$formatter = Yii::$app->formatter;

$reportSummary = [];
$courseHeading = $panelHeading;
$filenameParts = [];

if (!empty($reportDetails)) {
    $reportSummary = [
        'Academic Year' => $reportDetails['academicYear'] ?? '—',
        'Programme' => ($reportDetails['degreeName'] ?? '—') . (!empty($reportDetails['degreeCode']) ? ' (' . $reportDetails['degreeCode'] . ')' : ''),
        'Level of Study' => !empty($reportDetails['level']) ? strtoupper($reportDetails['level']) : '—',
        'Semester' => $reportDetails['semesterFullName'] ?? '—',
        'Group' => !empty($reportDetails['group']) ? strtoupper($reportDetails['group']) : '—',
    ];
    $courseHeading = ($reportDetails['courseName'] ?? $panelHeading) . (!empty($reportDetails['courseCode']) ? ' (' . $reportDetails['courseCode'] . ')' : '');
    $filenameParts = [
        $reportDetails['academicYear'] ?? null,
        $reportDetails['degreeCode'] ?? null,
        $reportDetails['courseCode'] ?? null,
        'assessments_report'
    ];
}

$assessmentRows = [];
$totalWeight = 0;
$dueDatesSet = 0;
foreach ($assessments as $assessment) {
    $rawName = $assessment['assessmentType']['ASSESSMENT_NAME'] ?? '';
    if (stripos($rawName, 'EXAM_COMPONENT') !== false) {
        $rawName = str_replace('EXAM_COMPONENT', '', $rawName);
    }

    $weight = $assessment['WEIGHT'] ?? null;
    if (is_numeric($weight)) {
        $totalWeight += (float)$weight;
    }

    $dueDate = $assessment['RESULT_DUE_DATE'] ?? null;
    if (!empty($dueDate)) {
        $dueDatesSet++;
    }

    $assessmentRows[] = [
        'id' => $assessment['ASSESSMENT_ID'],
        'name' => trim($rawName),
        'description' => $assessment['assessmentType']['ASSESSMENT_DESCRIPTION'] ?? '',
        'weight' => $weight,
        'divider' => $assessment['DIVIDER'] ?? null,
        'dueDate' => $dueDate,
    ];
}

$filenameParts = array_filter($filenameParts, static fn($value) => !empty($value));
$filename = strtolower(str_replace([' ', '/', '\\'], '_', implode('_', $filenameParts)));
if ($filename === '') {
    $filename = 'course_assessments_report';
}

$user = Yii::$app->user->identity;
$printedByParts = array_filter([
    $user->EMP_TITLE ?? null,
    $user->SURNAME ?? null,
    $user->OTHER_NAMES ?? null,
]);
$printedBy = !empty($printedByParts) ? implode(' ', $printedByParts) : ($user->username ?? 'User');
$printedAt = $formatter->asDatetime('now', 'php:d M Y H:i');

if (!empty($assessmentRows)) {
    $this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js', [
        'depends' => [JqueryAsset::class],
    ]);

    $encodedFilename = Json::htmlEncode($filename);
    $downloadScript = <<<JS
    function downloadAssessmentsReport() {
        const reportContainer = document.getElementById('course-assessments-report');
        if (!reportContainer) {
            return;
        }

        reportContainer.classList.add('print-mode');
        const worker = html2pdf().set({
            margin: [12, 12, 12, 12],
            filename: {$encodedFilename},
            pagebreak: { mode: ['css', 'legacy'] },
            jsPDF: {
                orientation: 'p',
                unit: 'mm',
                format: 'a4',
                putOnlyUsedFonts: true,
                floatPrecision: 16
            }
        }).from(reportContainer);

        worker.save().then(() => {
            reportContainer.classList.remove('print-mode');
        }).catch(() => {
            reportContainer.classList.remove('print-mode');
        });
    }
JS;
    $this->registerJs($downloadScript);
}

$this->registerCss(<<<CSS
.course-assessments-report-table th,
.course-assessments-report-table td {
    font-size: 0.85rem;
    vertical-align: middle;
}

.course-assessments-report-table td small {
    color: #6c757d;
}

#course-assessments-report.print-mode .assessment-action,
#course-assessments-report.print-mode .assessment-action-header {
    display: none !important;
}
CSS);
?>

<div class="course-assessments container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 class="h4 mb-1"><?= Html::encode($this->title); ?></h2>
            <p class="text-muted mb-0 small">
                <?= Html::encode($courseHeading); ?>
            </p>
        </div>
        <?php if (!empty($assessmentRows)): ?>
            <button type="button" onclick="downloadAssessmentsReport()" id="download-assessments-report" class="btn btn-primary">
                Download report
            </button>
        <?php endif; ?>
    </div>

    <div id="course-assessments-report" class="bg-white rounded-3 shadow-sm p-3 p-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 small text-uppercase">
                    <span class="fw-semibold">University of Nairobi</span>
                    <span><?= Html::encode($reportDetails['courseCode'] ?? $courseHeading); ?></span>
                    <span>Printed by <?= Html::encode($printedBy); ?></span>
                    <span><?= Html::encode($printedAt); ?></span>
                </div>
            </div>
            <?php if (!empty($reportSummary)): ?>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($reportSummary as $label => $value): ?>
                            <div class="col-sm-6 col-lg-4">
                                <div class="text-muted text-uppercase small fw-semibold"><?= Html::encode($label); ?></div>
                                <div class="fw-semibold text-dark"><?= Html::encode($value); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($assessmentRows)): ?>
            <div class="alert alert-warning mb-0">
                No assessments have been defined for this course marksheet.
            </div>
        <?php else: ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row row-cols-1 row-cols-sm-3 g-3 mb-3">
                        <div class="col">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="text-muted text-uppercase small fw-semibold">Assessments</div>
                                <div class="fs-5 fw-semibold"><?= Html::encode(count($assessmentRows)); ?></div>
                                <div class="text-muted small mb-0">Total entries available</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="text-muted text-uppercase small fw-semibold">Weight coverage</div>
                                <div class="fs-5 fw-semibold"><?= Html::encode($formatter->asDecimal($totalWeight, 2)); ?>%</div>
                                <div class="text-muted small mb-0">Combined coursework weight</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="text-muted text-uppercase small fw-semibold">Due dates set</div>
                                <div class="fs-5 fw-semibold"><?= Html::encode($dueDatesSet); ?> / <?= Html::encode(count($assessmentRows)); ?></div>
                                <div class="text-muted small mb-0">Assessments with published deadlines</div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle course-assessments-report-table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-muted text-uppercase small">#</th>
                                    <th scope="col" class="text-muted text-uppercase small">Assessment</th>
                                    <th scope="col" class="text-muted text-uppercase small">Description</th>
                                    <th scope="col" class="text-muted text-uppercase small text-end">Weight (%)</th>
                                    <th scope="col" class="text-muted text-uppercase small text-end">Divider</th>
                                    <th scope="col" class="text-muted text-uppercase small">Result due date</th>
                                    <th scope="col" class="text-muted text-uppercase small assessment-action-header">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assessmentRows as $index => $assessment): ?>
                                    <?php
                                        $weightDisplay = $assessment['weight'] !== null && $assessment['weight'] !== ''
                                            ? $formatter->asDecimal($assessment['weight'], 2) . '%'
                                            : '—';
                                        $dividerDisplay = $assessment['divider'] !== null && $assessment['divider'] !== ''
                                            ? $formatter->asInteger($assessment['divider'])
                                            : '—';
                                        $dueDateDisplay = !empty($assessment['dueDate'])
                                            ? $formatter->asDate($assessment['dueDate'], 'php:d M Y')
                                            : 'Not set';
                                        $description = $assessment['description'] !== ''
                                            ? $assessment['description']
                                            : '—';
                                    ?>
                                    <tr>
                                        <td><?= Html::encode($index + 1); ?></td>
                                        <td class="fw-semibold"><?= Html::encode($assessment['name']); ?></td>
                                        <td><?= Html::encode($description); ?></td>
                                        <td class="text-end"><?= Html::encode($weightDisplay); ?></td>
                                        <td class="text-end"><?= Html::encode($dividerDisplay); ?></td>
                                        <td><?= Html::encode($dueDateDisplay); ?></td>
                                        <td class="assessment-action">
                                            <?= Html::a(
                                                '<i class="fas fa-eye"></i> Missing marks',
                                                Url::to(['/shared-reports/missing-marks', 'assessmentId' => $assessment['id']]),
                                                [
                                                    'class' => 'btn btn-sm btn-outline-primary',
                                                    'title' => 'Open missing marks report'
                                                ]
                                            ); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light fw-semibold text-uppercase small">Signatories</div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="text-muted text-uppercase small fw-semibold">Internal Examiner</div>
                            <p class="mb-1">Signature: ________________________________</p>
                            <p class="mb-0">Date: _____________________________________</p>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted text-uppercase small fw-semibold">External Examiner</div>
                            <p class="mb-1">Signature: ________________________________</p>
                            <p class="mb-0">Date: _____________________________________</p>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted text-uppercase small fw-semibold">Dean/Director</div>
                            <p class="mb-1">Signature: ________________________________</p>
                            <p class="mb-0">Date: _____________________________________</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
