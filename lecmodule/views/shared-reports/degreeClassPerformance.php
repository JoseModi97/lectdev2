<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var string $title
 * @var string[] $reportDetails
 * @var string $user
 * @var string $date
 */

use yii\helpers\Html;
use yii\helpers\Url;

require_once Yii::getAlias('@views') . '/shared-reports/classPerfomanceHelpers.php';

$reportSummary = [
    'Academic Year' => $reportDetails['academicYear'],
    'Programme' => $reportDetails['degreeName'] . ' (' . $reportDetails['degreeCode'] . ')',
    'Level of Study' => strtoupper($reportDetails['level']),
    'Semester' => $reportDetails['semesterFullName'],
    'Group' => strtoupper($reportDetails['group']),
];
?>

<div class="class-performance-analysis container-fluid py-3">
    <p id="marksheetId" hidden><?= Html::encode($reportDetails['marksheetId']); ?></p>
    <div class="class-performance-statistics d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 class="h4 mb-1">Class Performance Report</h2>
            <p class="text-muted mb-0 small">
                <?= Html::encode($reportDetails['courseCode']); ?> · <?= Html::encode($reportDetails['degreeName']); ?>
            </p>
        </div>
        <a href="<?= Url::to(['/shared-reports/class-performance-download', 'marksheetId' => $reportDetails['marksheetId']]); ?>"
           id="download-analysis-report" class="btn btn-primary" target="_blank" rel="noopener">
            Download report
        </a>
    </div>

    <div id="class-performance-report" class="bg-white rounded-3 shadow-sm p-3 p-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 small text-uppercase">
                    <span class="fw-semibold">University of Nairobi</span>
                    <span><?= Html::encode($reportDetails['courseCode']); ?></span>
                    <span>Printed by <?= Html::encode($user); ?></span>
                    <span><?= Html::encode($date); ?></span>
                </div>
            </div>
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
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-5 col-xl-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-light fw-semibold text-uppercase small">Grade distribution</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="grading-system-table" class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="text-uppercase small text-muted">% Range</th>
                                        <th scope="col" class="text-uppercase small text-muted">Grade</th>
                                        <th scope="col" class="text-uppercase small text-muted text-end"># of students</th>
                                    </tr>
                                </thead>
                                <tbody id="grading-system-tbody">
                                    <tr>
                                        <td>70 - 100</td>
                                        <td>A</td>
                                        <td class="text-end" id="grade-A-count"></td>
                                    </tr>
                                    <tr>
                                        <td>60 - 69.99</td>
                                        <td>B</td>
                                        <td class="text-end" id="grade-B-count"></td>
                                    </tr>
                                    <tr>
                                        <td>50 - 59.99</td>
                                        <td>C</td>
                                        <td class="text-end" id="grade-C-count"></td>
                                    </tr>
                                    <tr>
                                        <td>40 - 49.99</td>
                                        <td>D</td>
                                        <td class="text-end" id="grade-D-count"></td>
                                    </tr>
                                    <tr>
                                        <td>0 - 39.99</td>
                                        <td>E</td>
                                        <td class="text-end" id="grade-E-count"></td>
                                    </tr>
                                    <tr>
                                        <td>--</td>
                                        <td>E*</td>
                                        <td class="text-end" id="grade-E-star-count"></td>
                                    </tr>
                                    <tr>
                                        <td>Not Graded</td>
                                        <td>X</td>
                                        <td class="text-end" id="grade-X-count"></td>
                                    </tr>
                                    <tr class="table-light">
                                        <td>Class size</td>
                                        <td></td>
                                        <td class="text-end fw-semibold" id="students-count"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-xl-4">
                <div class="card h-100 shadow-sm text-center">
                    <div class="card-header bg-light fw-semibold text-uppercase small">Grade distribution chart</div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div class="w-100" style="max-width: 320px;">
                            <div id="grade-distribution-loader" class="py-4"></div>
                            <canvas id="grade-distribution" class="w-100" height="220"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xl-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-light fw-semibold text-uppercase small">Key figures</div>
                    <div class="card-body">
                        <p class="mb-2 text-muted small">Quick reference for print and review.</p>
                        <ul class="list-unstyled mb-0 small">
                            <li class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted text-uppercase">Highest grade</span>
                                <span class="fw-semibold">A</span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted text-uppercase">Lowest grade</span>
                                <span class="fw-semibold">E / X</span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center">
                                <span class="text-muted text-uppercase">Report date</span>
                                <span class="fw-semibold"><?= Html::encode($date); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-6 col-xl-5">
                <div class="card h-100 shadow-sm text-center">
                    <div class="card-header bg-light fw-semibold text-uppercase small">Average scores chart</div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div class="w-100" style="max-width: 320px;">
                            <div id="average-scores-loader" class="py-4"></div>
                            <canvas id="average-scores" class="w-100" height="220"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-xl-4 page-break">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-light fw-semibold text-uppercase small">Average scores summary</div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted text-uppercase small">Coursework</span>
                                <span class="fw-semibold" id="avg-cw-score"></span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted text-uppercase small">Exam</span>
                                <span class="fw-semibold" id="avg-exam-score"></span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center">
                                <span class="text-muted text-uppercase small">Final</span>
                                <span class="fw-semibold" id="avg-final-score"></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 col-xl-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-light fw-semibold text-uppercase small">Notes</div>
                    <div class="card-body">
                        <p class="mb-0 small text-muted">
                            Use this report to highlight key trends and share quick talking points before meetings or when exporting the PDF.
                        </p>
                    </div>
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
    </div>
</div>

<?php
$loadSpin = '<h1 class="text-center text-primary" style="font-size: 100px;"><i class="fas fa-spinner fa-pulse"></i></h1>';
$urlGetClassPerfomance = Url::to(['/shared-reports/class-performance-stats']);

$analysisScript = <<< JS
    const loadDefault = '$loadSpin';
    $('#grade-distribution-loader').html(loadDefault);
    $('#average-scores-loader').html(loadDefault);
    const urlGetClassPerfomance = '$urlGetClassPerfomance';
    let marksheetId = $('#marksheetId').text();

    /**
     * check existence of a grade
     * @param string grade
     * @param array list
     * 
     * @return bool true | false
     */
    function containsGrade(grade, list){
        let i;
        for(i = 0; i < list.length; i++){
            if(list[i].GRADE === grade) return true;
        }
        return false;
    }

    /**
     * count the number of existing grade
     * @param string grade
     * @param array list
     * 
     * @return string count of the grade
     */
    function gradeCount(grade, list){
        let i;
        for(i = 0; i < list.length; i++){
            if(list[i].GRADE === grade) return list[i].cnt;
        }
    }
    
    $.ajax({
        type        :   'GET',
        url         :   urlGetClassPerfomance+'?marksheetId='+marksheetId,
        dataType    :   'json',
    })
    .done(function(classStats){
        if(classStats.status){
            $('#grade-distribution-loader').html('');
            $('#average-scores-loader').html(''); 
            $('#avg-cw-score').text(classStats.avgCourseMarks);
            $('#avg-exam-score').text(classStats.avgExamMarks);
            $('#avg-final-score').text(classStats.avgFinalMarks);
            let grades = classStats.gradesDistribution;
            let gradeDistributionCtx = $('#grade-distribution');
            let averageSCoresCtx = $('#average-scores');

            let gradeA = "A     ";
            let gradeB = "B     ";
            let gradeC = "C     ";
            let gradeD = "D     ";
            let gradeE = "E     ";
            let gradeEStar = "E*    ";
            let gradeX = null;

            let gradeACount;
            let gradeBCount;
            let gradeCCount;
            let gradeDCount;
            let gradeECount;
            let gradeEStarCount;
            let gradeXCount;

            if(containsGrade(gradeA, grades)) gradeACount = gradeCount(gradeA, grades)
            else gradeACount = 0;

            if(containsGrade(gradeB, grades)) gradeBCount = gradeCount(gradeB, grades)
            else gradeBCount = 0;

            if(containsGrade(gradeC, grades)) gradeCCount = gradeCount(gradeC, grades)
            else gradeCCount = 0;

            if(containsGrade(gradeD, grades)) gradeDCount = gradeCount(gradeD, grades)
            else gradeDCount = 0;

            if(containsGrade(gradeE, grades)) gradeECount = gradeCount(gradeE, grades)
            else gradeECount = 0;
            
            if(containsGrade(gradeEStar, grades)) gradeEStarCount = gradeCount(gradeEStar, grades)
            else gradeEStarCount = 0;

            if(containsGrade(gradeX, grades)) gradeXCount = gradeCount(gradeX, grades)
            else gradeXCount = 0;
            
            $('#grade-A-count').text(gradeACount);
            $('#grade-B-count').text(gradeBCount);
            $('#grade-C-count').text(gradeCCount);
            $('#grade-D-count').text(gradeDCount);
            $('#grade-E-count').text(gradeECount);
            $('#grade-E-star-count').text(gradeEStarCount);
            $('#grade-X-count').text(gradeXCount);
            $('#students-count').text(parseInt(classStats.totalStudents));

            var gradeDistributionChart = new Chart(gradeDistributionCtx, {
                type: 'bar',
                data: {
                    labels: [
                        gradeA,
                        gradeB,
                        gradeC,
                        gradeD,
                        gradeE,
                        gradeEStar,
                        'X',
                        'TOTAL'
                    ],
                    datasets: [{
                        label: 'GRADE DISTRIBUTION',
                        data: [
                            gradeACount,
                            gradeBCount,
                            gradeCCount,
                            gradeDCount,
                            gradeECount,
                            gradeEStarCount,
                            gradeXCount,
                            parseInt(classStats.totalStudents)
                        ],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)',
                            'rgba(255, 159, 64, 0.2)',
                            'rgba(75, 192, 192, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)',
                            'rgba(255, 159, 64, 1)',
                            'rgba(54, 162, 235, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        xAxes: [{
                            scaleLabel: {
                                display: true,
                                labelString: 'GRADES'
                            },
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            },
                            scaleLabel: {
                                display: true,
                                labelString: '# OF STUDENTS'
                            }
                        }]
                    }
                }
            });

            var averageSCoresChart = new Chart(averageSCoresCtx, {
                type: 'horizontalBar',
                data: {
                    labels: ['COURSEWORK', 'EXAM', 'FINAL'],
                    datasets: [{
                        label: 'AVERAGE SCORE',
                        data: [classStats.avgCourseMarks, classStats.avgExamMarks, classStats.avgFinalMarks],
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        xAxes: [{  
                            scaleLabel: {
                                display: true,
                                labelString: 'STUDENT MARKS'
                            },
                            ticks: {
                                beginAtZero: true
                            }
                        }],
                        yAxes: [{
                            stacked: true
                        }]
                    }
                }
            });
        }
        else{
            alert('Couldn\'t fetch class perfomance analysis data at the moment.');
        }
    })
    .fail(function(data){});

JS;
$this->registerJs($analysisScript, yii\web\View::POS_END);
