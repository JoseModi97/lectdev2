<?php

/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/**
 * @var yii\web\View $this
 * @var string $title
 * @var array $students
 * @var string $maxStudentCourses
 * @var string $panelHeading
 * @var app\models\StudentConsolidatedMarksFilter $filter
 */

set_time_limit(0);

$this->registerCss(
    <<<CSS
    .grid-header-row th {
        height: 40px; /* Adjust as needed */
        vertical-align: middle;
    }
    .btn-shadow {
        box-shadow: 0 2px 4px rgba(0,0,0,.2); /* Subtle shadow */
    }
    .btn-circle {
        width: 30px; /* Adjust as needed */
        height: 30px; /* Adjust as needed */
        padding: 0; /* Remove padding to allow full height for line-height */
        border-radius: 15px; /* Half of width/height for a circle */
        text-align: center;
        font-size: 14px; /* Adjust icon size */
        line-height: 30px; /* Set line-height equal to height for vertical centering */
    }
    .refresh-grid-button:hover {
        background-color: #4a5a96; /* A lighter shade of the panel background */
        border-color: #4a5a96;
    }
    .panel-title-small {
        font-size: 1.1em; /* Adjust as needed */
    }
    CSS
);

use app\components\SmisHelper;
use app\models\DegreeProgramme;
use app\models\Group;
use app\models\LevelOfStudy;
use app\models\MarksheetDef;
use app\models\TempMarksheet;
use kartik\select2\Select2;
use yii\db\ActiveQuery;
use yii\web\ServerErrorHttpException;

$this->title = $title;

$this->params['breadcrumbs'][] = [
    'label' => 'Consolidated marks per student report filters',
    'url' => ['/shared-reports/student-consolidated-marks-filters', 'level' => $filter->approvalLevel]
];

$this->params['breadcrumbs'][] = $this->title;
?>



<div id="consolidated-marks-per-student-grid" class="grid-view is-bs3 hide-resize">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="panel-title panel-title-small">
                    <?= $panelHeading; ?>
                </h3>
                <button type="button" class="btn btn-primary btn-sm refresh-grid-button btn-shadow btn-circle">
                    <i class="fa fa-refresh" style="color: #333; font-weight: bold;"></i>
                </button>
            </div>

        </div>

        <div id="consolidated-marks-per-student-grid-container" class="table-responsive kv-grid-container">

        <table class="table table-bordered table-hover table-condensed table-striped">
                <?php
                $regNumberColSpan = ($maxStudentCourses * 2) + 3;
                ?>
                <thead>
                    <tr>
                        <th>#</th>
                        <th colspan="<?= $regNumberColSpan ?>">REGISTRATION NUMBER</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rowCount = 1;
                    foreach ($students as $student):
                        $regNumber = $student['REGISTRATION_NUMBER'];
                        $name = $student['SURNAME'] . ' ' . $student['OTHER_NAMES'];

                        /**
                         * Get recommendation and GPA if present
                         */
                        $recommendation = '';
                        $GPA = '';

                        $connection = Yii::$app->getDb();

                        $recommendationQuery = "SELECT ER.GPA, ER.EXT_RESULT, ER.RESULT FROM MUTHONI.EXAM_RESULT ER
                        WHERE ER.REGISTRATION_NUMBER = :regNumber AND ER.LEVEL_OF_STUDY = :studyLevel";

                        $bindParams = [
                            ':regNumber' => $regNumber,
                            ':studyLevel' => $filter->levelOfStudy,
                        ];

                        try {
                            $recommendationResult = $connection->createCommand($recommendationQuery)->bindValues($bindParams)
                                ->queryOne();

                            if (!empty($recommendationResult)) {
                                if (!is_null($recommendationResult['RESULT'])) {
                                    $recommendation = $recommendationResult['RESULT'];
                                }

                                if (!is_null($recommendationResult['EXT_RESULT'])) {
                                    $recommendation .= '. ' . $recommendationResult['EXT_RESULT'];
                                }

                                if (!is_null($recommendationResult['GPA'])) {
                                    $GPA = $recommendationResult['GPA'];
                                }
                            }
                        } catch (\yii\db\Exception $ex) {
                            $message = 'Error while trying to get student recommendation.';
                            if (YII_ENV_DEV) {
                                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
                            }
                            throw new ServerErrorHttpException($message, 500);
                        }

                        /**
                         * Get the highest number of courses registered for by a student
                         */
                        $studentCoursesTotal = 0;
                        try {
                            $studentCoursesTotal = SmisHelper::getMaximumCoursesRegisteredFor($filter, $regNumber);
                        } catch (\yii\db\Exception $ex) {
                            $message = 'Error while trying to get total number of courses registered for a student.';
                            if (YII_ENV_DEV) {
                                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
                            }
                            throw new ServerErrorHttpException($message, 500);
                        }

                        /**
                         * Get all courses registered for by a student
                         */
                        $timetableStartsWith = $filter->academicYear . '_' . $filter->degreeCode . '_' . $filter->levelOfStudy;

                        $studentMarksheets = MarksheetDef::find()->alias('MD')
                            ->select([
                                'MD.MRKSHEET_ID',
                                'MD.COURSE_ID'
                            ])
                            ->joinWith(['course CS' => function (ActiveQuery $q) {
                                $q->select([
                                    'CS.COURSE_ID',
                                    'CS.COURSE_CODE'
                                ]);
                            }], true, 'INNER JOIN')
                            ->joinWith(['marksheet MS' => function (ActiveQuery $q) {
                                $q->select([
                                    'MS.MRKSHEET_ID',
                                    'MS.REGISTRATION_NUMBER'
                                ]);
                            }], true, 'INNER JOIN')
                            ->where(['like', 'MD.MRKSHEET_ID', $timetableStartsWith . '%', false])
                            ->andWhere([
                                'MD.GROUP_CODE' => $filter->group,
                                'MS.REGISTRATION_NUMBER' => $regNumber,
                            ])
                            ->orderBy(['CS.COURSE_CODE' => SORT_ASC])
                            ->asArray()
                            ->all();

                        $courses = [];
                        $totalScore = 0;
                        $averageScore = '';
                        $courseWithMarks = 0;
                        foreach ($studentMarksheets as $studentMarksheet) {
                            $studentMarks = TempMarksheet::find()->select(['GRADE', 'FINAL_MARKS'])
                                ->where([
                                    'MRKSHEET_ID' => $studentMarksheet['MRKSHEET_ID'],
                                    'REGISTRATION_NUMBER' => $regNumber
                                ])
                                ->one();

                            $grade = '';
                            $finalMarks = '';

                            if ($studentMarks) {
                                if ($studentMarks->GRADE) {
                                    $grade = $studentMarks->GRADE;
                                }

                                if ($studentMarks->FINAL_MARKS) {
                                    $finalMarks = $studentMarks->FINAL_MARKS;
                                    $totalScore += $finalMarks;
                                    $courseWithMarks++;
                                }
                            }

                            $courses[] = [
                                'marksheetId' => $studentMarksheet['MRKSHEET_ID'],
                                'code' => $studentMarksheet['course']['COURSE_CODE'],
                                'grade' => $grade,
                                'finalMarks' => $finalMarks
                            ];

                            if ($courseWithMarks > 0) {
                                $averageScore = $totalScore / $courseWithMarks;
                            }
                        }
                    ?>

                        <tr>
                            <td rowspan="3"> <b><?= $rowCount; ?> </b></td>
                            <td colspan="<?= $regNumberColSpan; ?>"><b>
                                    <?= $regNumber . ' ' . $name . ' | RECOM: ' . $recommendation; ?>
                                </b>
                            </td>
                        </tr>

                        <tr>
                            <?php
                            /**
                             * The maximum number of columns we need to display a student's grades and marks, is
                             * double the courses registered for by the student + 1. Therefore, the maximum number of
                             * these columns will be double the highest number of courses registered for by students
                             * in the timetable + 1.
                             */
                            $columnSpans = [];
                            $maxCols = ($maxStudentCourses * 2) + 1;
                            $extraSizeCols = fmod($maxCols, $studentCoursesTotal);
                            if ($extraSizeCols > 0):
                                $normalSizeCols = $studentCoursesTotal - $extraSizeCols;
                                $normalSizeColsSpan = ($maxCols - $extraSizeCols) / $studentCoursesTotal;
                                $extraSizeColsSpan = $normalSizeColsSpan + 1;
                            ?>

                                <?php for ($i = 0; $i < $normalSizeCols; $i++):

                                    /**
                                     * Store the sizes of each course code column to be shared between the course marks
                                     * and grade columns in the table row after this.
                                     */
                                    $columnSpans[] = $normalSizeColsSpan;
                                ?>
                                    <td colspan="<?= $normalSizeColsSpan; ?>"> <?= $courses[$i]['code']; ?></td>
                                <?php endfor; ?>

                                <?php for ($j = 0; $j < $extraSizeCols; $j++):
                                    $columnSpans[] = $extraSizeColsSpan;
                                ?>
                                    <td colspan="<?= $extraSizeColsSpan; ?>"> <?= $courses[$j + $normalSizeCols]['code']; ?></td>
                                <?php endfor; ?>

                            <?php else:
                                $normalSizeColsSpan = $maxCols / $studentCoursesTotal;
                            ?>

                                <?php for ($i = 0; $i < $studentCoursesTotal; $i++):
                                    $columnSpans[] = $normalSizeColsSpan;
                                ?>
                                    <td colspan="<?= $normalSizeColsSpan; ?>"> <?= $courses[$i]['code']; ?></td>
                                <?php endfor; ?>

                            <?php endif; ?>

                            <td> # UNITS </td>
                            <td> GPA </td>
                        </tr>

                        <tr>
                            <?php
                            /**
                             * If the column span size is an even, the size iss shared equally between the course marks
                             * and grade columns. Else the course marks column takes the extra size.
                             */
                            for ($i = 0; $i < $studentCoursesTotal; $i++):
                                $marksColSpan = $columnSpans[$i] / 2;
                                $gradeColSpan = $marksColSpan;
                                if ($columnSpans[$i] % 2 !== 0) {
                                    $marksColSpan = (($columnSpans[$i] - 1) / 2) + 1;
                                    $gradeColSpan = (($columnSpans[$i] - 1) / 2);
                                }
                            ?>
                                <td colspan="<?= $marksColSpan ?>"> <?= $courses[$i]['finalMarks']; ?></td>
                                <td colspan="<?= $gradeColSpan ?>"> <?= $courses[$i]['grade']; ?></td>
                            <?php endfor; ?>

                            <td> <?= $studentCoursesTotal; ?> </td>
                            <td> <?= $GPA; ?> </td>
                        </tr>
                    <?php
                        $rowCount++;
                    endforeach;
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Student Details Modal -->
<div class="modal fade" id="studentDetailsModal" tabindex="-1" role="dialog" aria-labelledby="studentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <!-- Content will be loaded here via AJAX -->
        </div>
    </div>
</div>

<?php
$getStudentDetailsUrl = \yii\helpers\Url::to(['/shared-reports/get-student-details']);
$js = <<<JS
$(document).on('click', '.view-student-details', function() {
    var button = $(this);
    var regNumber = button.data('reg-number');
    var academicYear = button.data('academic-year');
    var degreeCode = button.data('degree-code');
    var levelOfStudy = button.data('level-of-study');
    var group = button.data('group');

    $('#studentDetailsModal').modal('show');
    $('#studentDetailsModal .modal-content').html('<div class="modal-body text-center"><i class="fa fa-spinner fa-spin fa-3x"></i><p class="mt-3">Loading student details...</p></div>');

    console.log('AJAX URL:', '{$getStudentDetailsUrl}');
    $.ajax({
        url: '{$getStudentDetailsUrl}',
        type: 'GET',
        data: {
            regNumber: regNumber,
            academicYear: academicYear,
            degreeCode: degreeCode,
            levelOfStudy: levelOfStudy,
            group: group
        },
        success: function(response) {
            $('#studentDetailsModal .modal-content').html(response);
        },
        error: function(xhr, status, error) {
            $('#studentDetailsModal .modal-content').html('<div class="modal-header"><h5 class="modal-title">Error</h5><button type="button" class="close custom-modal-close" aria-label="Close"><span aria-hidden="true" style="color: white; font-weight: bold;">&times;</span></button></div><div class="modal-body"><p>An error occurred while loading student details: ' + xhr.responseText + '</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary custom-modal-close">Close</button></div>');
        }
    });
});

$(document).on('click', '.custom-modal-close', function() {
    $('#studentDetailsModal').modal('hide');
});

$(document).on('click', '.refresh-grid-button', function() {
    window.parent.$('#course-analysis-filters-form').submit();
});

$('#student-reg-filter').on('change', function() {
    var selectedRegNumber = $(this).val();
    var filterForm = window.parent.$('#course-analysis-filters-form');
    var currentData = filterForm.serializeArray();
    var found = false;

    // Update or add registrationNumber to form data
    for (var i = 0; i < currentData.length; i++) {
        if (currentData[i].name === 'registrationNumber') {
            currentData[i].value = selectedRegNumber;
            found = true;
            break;
        }
    }
    if (!found) {
        currentData.push({name: 'registrationNumber', value: selectedRegNumber});
    }

    // Remove registrationNumber if selectedRegNumber is empty
    if (!selectedRegNumber) {
        currentData = currentData.filter(function(item) {
            return item.name !== 'registrationNumber';
        });
    }

    $.ajax({
        url: window.parent.studentConsolidatedMarksUrls.consolidatedMarks, // Use the correct URL for the grid partial
        type: 'GET',
        data: $.param(currentData), // Serialize the array back to a string
        beforeSend: function() {
            $('#consolidated-marks-container').html('<div class="d-flex justify-content-center py-5"><div class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i><p class="mt-3 mb-0 text-muted">Loading consolidated marks…</p></div></div>');
        },
        success: function(response) {
            $('#consolidated-marks-container').html(response);
        },
        error: function() {
            $('#consolidated-marks-container').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
        }
    });
});

$(window).on('beforeunload', function(){
    return 'Are you sure you want to exit? Your selected filters will be erased.';
});
JS;
$this->registerJs($js);
?>