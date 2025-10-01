<?php

/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * Manage course allocations to lecturers
 */

namespace app\controllers;

use app\components\SmisHelper;
use app\models\AllocationRequest;
use app\models\AllocationStatus;
use app\models\Course;
use app\models\CourseAllocationFilter;
use app\models\CourseAssignment;
use app\models\DegreeProgramme;
use app\models\Department;
use app\models\MarksheetDef;
use app\models\search\AllocationRequestsSearchNew;
use app\models\search\MarksheetDefAllocationSearchNew;
use app\models\Semester;
use app\models\EmpVerifyView;
use app\models\search\CourseAssignmentSearch;
use Exception;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/**
 * Manage course allocations to lecturers
 */
class AllocationController extends BaseController
{
    /**
     * @return array component behaviors
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     */
    public function init()
    {
        parent::init();
        SmisHelper::allowAccess(['LEC_SMIS_HOD']);
    }

    /**
     * @param string $filtersFor
     * @return string page for course allocation filters
     * @throws ServerErrorHttpException
     */
    public function actionFilters(string $filtersFor): string
    {
        try {
            if (
                $filtersFor !== 'nonSuppCourses' && $filtersFor !== 'suppCourses' && $filtersFor !== 'serviceCourses'
                && $filtersFor !== 'requestedCourses'
            ) {
                throw new Exception('You must provide the correct type for course allocation filters.');
            }

            $filter = new CourseAllocationFilter();
            $filter->purpose = $filtersFor;

            return $this->render('filter', [
                'title' => 'Lecturer allocation filters',
                'filter' => $filter
            ]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * Get available academic years configured for this system
     * @return Response
     */
    public function actionGetAcademicYears(): Response
    {
        try {
            return $this->asJson(['status' => 200, 'academicYears' => Yii::$app->params['academicYears']]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return $this->asJson(['status' => 500, 'message' => $message]);
        }
    }


    public function actionAllocation()
    {
        try {
            $courseAssignmentSearch = new CourseAssignmentSearch();

            // Non-supplementary courses
            $nonSuppCoursesProvider = $courseAssignmentSearch->search(Yii::$app->request->queryParams, [
                'payrollNo' => $this->payrollNo,
                'academicYear' => $this->academicYear,
                'semesterType' => 'other'
            ]);


            // Supplementary courses
            $suppCoursesProvider = $courseAssignmentSearch->search(Yii::$app->request->queryParams, [
                'payrollNo' => $this->payrollNo,
                'academicYear' => $this->academicYear,
                'semesterType' => 'supplementary'
            ]);

            if (empty(Yii::$app->request->queryParams) && empty($nonSuppCoursesProvider->getModels())) {
                $this->setFlash(
                    'danger',
                    'Allocated courses',
                    'You don\'t have any courses in the system for this academic year.
                        Kindly liase with your HOD to allocate the same to you'
                );
                return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
            }

            // All courses
            $allCoursesProvider = $courseAssignmentSearch->search(Yii::$app->request->queryParams, [
                'payrollNo' => $this->payrollNo,
                'academicYear' => null,
                'semesterType' => 'all'
            ]);

            //get user programmes
            $programmes = $this->getUserProgrammes($this->payrollNo, $nonSuppCoursesProvider);


            return $this->render('allocation', [
                'title' => 'My course allocations',
                'courseAssignmentSearch' => $courseAssignmentSearch,
                'nonSuppCoursesProvider' => $nonSuppCoursesProvider,
                // 'suppCoursesProvider' => $suppCoursesProvider,
                // 'allCoursesProvider' => $allCoursesProvider,
                'academicYear' => $this->academicYear,
                'facCode' => $this->facCode,
                'programmes' => $programmes
            ]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }


    /**
     * Get available programmes in a user\'s faculty filtered by academic year
     * @return Response
     */
    public function actionGetProgrammes(): Response
    {
        try {
            $get = Yii::$app->request->get();
            $academicYear = $get['year'] ?? null; // Get academic year from GET parameters

            $query = DegreeProgramme::find()->select(['DEGREE_NAME', 'DEGREE_CODE'])
                ->where(['FACUL_FAC_CODE' => $this->facCode]);

            if ($academicYear) {
                // Join with Semester table to filter by academic year
                $query->innerJoin('SMIS_SEMESTERS SM', 'SM.DEGREE_CODE = SMIS_DEGREE_PROGRAMMES.DEGREE_CODE')
                    ->andWhere(['SM.ACADEMIC_YEAR' => $academicYear])
                    ->distinct(); // Ensure unique programmes
            }

            $degreeProgrammes = $query->orderBy(['DEGREE_CODE' => SORT_ASC])->asArray()->all();

            return $this->asJson(['programmes' => $degreeProgrammes]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return $this->asJson(['status' => 500, 'message' => $message]);
        }
    }

    /**
     * Get available levels of study in an academic year and programme
     * @return Response
     */
    public function actionGetLevelsOfStudy(): Response
    {
        try {
            $get = Yii::$app->request->get();
            $levels = Semester::find()->alias('SM')->select(['SM.LEVEL_OF_STUDY'])->distinct()
                ->where(['SM.ACADEMIC_YEAR' => $get['year'], 'SM.DEGREE_CODE' => $get['degreeCode']])
                ->joinWith(['levelOfStudy LVL' => function (ActiveQuery $q) {
                    $q->select([
                        'LVL.LEVEL_OF_STUDY',
                        'LVL.NAME'
                    ]);
                }], true, 'INNER JOIN')
                ->orderBy(['SM.LEVEL_OF_STUDY' => SORT_ASC])->asArray()->all();



            return $this->asJson(['levels' => $levels]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return $this->asJson(['status' => 500, 'message' => $message]);
        }
    }

    /**
     * Get available student groups in an academic year, programme and study level
     * @return Response
     */
    public function actionGetGroups(): Response
    {
        try {
            $get = Yii::$app->request->get();
            $groups = Semester::find()->alias('SM')
                ->select(['SM.GROUP_CODE'])
                ->joinWith(['group GR' => function ($q) {
                    $q->select([
                        'GR.GROUP_CODE',
                        'GR.GROUP_NAME'
                    ]);
                }], true, 'INNER JOIN')
                ->where([
                    'SM.ACADEMIC_YEAR' => $get['year'],
                    'SM.DEGREE_CODE' => $get['degreeCode'],
                    'SM.LEVEL_OF_STUDY' => $get['level']
                ])
                ->distinct()
                ->orderBy(['SM.GROUP_CODE' => SORT_ASC])
                ->asArray()->all();
            return $this->asJson(['groups' => $groups]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return $this->asJson(['status' => 500, 'message' => $message]);
        }
    }

    /**
     * Get available semesters in an academic year, programme, study level and student group
     * @return Response
     */
    public function actionGetSemesters(): Response
    {
        try {
            $get = Yii::$app->request->get();
            $semesters = Semester::find()->alias('SM')
                ->select(['SM.SEMESTER_ID', 'SM.SEMESTER_CODE', 'SM.DESCRIPTION_CODE'])
                ->joinWith(['semesterDescription SD' => function ($q) {
                    $q->select([
                        'SD.DESCRIPTION_CODE',
                        'SD.SEMESTER_DESC'
                    ]);
                }], true, 'INNER JOIN')
                ->where([
                    'SM.ACADEMIC_YEAR' => $get['year'],
                    'SM.DEGREE_CODE' => $get['degreeCode'],
                    'SM.LEVEL_OF_STUDY' => $get['level'],
                    'SM.GROUP_CODE' => $get['group']
                ])
                ->distinct()
                ->orderBy(['SM.SEMESTER_CODE' => SORT_ASC])
                ->asArray()->all();
            return $this->asJson(['semesters' => $semesters]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return $this->asJson(['status' => 500, 'message' => $message]);
        }
    }

    /**
     * Give courses to lecturers
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionGive(): string
    {
        try {
            $courseFilter = new CourseAllocationFilter();

            $session = Yii::$app->session;

            // Save course filters in the session for retrieval on page redirects
            if (!empty(Yii::$app->request->get()['CourseAllocationFilter'])) {
                $session['CourseAllocationFilter'] = Yii::$app->request->get();
            }

            if (!$courseFilter->load($session->get('CourseAllocationFilter')) || !$courseFilter->validate()) {
                throw new Exception('Failed to load filters for course allocations.');
            }

            $purpose = $courseFilter->purpose;

            $coursesSearch = null;
            if ($purpose === 'nonSuppCourses' || $purpose === 'suppCourses') {
                $coursesSearch = new MarksheetDefAllocationSearchNew();
            }

            if ($purpose === 'requestedCourses' || $purpose === 'serviceCourses') {
                $coursesSearch = new AllocationRequestsSearchNew();
            }
            $coursesProvider = $coursesSearch->search($this->deptCode, $courseFilter);

            $viewFile = 'coursesToAllocate';
            if ($purpose === 'requestedCourses' || $purpose === 'serviceCourses') {
                $viewFile = 'requestedCourses';
            }

            $panelHeading = 'Programme timetables';
            if ($purpose === 'suppCourses') {
                $panelHeading = 'Supplementary timetables';
            } elseif ($purpose === 'requestedCourses') {
                $panelHeading = 'Lecturer requests';
            } elseif ($purpose === 'serviceCourses') {
                $panelHeading = 'Service courses';
            }

            return $this->render($viewFile, [
                'title' => 'Allocate courses to lecturers',
                'coursesProvider' => $coursesProvider,
                'coursesSearch' => $coursesSearch,
                'filter' => $courseFilter,
                'deptCode' => $this->deptCode,
                'deptName' => $this->deptName,
                'panelHeading' => $panelHeading
            ]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * Display course request view
     * @param string $requestId
     * @return string|\yii\console\Response|Response
     */
    public function actionViewRequestRender(string $requestId)
    {
        try {
            $allocationReq = AllocationRequest::findOne($requestId);
            return $this->renderAjax('_viewCourseRequest', [
                'allocationReqModel' => $allocationReq,
                'deptCode' => $this->deptCode,
                'title' => 'view course request'
            ]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            Yii::$app->session->setFlash('danger', 'Failed to update course leader', $message);
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        }
    }

    /**
     * Get course details
     * @return Response
     */
    public function actionCourseDetails(): Response
    {
        try {
            $post = Yii::$app->request->post();
            $mkModel = MarksheetDef::find()->alias('MD')
                ->select(['MD.MRKSHEET_ID', 'MD.COURSE_ID', 'MD.SEMESTER_ID'])
                ->where(['MD.MRKSHEET_ID' => $post['marksheetId']])
                ->joinWith(['course CS' => function (ActiveQuery $q) {
                    $q->select([
                        'CS.COURSE_ID',
                        'CS.COURSE_CODE',
                        'CS.COURSE_NAME',
                    ]);
                }], true, 'INNER JOIN')
                ->joinWith(['semester SM' => function (ActiveQuery $q) {
                    $q->select([
                        'SM.SEMESTER_ID',
                        'SM.ACADEMIC_YEAR',
                        'SM.LEVEL_OF_STUDY',
                        'SM.SEMESTER_CODE',
                        'SM.SEMESTER_TYPE',
                        'SM.GROUP_CODE',
                        'SM.DESCRIPTION_CODE',
                    ]);
                    // Join related lookup tables for names
                    $q->joinWith(['levelOfStudy LVL' => function (ActiveQuery $q2) {
                        $q2->select(['LVL.LEVEL_OF_STUDY', 'LVL.NAME']);
                    }], false);
                    $q->joinWith(['semesterDescription DESC' => function (ActiveQuery $q3) {
                        $q3->select(['DESC.DESCRIPTION_CODE', 'DESC.SEMESTER_DESC']);
                    }], false);
                    $q->joinWith(['group GRP' => function (ActiveQuery $q4) {
                        $q4->select(['GRP.GROUP_CODE', 'GRP.GROUP_NAME']);
                    }], false);
                    $q->joinWith(['degreeProgramme DEG' => function (ActiveQuery $q5) {
                        $q5->select(['DEG.DEGREE_CODE', 'DEG.DEGREE_NAME']);
                    }], false);
                }], false, 'LEFT JOIN')
                ->one();

            return $this->asJson([
                'status' => 200,
                'data' => [
                    'marksheetId' => $post['marksheetId'],
                    'courseCode' => $mkModel->course->COURSE_CODE,
                    'courseName' => $mkModel->course->COURSE_NAME,
                    'academicYear' => $mkModel->semester->ACADEMIC_YEAR ?? '',
                    'levelOfStudyName' => $mkModel->semester->levelOfStudy->NAME ?? '',
                    'semesterDescription' => $mkModel->semester->semesterDescription->SEMESTER_DESC ?? '',
                    'semesterCode' => $mkModel->semester->SEMESTER_CODE ?? '',
                    'groupName' => $mkModel->semester->group->GROUP_NAME ?? '',
                    'semesterType' => $mkModel->semester->SEMESTER_TYPE ?? '',
                    'degreeName' => $mkModel->semester->degreeProgramme->DEGREE_NAME ?? '',
                ]
            ]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return $this->asJson(['status' => 500, 'message' => $message]);
        }
    }

    /**
     * Save course allocations
     * Save course requests
     * Update course requests
     * @return \yii\console\Response|Response
     */
    public function actionAllocateRequestLecturer()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = Yii::$app->request->post();
            $requestId = $post['requestId'];
            $courseType = $post['courseType'];
            $marksheetId = $post['marksheetId'];
            $lecturers = $post['lecturers'] ?? null;
            $department = $post['department'] ?? null;
            $internalLecturer = $post['internalLecturer'] ?? null;
            $externalLecturer = $post['externalLecturer'] ?? null;
            $statusName = null;

            // Department courses must have a lecturer assigned. Service courses can be denied a lecturer
            if ($courseType === 'departmental' && $internalLecturer === 'true') {
                if (empty($lecturers)) {
                    Yii::$app->session->setFlash(
                        'danger',
                        'Assign Courses',
                        'Failed to allocate this course. A lecturer must be provided.'
                    );
                    return $this->refresh();
                }
            }
            if ($courseType === 'departmental' && $externalLecturer === 'true') {
                if (!isset($department) || $department === '') {
                    Yii::$app->session->setFlash(
                        'danger',
                        'Assign Courses',
                        'Failed to request for this course. A servicing department must be provided.'
                    );
                    return $this->refresh();
                }
            }

            if ($courseType === 'service') {
                $remarks = $post['remarks'];
                $statusName = $post['status'];
                if (!isset($statusName) || $statusName === '') {
                    Yii::$app->session->setFlash(
                        'danger',
                        'Assign Courses',
                        'Failed to allocate this course. A status must be provided.'
                    );
                    return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
                }
                $allocationStatus = AllocationStatus::find()->where(['STATUS_NAME' => $statusName])->one();
                $statusId = $allocationStatus->STATUS_ID;
                if ($statusName === 'NOT APPROVED') {
                    $lecturers = [];
                } elseif ($statusName === 'APPROVED') {
                    if (empty($lecturers)) {
                        Yii::$app->session->setFlash(
                            'danger',
                            'Assign Courses',
                            'Failed to allocate this course. A lecturer must be provided.'
                        );
                        return $this->refresh();
                    }
                }
            }

            $currentDate = new Expression("
    CAST(SYSTIMESTAMP AT TIME ZONE 'Africa/Nairobi' AS DATE)
    - ( (44/1440) + (27/86400) )
");

            // Allocate lecturers to a course.
            if (($courseType === 'departmental' &&  $internalLecturer === 'true') || $statusName === 'APPROVED') {
                $this->courseAssignement($marksheetId, $lecturers);

                /**
                 * Start block
                 * @todo This feature might be needed in the future.
                 * When allocating this course for the first time,
                 * make the lecturer at index 0 the default leader and email them.
                 * Otherwise, explicitly decide the leader under manageAllocateLecturer method.
                 * @see actionManageAllocatedLecturer
                 */
                $marksheetDef = MarksheetDef::findOne($marksheetId);
                if (is_null($marksheetDef->PAYROLL_NO)) {
                    $marksheetDef->PAYROLL_NO = $lecturers[0];
                    $marksheetDef->LAST_UPDATE = $currentDate;
                    if ($marksheetDef->save()) {
                        $this->sendAllocationAlert($marksheetDef, $lecturers[0], 'newCourseLeader');
                    } else {
                        throw new Exception('Failed to create course leader.');
                    }
                }
                /**
                 * End block
                 */
            }

            /**
             * Make lecturer requests and notify the requested department
             * We don\\'t want to send requests for a course, if another for the same exists and is still pending
             */
            $requestExists = false;
            if ($courseType === 'departmental' &&  $externalLecturer === 'true') {
                $allocationStatus = AllocationStatus::find()->where(['STATUS_NAME' => 'PENDING'])->one();

                $allocationReq = AllocationRequest::find()
                    ->where(['MARKSHEET_ID' => $marksheetId, 'STATUS_ID' => $allocationStatus->STATUS_ID])
                    ->one();

                if (is_null($allocationReq)) {
                    $allocationReq = new AllocationRequest();
                    $allocationReq->STATUS_ID = $allocationStatus->STATUS_ID;
                    $allocationReq->MARKSHEET_ID = $marksheetId;
                    $allocationReq->REQUESTING_DEPT = $this->deptCode;
                    $allocationReq->SERVICING_DEPT = $department;
                    $allocationReq->REQUEST_DATE =  $currentDate;
                    $allocationReq->REQUEST_BY = $this->payrollNo;
                    if ($allocationReq->save()) {
                        $this->sendServiceCourseAlert($allocationReq, 'request');
                    } else {
                        throw new Exception('Failed to make a lecturer request for this course.');
                    }
                } else {
                    $requestExists = true;
                }
            }

            // Attend to the lecturer requests and notify the requesting department
            if ($courseType === 'service') {
                $allocationReq = AllocationRequest::findOne($requestId);
                $remarks = ($remarks === '') ? null : $remarks;
                $allocationReq->REMARKS = $remarks;
                $allocationReq->STATUS_ID = $statusId;
                $allocationReq->ATTENDED_BY = $this->payrollNo;
                $allocationReq->ATTENDED_DATE = $currentDate;
                if ($allocationReq->save()) {
                    $this->sendServiceCourseAlert($allocationReq, 'service');
                } else {
                    throw new Exception('Failed to attend to the lecturer request for this course.');
                }

                if (!empty($lecturers)) {
                    $this->courseAssignement($marksheetId, $lecturers);
                }
            }

            $transaction->commit();

            if (($courseType === 'departmental' &&  $internalLecturer === 'true') || $statusName === 'APPROVED') {
                Yii::$app->session->setFlash(
                    'success',
                    'Assign Courses',
                    'This course has been allocated successfully.'
                );
            }

            if ($statusName === 'NOT APPROVED') {
                Yii::$app->session->setFlash(
                    'success',
                    'Assign Courses',
                    'This course has been not been allocated.'
                );
            }

            if ($courseType === 'departmental' &&  $externalLecturer === 'true') {
                if ($internalLecturer === 'true') {
                    if ($requestExists) {
                        Yii::$app->session->addFlash(
                            'danger',
                            'Request Courses',
                            'Request for this course was not sent. Another request exists that is still pending.'
                        );
                    } else {
                        Yii::$app->session->addFlash(
                            'success',
                            'Request Courses',
                            'A lecturer for this course has been requested successfully.'
                        );
                    }
                } else {
                    if ($requestExists) {
                        Yii::$app->session->setFlash(
                            'danger',
                            'Request Courses',
                            'Request for this course was not sent. Another request exists that is still pending.'
                        );
                    } else {
                        Yii::$app->session->setFlash(
                            'success',
                            'Request Courses',
                            'A lecturer for this course has been requested successfully.'
                        );
                    }
                }
            }

            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        } catch (Exception $ex) {
            $transaction->rollBack();
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return $this->asJson(['status' => 500, 'message' => $message]);
        }
    }

    /**
     * Get the lecturers allocated for a course
     * @param string $marksheetId
     * @param string $purpose
     * @return string|Response
     */
    public function actionAllocatedLecturerRender(string $marksheetId, string $purpose)
    {
        try {
            if ($purpose === 'manage') {
                $view = '_manageAssignedLecturer';
            } elseif ($purpose === 'remove') {
                $view = '_removeAssignedLecturer';
            } else {
                throw new Exception('The reason to view allocated courses must be provided.');
            }

            $lecturers = CourseAssignment::find()->where(['MRKSHEET_ID' => $marksheetId])->all();
            return $this->renderAjax($view, [
                'title' => 'Lectures allocated',
                'lecturers' => $lecturers,
                'marksheetId' => $marksheetId,
            ]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return $this->asJson(['status' => 500, 'message' => $message]);
        }
    }

    /**
     * Cancel a lecturer request by deleting the PENDING record.
     * Only the requesting department may delete its own pending request.
     * Returns JSON on AJAX requests.
     */
    public function actionCancelRequest()
    {
        try {
            $post = \Yii::$app->request->post();
            $requestId = $post['requestId'] ?? null;
            if (!$requestId) {
                return $this->asJson(['status' => 500, 'message' => 'Missing request id.']);
            }

            $allocationReq = AllocationRequest::findOne($requestId);
            if (!$allocationReq) {
                return $this->asJson(['status' => 500, 'message' => 'Request not found.']);
            }

            // Only requesting department can cancel
            if ($allocationReq->REQUESTING_DEPT !== $this->deptCode) {
                return $this->asJson(['status' => 500, 'message' => 'Not allowed to cancel this request.']);
            }

            // Only PENDING requests can be deleted
            $pending = AllocationStatus::find()->where(['STATUS_NAME' => 'PENDING'])->one();
            if ($pending && (int)$allocationReq->STATUS_ID !== (int)$pending->STATUS_ID) {
                return $this->asJson(['status' => 500, 'message' => 'Only pending requests can be deleted.']);
            }

            if ($allocationReq->delete() === false) {
                return $this->asJson(['status' => 500, 'message' => 'Failed to cancel (delete) request.']);
            }

            return $this->asJson(['status' => 200]);
        } catch (\Throwable $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return $this->asJson(['status' => 500, 'message' => $message]);
        }
    }

    /**
     * Revert a serviced course request back to PENDING.
     * - Clears remarks and attended fields
     * - Sets status to PENDING
     * - Removes any allocated lecturers for the marksheet
     * Returns JSON on AJAX requests.
     */
    public function actionRevertRequest()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $post = \Yii::$app->request->post();
            $requestId = $post['requestId'] ?? null;
            $marksheetId = $post['marksheetId'] ?? null;
            if (!$requestId || !$marksheetId) {
                return $this->asJson(['status' => 500, 'message' => 'Missing request id or marksheet id.']);
            }

            $allocationReq = AllocationRequest::findOne($requestId);
            if (!$allocationReq) {
                return $this->asJson(['status' => 500, 'message' => 'Request not found.']);
            }

            // Only servicing department can revert
            if ($allocationReq->SERVICING_DEPT !== $this->deptCode) {
                return $this->asJson(['status' => 500, 'message' => 'Not allowed to revert this request.']);
            }

            // Only APPROVED / NOT APPROVED requests can be reverted
            $pending = AllocationStatus::find()->where(['STATUS_NAME' => 'PENDING'])->one();
            if (!$pending) {
                return $this->asJson(['status' => 500, 'message' => 'Pending status not configured.']);
            }

            if ((int)$allocationReq->STATUS_ID === (int)$pending->STATUS_ID) {
                return $this->asJson(['status' => 500, 'message' => 'Request already pending.']);
            }

            // Remove allocated lecturers for this marksheet
            $assignments = CourseAssignment::find()->where(['MRKSHEET_ID' => $marksheetId])->all();
            foreach ($assignments as $assignment) {
                if ($assignment->delete() === false) {
                    throw new Exception('Failed to remove allocated lecturers for this request.');
                }
            }

            // If course leader is one of deleted or no assignments remain, clear leader
            $remaining = CourseAssignment::find()->where(['MRKSHEET_ID' => $marksheetId])->count();
            if ((int)$remaining === 0) {
                $marksheetDef = MarksheetDef::findOne($marksheetId);
                if ($marksheetDef) {
                    $marksheetDef->PAYROLL_NO = null;
                    $marksheetDef->LAST_UPDATE = new Expression('CURRENT_DATE');
                    if ($marksheetDef->save() === false) {
                        throw new Exception('Failed to reset course leader.');
                    }
                }
            }

            // Revert request fields
            $allocationReq->STATUS_ID = $pending->STATUS_ID;
            $allocationReq->REMARKS = null;
            $allocationReq->ATTENDED_BY = null;
            $allocationReq->ATTENDED_DATE = null;
            if ($allocationReq->save() === false) {
                throw new Exception('Failed to revert request to pending.');
            }

            $transaction->commit();
            return $this->asJson(['status' => 200]);
        } catch (\Throwable $ex) {
            $transaction->rollBack();
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return $this->asJson(['status' => 500, 'message' => $message]);
        }
    }

    /**
     * Update a course leader
     * @return Response
     */
    public function actionManageAllocatedLecturer(): Response
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = Yii::$app->request->post();
            $marksheetId = $post['marksheetId'];
            $lecturer = $post['lecturer'];

            $mkModel = MarksheetDef::findOne($marksheetId);
            if (is_null($mkModel)) {
                throw new Exception('Marksheet not found.');
            }

            $previousMkModel = clone $mkModel;
            $mkModel->PAYROLL_NO = $lecturer;
            $mkModel->LAST_UPDATE = new Expression("CAST(SYSTIMESTAMP AT TIME ZONE 'Africa/Nairobi' AS DATE)");
            if ($mkModel->save()) {
                $this->sendAllocationAlert($mkModel, $lecturer, 'updateCourseLeader');
            } else {
                throw new Exception('Failed to create course leader.');
            }

            $lecturer = EmpVerifyView::find()->select(['PAYROLL_NO'])
                ->where(['PAYROLL_NO' => $previousMkModel->PAYROLL_NO])->one();

            if (!is_null($lecturer)) {
                $this->sendAllocationAlert($previousMkModel, $previousMkModel->PAYROLL_NO, 'removeCourseLeader');
            }

            $transaction->commit();

            Yii::$app->session->setFlash(
                'success',
                'Manage course lecturers',
                'This course leader has been set successfully.'
            );
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        } catch (Exception $ex) {
            $transaction->rollBack();
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            Yii::$app->session->setFlash('danger', 'Failed to update course leader', $message);
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        }
    }

    /**
     * Remove allocated lecturers from a course
     * @return Response
     * @throws \Throwable
     */
    public function actionRemoveAllocatedLecturer(): Response
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = Yii::$app->request->post();
            $marksheetId = $post['marksheetId'];
            $lecturers = $post['lecturers'];
            foreach ($lecturers as $lec) {
                $courseAssignment = CourseAssignment::find()
                    ->where(['PAYROLL_NO' => $lec, 'MRKSHEET_ID' => $marksheetId])->one();

                if (!$courseAssignment->delete()) {
                    throw new Exception('Failed to remove course allocation.');
                } else {
                    $mkModel = MarksheetDef::findOne($marksheetId);

                    /**
                     * Start block
                     * @todo This feature might be needed in the future.
                     * If this lecturer was a course leader, replace them with any remaining 
                     * lecturers allocated for the course.
                     */
                    if (strval($mkModel->PAYROLL_NO) === $lec) {
                        $otherLecturer = CourseAssignment::find()->where(['MRKSHEET_ID' => $marksheetId])->one();
                        if (is_null($otherLecturer)) {
                            $mkModel->PAYROLL_NO = null;
                        } else {
                            $mkModel->PAYROLL_NO = $otherLecturer->PAYROLL_NO;
                        }
                        $mkModel->LAST_UPDATE = new Expression("CAST(SYSTIMESTAMP AT TIME ZONE 'Africa/Nairobi' AS DATE)");
                        if ($mkModel->save()) {
                            if (!is_null($otherLecturer)) {
                                $this->sendAllocationAlert($mkModel, $otherLecturer->PAYROLL_NO, 'newCourseLeader');
                            }
                        } else {
                            throw new Exception('Failed to remove course leader.');
                        }
                    }
                    /**
                     * End block
                     */

                    $staff = EmpVerifyView::find()->select(['PAYROLL_NO'])->where(['PAYROLL_NO' => $lec])->one();

                    if (!is_null($staff)) {
                        $this->sendAllocationAlert($mkModel, $lec, 'removedFromCourse');
                    }
                }
            }
            $transaction->commit();
            Yii::$app->session->setFlash(
                'success',
                'Remove allocated lecturers',
                'The selected lecturer(s) have been removed from the course successfully.'
            );
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        } catch (Exception $ex) {
            $transaction->rollBack();
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            Yii::$app->session->setFlash('danger', 'Failed to remove lecturer', $message);
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        }
    }

    /**
     * Allocate a course to lecturer(s)
     * @param string $marksheetId
     * @param array $lecturers
     * @return void
     * @throws Exception
     */
    private function courseAssignement(string $marksheetId, array $lecturers)
    {
        foreach ($lecturers as $payrollNo) {
            $marksheetDef = MarksheetDef::findOne($marksheetId);
            $courseAssignment = CourseAssignment::find()
                ->where(['PAYROLL_NO' => $payrollNo, 'MRKSHEET_ID' => $marksheetId])
                ->one();

            if (is_null($courseAssignment)) {
                $courseAssignment = new CourseAssignment();
                $courseAssignment->PAYROLL_NO = $payrollNo;
                $courseAssignment->MRKSHEET_ID = $marksheetId;
                $courseAssignment->ASSIGNMENT_DATE = new Expression('CURRENT_DATE');
                if ($courseAssignment->save()) {
                    $this->sendAllocationAlert($marksheetDef, $payrollNo, 'addedToCourse');
                } else {
                    throw new Exception('Failed to allocate lecturer.');
                }
            }
        }
    }

    /**
     * Send email notification for course allocations and removals
     * @param MarksheetDef $marksheetDef
     * @param string $payrollNo
     * @param string $reason
     * @return void
     * @throws Exception
     */
    private function sendAllocationAlert(MarksheetDef $marksheetDef, string $payrollNo, string $reason): void
    {
        $staff = EmpVerifyView::find()
            ->select(['EMP_TITLE', 'SURNAME', 'OTHER_NAMES', 'FAC_CODE', 'EMAIL'])
            ->where(['PAYROLL_NO' => $payrollNo])->asArray()->one();

        $recipientName = $staff['EMP_TITLE'] . ' ' . $staff['SURNAME'] . ' ' . $staff['OTHER_NAMES'];
        $isFacultyFHS = $staff['FAC_CODE'] === 'G';

        $course = Course::find()->select(['DEPT_CODE', 'COURSE_CODE', 'COURSE_NAME'])
            ->where(['COURSE_ID' => $marksheetDef->COURSE_ID])->asArray()->one();

        $department = Department::find()->select(['DEPT_NAME'])
            ->where(['DEPT_CODE' => $course['DEPT_CODE']])->asArray()->one();

        if ($reason === 'newCourseLeader' || $reason === 'updateCourseLeader') {
            $subject = 'ADDED AS COURSE LEADER';
            $viewName = 'courseLeader';
        } elseif ($reason === 'removeCourseLeader') {
            $subject = 'REMOVED AS COURSE LEADER';
            $viewName = 'notCourseLeader';
        } elseif ($reason === 'addedToCourse') {
            $subject = 'ADDED TO COURSE';
            $viewName = 'addedToCourse';
        } elseif ($reason === 'removedFromCourse') {
            $subject = 'REMOVED FROM COURSE';
            $viewName = 'removedFromCourse';
        } else {
            throw new Exception('You must provide a correct reason for this email alert');
        }

        $email = [
            'recipientEmail' => $staff['EMAIL'],
            'subject' => $subject,
            'params' => [
                'recipient' => $recipientName,
                'isFacultyFHS' => $isFacultyFHS,
                'courseCode' => $course['COURSE_CODE'],
                'courseName' => $course['COURSE_NAME'],
                'academicYear' => $this->getCurrentAcademicYear(),
                'departmentName' => strtolower($department['DEPT_NAME']),
            ]
        ];
        $emails = [];
        $emails[] = $email;
        $layout = '@app/mail/layouts/html';
        $view = '@app/mail/views/' . $viewName;
        // SmisHelper::sendEmails($emails, $layout, $view);
    }

    /**
     * Send email notifications for when a request for a lecturer is made
     * and when that request is attended to.
     * @param AllocationRequest $allocationRequest
     * @param string $reason
     * @return void
     * @throws Exception
     */
    private function sendServiceCourseAlert(AllocationRequest $allocationRequest, string $reason): void
    {
        $marksheetDef = MarksheetDef::find()->select(['COURSE_ID', 'SEMESTER_ID'])
            ->where(['MRKSHEET_ID' => $allocationRequest->MARKSHEET_ID])->asArray()->one();

        $programme = '';

        $course = Course::find()->select(['COURSE_CODE', 'COURSE_NAME'])
            ->where(['COURSE_ID' => $marksheetDef['COURSE_ID']])->asArray()->one();

        $requestingDeptCode = $allocationRequest->REQUESTING_DEPT;
        $servicingDeptCode = $allocationRequest->SERVICING_DEPT;

        // Send email alert to the servicing dept
        if ($reason === 'request') {
            $viewName = 'courseRequest';

            $semester = Semester::find()->select(['DEGREE_CODE'])->where(['SEMESTER_ID' => $marksheetDef['SEMESTER_ID']])
                ->asArray()->one();

            $degreeProgramme = DegreeProgramme::find()->select(['DEGREE_NAME', 'DEGREE_TYPE'])
                ->where(['DEGREE_CODE' => $semester['DEGREE_CODE']])->asArray()->one();

            $programme = $degreeProgramme['DEGREE_TYPE'] . ' ' . $degreeProgramme['DEGREE_NAME'];

            $requestingDept = Department::find()->select(['DEPT_NAME'])
                ->where(['DEPT_CODE' => $requestingDeptCode])->asArray()->one();
            $requestingDeptName = $requestingDept['DEPT_NAME'];

            $servicingDept = Department::find()->select(['EMAIL', 'DEPT_NAME'])
                ->where(['DEPT_CODE' => $servicingDeptCode])->asArray()->one();
            $deptEmail =  $servicingDept['EMAIL'];
            $servicingDeptName = $servicingDept['DEPT_NAME'];
        }
        // Send email alert to the requesting dept
        else {
            $viewName = 'attendedCourseRequest';

            $servicingDept = Department::find()->select(['DEPT_NAME'])
                ->where(['DEPT_CODE' => $servicingDeptCode])->asArray()->one();
            $servicingDeptName = $servicingDept['DEPT_NAME'];

            $requestingDept = Department::find()->select(['EMAIL', 'DEPT_NAME'])
                ->where(['DEPT_CODE' => $requestingDeptCode])->asArray()->one();
            $deptEmail =  $requestingDept['EMAIL'];
            $requestingDeptName = $requestingDept['DEPT_NAME'];
        }

        $email = [
            'recipientEmail' => $deptEmail,
            'subject' => 'LECTURER REQUEST FOR A COURSE',
            'params' => [
                'recipient' => 'HOD',
                'courseCode' => $course['COURSE_CODE'],
                'courseName' => $course['COURSE_NAME'],
                'requestingDeptName' => strtolower($requestingDeptName),
                'servicingDeptName' => strtolower($servicingDeptName),
                'academicYear' => $this->getCurrentAcademicYear(),
                'programme' => $programme
            ]
        ];
        $emails = [];
        $emails[] = $email;
        $layout = '@app/mail/layouts/html';
        $view = '@app/mail/views/' . $viewName;
        // SmisHelper::sendEmails($emails, $layout, $view);
    }

    public function getUserProgrammes($payrollNo, $dataProvider = null)
    {
        $programmes = [];

        if ($dataProvider) {
            $models = $dataProvider->getModels();
            $uniqueProgrammes = [];

            foreach ($models as $model) {
                $degreeCode = $model->marksheetDef->semester->degreeProgramme->DEGREE_CODE;
                $degreeName = $model->marksheetDef->semester->degreeProgramme->DEGREE_NAME;

                if (!isset($uniqueProgrammes[$degreeCode])) {
                    $uniqueProgrammes[$degreeCode] = [
                        'DEGREE_CODE' => $degreeCode,
                        'DEGREE_NAME' => $degreeName
                    ];
                }
            }

            $programmes = array_values($uniqueProgrammes);
        } else {
            // $programmes = CourseAssignment::find()
            //     ->select(['DISTINCT DEG.DEGREE_CODE', 'DEG.DEGREE_NAME'])
            //     ->alias('CA')
            //     ->innerJoin(['MD' => 'marksheet_def'], 'CA.MRKSHEET_ID = MD.MRKSHEET_ID')
            //     ->innerJoin(['SM' => 'semester'], 'MD.SEMESTER_ID = SM.SEMESTER_ID')
            //     ->innerJoin(['DEG' => 'degree_programme'], 'SM.DEGREE_CODE = DEG.DEGREE_CODE')
            //     ->where(['CA.PAYROLL_NO' => $payrollNo])
            //     ->orderBy(['DEG.DEGREE_NAME' => SORT_ASC])
            //     ->asArray()
            //     ->all();

            $programmes = [];
        }
        return $programmes;
    }
}
