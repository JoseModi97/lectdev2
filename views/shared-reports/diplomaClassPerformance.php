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

use yii\helpers\Url;

require_once Yii::getAlias('@views') . '/shared-reports/classPerfomanceHelpers.php';
?>

<div class="class-performance-analysis">
    <div class ="class-performance-statistics">
        <p id ="marksheetId" hidden><?= $reportDetails['marksheetId']; ?></p>
        <button type="button" onclick="generatePDF()" id="generate-analysis-pdf" class="btn btn-primary">
            Download report
        </button>
    </div>

    <div id="class-performance-report" style="padding-top: 10px;">
        <div style="padding: 10px;">
            <p class="bg-primary" style="padding: 10px;" style="color:#333333; font-weight: bold">
                University of Nairobi | <?= $reportDetails['courseCode']; ?> | Printed by <?= $user; ?> | <?= $date; ?>
            </p>

            <div>
                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        <?php
                        $contentBefore = '<p style="color:#333333; font-weight: bold;">ACADEMIC YEAR: ' . $reportDetails['academicYear'] . '</p>';
                        $contentBefore .= '<p style="color:#333333; font-weight: bold;">PROGRAMME: ' . $reportDetails['degreeName'] . ' (' . $reportDetails['degreeCode'] . ') </p>';
                        $contentBefore .= '<p style="color:#333333; font-weight: bold;">LEVEL OF STUDY: ' . strtoupper($reportDetails['level']) . '</p>';
                        $contentBefore .= '<p style="color:#333333; font-weight: bold;">SEMESTER: ' . $reportDetails['semesterFullName'] . '</p>';
                        $contentBefore .= '<p style="color:#333333; font-weight: bold;">GROUP: ' . strtoupper($reportDetails['group']) . '</p>';
                        echo $contentBefore;
                        ?>
                    </div>
                </div>

                <br><br>

                <div class="row" style="padding-top: 10px;">
                    <div class="col-lg-4 col-md-4">
                        <table id ="grading-system-table" class="table table-hover">
                            <thead>
                            <tr>
                                <th scope="col">% RANGE</th>
                                <th scope="col">GRADE</th>
                                <th scope="col"># OF STUDENTS</th>
                            </tr>
                            </thead>
                            <tbody id="grading-system-tbody">
                            <tr class="table-info">
                                <td>70 - 100</td>
                                <td>A</td>
                                <td id="grade-A-count"></td>
                            </tr>
                            <tr class="table-info">
                                <td>56 - 69.99</td>
                                <td>B</td>
                                <td id="grade-B-count"></td>
                            </tr>
                            <tr class="table-info">
                                <td>40 - 55.99</td>
                                <td>C</td>
                                <td id="grade-C-count"></td>
                            </tr>
                            <tr class="table-info">
                                <td>0 - 39.99</td>
                                <td>D</td>
                                <td id="grade-D-count"></td>
                            </tr>
                            <tr class="table-info">
                                <td>--</td>
                                <td>E*</td>
                                <td id="grade-E-star-count"></td>
                            </tr>
                            <tr>
                                <td>Not Graded</td>
                                <td>X</td>
                                <td id="grade-X-count"></td>
                            </tr>
                            <tr>
                                <td>Class size</td>
                                <td></td>
                                <td id="students-count"></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div id ="grade-distribution-loader">
                        </div>
                        <canvas id="grade-distribution" width="100" height="100"></canvas>
                    </div>
                    <div class="col-lg-4 col-md-4 "></div>
                </div>

                <br><br>

                <div class="row">
                    <div class="col-lg-4 col-md-4">
                        <div id ="average-scores-loader"></div>
                        <canvas id="average-scores" width="100" height="100"></canvas>
                    </div>
                    <div class="col-lg-4 col-md-4 page-break" style="padding-top: 10px;">
                        <h4>Average scores summary: </h4><hr>
                        <P>Coursework: <span id="avg-cw-score"></span></P>
                        <P>Exam: <span id="avg-exam-score"></span></P>
                        <P>Final: <span id="avg-final-score"></span></P>
                    </div>
                    <div class="col-lg-4 col-md-4"></div>
                </div>

                <br><br>

                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        <P>Internal Examiner    (Signature) ......................................... (Date) ...........................</P><br><br>
                        <P>External Examiner    (Signature) ......................................... (Date) ...........................</P><br><br>
                        <P>Dean/Director        (Signature) ......................................... (Date) ...........................</P>
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
            let gradeEStar = "E*    ";
            let gradeX = null;

            let gradeACount;
            let gradeBCount;
            let gradeCCount;
            let gradeDCount;
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
            
            if(containsGrade(gradeEStar, grades)) gradeEStarCount = gradeCount(gradeEStar, grades)
            else gradeEStarCount = 0;

            if(containsGrade(gradeX, grades)) gradeXCount = gradeCount(gradeX, grades)
            else gradeXCount = 0;
            
            $('#grade-A-count').text(gradeACount);
            $('#grade-B-count').text(gradeBCount);
            $('#grade-C-count').text(gradeCCount);
            $('#grade-D-count').text(gradeDCount);
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
                            gradeEStarCount,
                            gradeXCount,
                            parseInt(classStats.totalStudents)
                        ],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(255, 159, 64, 0.2)',
                            'rgba(255, 159, 64, 0.2)',
                            'rgba(75, 192, 192, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
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

    /** Generate report in pdf format */
    function generatePDF() {
        let element = document.getElementById("class-performance-report");
        let options = {
            margin: [10, 10, 0, 10],
            filename : 'class_performance_report',
            pagebreak: {
                mode: 'css',
                before: '.page-break'
            },
            jsPDF: {
                orientation: 'p',
                unit: 'mm',
                format: 'a4',
                putOnlyUsedFonts:true,
                floatPrecision: 16 // or "smart", default is 16
            }
        }
        html2pdf().set(options).from(element).save();
    }
JS;
$this->registerJs($analysisScript, yii\web\View::POS_END);
