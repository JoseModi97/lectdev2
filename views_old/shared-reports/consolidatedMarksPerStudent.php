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

use app\components\SmisHelper;
use app\models\MarksheetDef;
use app\models\TempMarksheet;
use yii\db\ActiveQuery;
use yii\web\ServerErrorHttpException;

$this->title = $title;

$this->params['breadcrumbs'][] = [
    'label' => 'Consolidated marks per student report filters',
    'url' => ['/shared-reports/student-consolidated-marks-filters', 'level' => $filter->approvalLevel]
];

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading"><b>Please note the following</b></div>
            <div class="panel-body">
                <p>
                    This consolidated marksheet is provisional and can be tabled in the examiners committees for approval.
                    The final consolidated marksheet with approved results should be generated from SMIS.
                </p>
            </div>
        </div>
    </div>
</div>

<?php
echo $this->render('consolidatedMarksPerStudentActiveFilters', ['filter' => $filter]);
?>

<div id="consolidated-marks-per-student-grid" class="grid-view is-bs3 hide-resize">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <div class="pull-right">
                <div class="summary">
                    Showing <b><?= count($students); ?></b> of <b><?= count($students); ?></b> students.
                </div>
            </div>
            <h3 class="panel-title">
                <?= $panelHeading; ?>
            </h3>
        </div>

        <div id="consolidated-marks-per-student-grid-container" class="table-responsive kv-grid-container">
            <table class="table table-bordered table-hover table-condensed table-striped">
                <?php
                $regNumberColSpan = ($maxStudentCourses * 2) + 3;
                ?>
                <thead>
                <tr>
                    <th>#</th>
                    <th colspan="<?= $regNumberColSpan?>">REGISTRATION NUMBER</th>
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

                        if(!empty($recommendationResult)){
                            if(!is_null($recommendationResult['RESULT'])){
                                $recommendation = $recommendationResult['RESULT'];
                            }

                            if(!is_null($recommendationResult['EXT_RESULT'])){
                                $recommendation .= '. ' . $recommendationResult['EXT_RESULT'];
                            }

                            if(!is_null($recommendationResult['GPA'])){
                                $GPA = $recommendationResult['GPA'];
                            }
                        }
                    } catch (\yii\db\Exception $ex) {
                        $message = 'Error while trying to get student recommendation.';
                        if(YII_ENV_DEV){
                            $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
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
                        if(YII_ENV_DEV){
                            $message = $ex->getMessage().' File: '.$ex->getFile().' Line: '.$ex->getLine();
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
                        ->joinWith(['course CS' => function(ActiveQuery $q){
                            $q->select([
                                'CS.COURSE_ID',
                                'CS.COURSE_CODE'
                            ]);
                        }], true, 'INNER JOIN')
                        ->joinWith(['marksheet MS' => function(ActiveQuery $q){
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
                    foreach ($studentMarksheets as $studentMarksheet){
                        $studentMarks = TempMarksheet::find()->select(['GRADE', 'FINAL_MARKS'])
                            ->where([
                                'MRKSHEET_ID' => $studentMarksheet['MRKSHEET_ID'],
                                'REGISTRATION_NUMBER' => $regNumber
                            ])
                            ->one();

                        $grade = '';
                        $finalMarks = '';

                        if($studentMarks){
                            if($studentMarks->GRADE){
                                $grade = $studentMarks->GRADE;
                            }

                            if($studentMarks->FINAL_MARKS){
                                $finalMarks = $studentMarks->FINAL_MARKS;
                                $totalScore+= $finalMarks;
                                $courseWithMarks++;
                            }
                        }

                        $courses[] = [
                            'marksheetId' => $studentMarksheet['MRKSHEET_ID'],
                            'code' => $studentMarksheet['course']['COURSE_CODE'],
                            'grade' => $grade,
                            'finalMarks' => $finalMarks
                        ];

                        if($courseWithMarks > 0){
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
                        if($extraSizeCols > 0):
                            $normalSizeCols = $studentCoursesTotal - $extraSizeCols;
                            $normalSizeColsSpan = ($maxCols - $extraSizeCols) / $studentCoursesTotal;
                            $extraSizeColsSpan = $normalSizeColsSpan + 1;
                            ?>

                            <?php for($i = 0; $i < $normalSizeCols; $i ++):

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

                            <?php for($i = 0; $i < $studentCoursesTotal; $i ++):
                            $columnSpans[] = $normalSizeColsSpan;
                            ?>
                            <td colspan="<?= $normalSizeColsSpan; ?>"> <?= $courses[$i]['code']; ?></td>
                        <?php endfor; ?>

                        <?php endif;?>

                        <td> # UNITS </td>
                        <td> GPA </td>
                    </tr>

                    <tr>
                        <?php
                        /**
                         * If the column span size is an even, the size iss shared equally between the course marks
                         * and grade columns. Else the course marks column takes the extra size.
                         */
                        for($i = 0; $i < $studentCoursesTotal; $i ++):
                            $marksColSpan = $columnSpans[$i] / 2;
                            $gradeColSpan = $marksColSpan;
                            if($columnSpans[$i] % 2 !== 0) {
                                $marksColSpan = (($columnSpans[$i] - 1) / 2) + 1;
                                $gradeColSpan = (($columnSpans[$i] - 1) / 2);
                            }
                            ?>
                            <td colspan="<?= $marksColSpan?>"> <?= $courses[$i]['finalMarks'];?></td>
                            <td colspan="<?= $gradeColSpan?>"> <?= $courses[$i]['grade'];?></td>
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