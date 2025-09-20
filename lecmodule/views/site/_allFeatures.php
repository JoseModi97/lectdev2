<?php

/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc show the main menu
 */

/**
 * @var yii\web\View $this
 * @var string $facCode
 * @var string $deptCode
 */

use app\components\Menu;
?>

<div class="row">
    <!-- Start of the lecturer menu -->
    <div class="col-sm-12 col-md-4 col-lg-4">
        <div class="row">
            <div class="col-sm-12 col-md-3 col-lg-3">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="rq">
                            <ul class="dc ayn">
                                <li data-jstree='{"type":"folder"}'><a href="javascript:void(0);"><strong class="agd">LECTURER</strong></a>
                                    <ul class="dc ayn">
                                        <!-- Start of the lecturer operations -->
                                        <?= Menu::nodeGen('My course allocations', ['/allocated-courses']) ?>
                                        <!-- End of the lecturer operations -->


                                        <?= Menu::nodeGen('Student Marksheet', ['/student-marksheet']) ?>
                                        <?= Menu::nodeGen('HoD Approval', ['/student-marksheet/hod-approval']) ?>
                                        <?= Menu::nodeGen('Dean Approval', ['/student-marksheet/dean-approval']) ?>






                                        <!-- Start of the lecturer reports -->
                                        <li data-jstree='{"type":"folder"}'><a href="javascript:void(0);"><strong class="agd">Reports</strong></a>
                                            <ul class="dc ayn">
                                                <?= Menu::nodeGen('Marks submission status', ['/submission-status/index']) ?>
                                            </ul>
                                            <ul class="dc ayn">
                                                <?= Menu::nodeGen('Course analysis', [
                                                    '/shared-reports/course-analysis-filters',
                                                    'level' => 'lecturer'
                                                ]) ?>
                                            </ul>
                                        </li>
                                        <!-- End of the lecturer reports -->
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End of the lecturer menu -->

    <!-- Start of the HOD menu -->
    <div class="col-sm-12 col-md-4 col-lg-4">
        <div class="row">
            <div class="col-sm-12">
                <div class="rq">
                    <ul class="dc ayn">
                        <li data-jstree='{"type":"folder"}'><a href="javascript:void(0);"><strong class="agd">HOD</strong></a>
                            <ul class="dc ayn">
                                <!-- Start of the HOD operations -->

                                <?= Menu::nodeGen('View uploaded results (interface 1)', [
                                    '/marks-approval/new-filters',
                                    'level' => 'hod',
                                    'filtersInterface' => '1'
                                ]) ?>

                                <?= Menu::nodeGen('View uploaded results (interface 2)', [
                                    '/marks-approval/new-filters',
                                    'level' => 'hod',
                                    'filtersInterface' => '2'
                                ]) ?>

                                <!--                                <li data-jstree='{"type":"folder"}'><a href="javascript:void(0);"><strong class="agd">View uploaded results</strong></a>-->
                                <!--                                    <ul class="dc ayn">-->
                                <!--                                        --><? //= Menu::nodeGen('Pending courses', ['/marks-approval/new-filters',
                                                                                //                                            'level' => 'hod', 'filtersInterface' => '2', 'resultsType' => 'pending'])
                                                                                ?>
                                <!--                                    </ul>-->
                                <!--                                    <ul class="dc ayn">-->
                                <!--                                        --><? //= Menu::nodeGen('Approved courses', ['/marks-approval/new-filters',
                                                                                //                                            'level' => 'hod', 'filtersInterface' => '2', 'resultsType' => 'approved'])
                                                                                ?>
                                <!--                                    </ul>-->
                                <!--                                </li>-->

                                <?= Menu::nodeGen('View uploaded results and edit marks', [
                                    '/marks-approval/programmes-in-faculty',
                                    'level' => 'hod'
                                ]) ?>

                                <li data-jstree='{"type":"folder"}'><a href="javascript:void(0);"><strong class="agd">Allocate lecturers</strong></a>
                                    <ul class="dc ayn">
                                        <?= Menu::nodeGen('Programme timetables', [
                                            '/allocation/filters',
                                            'filtersFor' => 'nonSuppCourses'
                                        ]) ?>
                                    </ul>
                                    <ul class="dc ayn">
                                        <?= Menu::nodeGen('Supplementary timetables', [
                                            '/allocation/filters',
                                            'filtersFor' => 'suppCourses'
                                        ]) ?>
                                    </ul>
                                    <ul>
                                        <li data-jstree='{"type":"folder"}'> <a href="javascript:void(0);"><strong class="agd">Departmental requests</strong></a>
                                            <ul class="dc ayn">
                                                <?= Menu::nodeGen('Lecturer requests', [
                                                    '/allocation/filters',
                                                    'filtersFor' => 'requestedCourses'
                                                ]) ?>
                                            </ul>
                                            <ul class="dc ayn">
                                                <?= Menu::nodeGen('Service courses', [
                                                    '/allocation/filters',
                                                    'filtersFor' => 'serviceCourses'
                                                ]) ?>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <!-- End of the HOD operations -->

                                <!-- Start of the HOD reports -->
                                <li data-jstree='{"type":"folder"}'><a href="javascript:void(0);"><strong class="agd">Reports</strong></a>
                                    <ul class="dc ayn">
                                        <?= Menu::nodeGen('Course analysis', [
                                            '/shared-reports/course-analysis-filters',
                                            'level' => 'hod'
                                        ]) ?>
                                    </ul>
                                    <ul class="dc ayn">
                                        <?= Menu::nodeGen('Course analysis (Submitted)', [
                                            '/shared-reports/course-analysis-filters',
                                            'level' => 'hod',
                                            'restrictedTo' => 'submitted'
                                        ]) ?>
                                    </ul>
                                    <ul class="dc ayn">
                                        <?= Menu::nodeGen(
                                            'Consolidated marksheet (level based)',
                                            ['/shared-reports/student-consolidated-marks-filters', 'level' => 'hod']
                                        )
                                        ?>
                                    </ul>
                                    <ul class="dc ayn">
                                        <?= Menu::nodeGen(
                                            'Received/Missing marks',
                                            ['/received-marks/filters']
                                        )
                                        ?>
                                    </ul>
                                </li>
                                <!-- Start of the HOD reports -->
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End of the HOD menu -->

    <!-- Start of the Dean menu -->
    <div class="col-sm-12 col-md-4 col-lg-4">
        <div class="row">
            <div class="col-sm-12">
                <div class="rq">
                    <ul class="dc ayn">
                        <!-- Start of the dean operations -->
                        <li data-jstree='{"type":"folder"}'><a href="javascript:void(0);"><strong class="agd">Dean</strong></a>
                            <ul class="dc ayn">
                                <?= Menu::nodeGen('View uploaded results (interface 1)', [
                                    '/marks-approval/new-filters',
                                    'level' => 'dean',
                                    'filtersInterface' => '1'
                                ]) ?>

                                <?= Menu::nodeGen('View uploaded results (interface 2)', [
                                    '/marks-approval/new-filters',
                                    'level' => 'dean',
                                    'filtersInterface' => '2'
                                ]) ?>
                            </ul>
                        </li>

                        <!--                        <li data-jstree='{"type":"folder"}'><a href="javascript:void(0);"><strong class="agd">View uploaded results</strong></a>-->
                        <!--                            <ul class="dc ayn">-->
                        <!--                                --><? //= Menu::nodeGen('Pending courses', ['/marks-approval/new-filters',
                                                                //                                    'level' => 'dean', 'filtersInterface' => '2', 'resultsType' => 'pending'])
                                                                ?>
                        <!--                            </ul>-->
                        <!--                            <ul class="dc ayn">-->
                        <!--                                --><? //= Menu::nodeGen('Approved courses', ['/marks-approval/new-filters',
                                                                //                                    'level' => 'dean', 'filtersInterface' => '2', 'resultsType' => 'approved'])
                                                                ?>
                        <!--                            </ul>-->
                        <!--                        </li>-->

                        <?= Menu::nodeGen(
                            'View uploaded results and edit marks',
                            ['/marks-approval/programmes-in-faculty', 'level' => 'dean']
                        ) ?>

                        <!-- End of the dean operations -->

                        <!-- Start of the dean reports -->
                        <li data-jstree='{"type":"folder"}'><a href="javascript:void(0);"><strong class="agd">Reports</strong></a>
                            <ul class="dc ayn">
                                <?= Menu::nodeGen('Course analysis', [
                                    '/shared-reports/course-analysis-filters',
                                    'level' => 'dean'
                                ]) ?>
                            </ul>
                            <ul class="dc ayn">
                                <?= Menu::nodeGen('Course analysis (Submitted)', [
                                    '/shared-reports/course-analysis-filters',
                                    'level' => 'dean',
                                    'restrictedTo' => 'submitted'
                                ]) ?>
                            </ul>
                            <ul class="dc ayn">
                                <?= Menu::nodeGen(
                                    'Consolidated marksheet (level based)',
                                    ['/shared-reports/student-consolidated-marks-filters', 'level' => 'dean']
                                )
                                ?>
                            </ul>
                            <ul class="dc ayn">
                                <?= Menu::nodeGen(
                                    'Created timetables',
                                    ['/dean-reports/department-timetables']
                                ) ?>
                            </ul>
                            <ul class="dc ayn">
                                <?= Menu::nodeGen(
                                    'Lecturer course allocation',
                                    ['/dean-reports/course-allocations-in-departments']
                                ) ?>
                            </ul>
                            <ul class="dc ayn">
                                <?= Menu::nodeGen(
                                    'Course work definition',
                                    ['/dean-reports/course-work-definition-in-departments']
                                ) ?>
                            </ul>
                            <ul class="dc ayn">
                                <?= Menu::nodeGen(
                                    'Received/Missing marks',
                                    ['/received-marks/filters']
                                )
                                ?>
                            </ul>
                        </li>
                        <!-- End of the dean reports -->
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End of the Dean menu -->
</div>

<hr>

<div class="row">
    <!-- Start of the faculty administrator menu -->
    <div class="col-sm-12 col-md-4 col-lg-4">
        <div class="row">
            <div class="col-sm-12">
                <div class="rq">
                    <ul class="dc ayn">
                        <li data-jstree='{"type":"folder"}'><a href="javascript:void(0);"><strong class="agd">Faculty administrator</strong></a>
                            <ul class="dc ayn">
                                <!-- Start of the faculty administrator operations -->
                                <?= Menu::nodeGen('Record returned scripts', [
                                    '/departments/in-faculty',
                                    'level' => 'facAdmin',
                                    'type' => 'operations'
                                ]) ?>
                                <!-- End of the faculty administrator operations -->

                                <!-- Start of the faculty administrator reports -->
                                <li data-jstree='{"type":"folder"}'><a href="javascript:void(0);"><strong class="agd">Reports</strong></a>
                                    <ul class="dc ayn">
                                        <?= Menu::nodeGen(
                                            'Returned scripts',
                                            ['/departments/in-faculty', 'level' => 'facAdmin', 'type' => 'reports']
                                        ) ?>
                                    </ul>

                                    <ul class="dc ayn">
                                        <?= Menu::nodeGen(
                                            'Created timetables',
                                            ['/faculty-admin-reports/department-timetables']
                                        ) ?>
                                    </ul>

                                    <ul class="dc ayn">
                                        <?= Menu::nodeGen(
                                            'Lecturer course allocation',
                                            ['/faculty-admin-reports/course-allocations-in-departments']
                                        ) ?>
                                    </ul>

                                    <ul class="dc ayn">
                                        <?= Menu::nodeGen(
                                            'Course work definition',
                                            ['/faculty-admin-reports/course-work-definition-in-departments']
                                        ) ?>
                                    </ul>
                                    <ul class="dc ayn">
                                        <?= Menu::nodeGen('Course analysis', [
                                            '/shared-reports/course-analysis-filters',
                                            'level' => 'dean'
                                        ]) ?>
                                    </ul>
                                </li>
                                <!-- End of the faculty administrator reports -->
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End of the faculty administrator menu -->

    <!-- Start of the system administrator menu -->
    <div class="col-sm-12 col-md-4 col-lg-4">
        <div class="row">
            <div class="col-sm-12">
                <div class="rq">
                    <ul class="dc ayn">
                        <li data-jstree='{"type":"folder"}'><a href="javascript:void(0);"><strong class="agd">System administrator</strong></a>
                            <ul class="dc ayn">
                                <!-- Start of the system administrator reports -->
                                <li data-jstree='{"type":"folder"}'><a href="javascript:void(0);"><strong
                                            class="agd">Reports</strong></a>
                                    <ul class="dc ayn">
                                        <?= Menu::nodeGen(
                                            'Created timetables',
                                            ['/system-admin-reports/faculty-timetables']
                                        ) ?>
                                    </ul>
                                    <ul class="dc ayn">
                                        <?= Menu::nodeGen(
                                            'Lecturer course allocation',
                                            ['/system-admin-reports/course-allocations-in-faculties']
                                        ) ?>
                                    </ul>
                                    <ul class="dc ayn">
                                        <?= Menu::nodeGen(
                                            'Course work definition',
                                            ['/system-admin-reports/course-work-definition-in-faculties']
                                        ) ?>
                                    </ul>
                                </li>
                                <!-- End of the system administrator reports -->
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End of the system administrator menu -->
</div>