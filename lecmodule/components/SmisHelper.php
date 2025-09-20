<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 * @desc Defines all helper functions
 */

namespace app\components;

use app\models\Course;
use app\models\CourseAssignment;
use app\models\CourseWorkAssessment;
use app\models\DegreeAssessment;
use app\models\DegreeProgramme;
use app\models\Department;
use app\models\EmpVerifyView;
use app\models\Faculty;
use app\models\GradingSystemDetails;
use app\models\Group;
use app\models\LevelOfStudy;
use app\models\Marksheet;
use app\models\MarksheetDef;
use app\models\MarksheetRatio;
use app\models\ProjectDescription;
use app\models\search\AllocatedCoursesPerLecturerSearch;
use app\models\search\CourseworkAssessmentSearch;
use app\models\search\CreatedMarksheetsSearch;
use app\models\search\NoOfAllocatedCoursesPerLecturerSearch;
use app\models\search\ProgrammeCourseWorkSearch;
use app\models\Semester;
use app\models\StudentBalance;
use app\models\StudentBalanceAll;
use app\models\StudentConsolidatedMarksFilter;
use app\models\StudentCourse;
use app\models\StudentCoursework;
use app\models\TempMarksheet;
use Exception;
use Yii;
use yii\console\Response;
use yii\data\ArrayDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\DataReader;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;

class SmisHelper
{
    const MARKS_COMPLETE = 1;
    const MARKS_NOT_COMPLETE = 0;
    const MARKS_PUBLISHED = 1;
    const MARKS_NOT_PUBLISHED = 0;
    const MARKS_CONSOLIDATED = 1;
    const MARKS_NOT_TRANSFERRED = 0;

    /**
     * Format date and time as needed.
     * Set $dateTime as now to get current date and time. Or pass another date and time.
     * Set $dateAndTime to TRUE to get date + time.
     * Set $dateAndTime to FALSE to get only the date.
     *
     * @param string $dateTime
     * @param bool $dateAndTime
     *
     * @return string $formatted date/datetime
     * @throws Exception
     */
    public static function getDateTime(string $dateTime = 'now', bool $dateAndTime = true): string
    {
        $formatter = Yii::$app->components['formatter'];
        if ($dateAndTime) {
            $format = $formatter['datetimeFormat'];
        } else {
            $format = $formatter['dateFormat'];
        }

        $defaultTimezone = new \DateTimeZone(Yii::$app->components['formatter']['defaultTimeZone']);
        $todayDateTime = new \DateTime($dateTime, $defaultTimezone);

        return $todayDateTime->format($format);
    }

    /**
     * Check if all marks have been approved at a certain level.
     * If marks are still pending, user actions are allowed on them at that level.
     *
     * @param string $assessmentId
     * @param mixed $statusToCheck
     *
     * @return bool TRUE if marks are pending. Else FALSE.
     */
    public static function marksPending(string $assessmentId, string $statusToCheck): bool
    {
        $allMarks = StudentCoursework::find()->where(['ASSESSMENT_ID' => $assessmentId])->count();
        if ($allMarks > 0) {
            $approvedMarks = StudentCoursework::find()
                ->where([
                    'ASSESSMENT_ID' => $assessmentId,
                    $statusToCheck => 'APPROVED'
                ])->count();

            if ($allMarks === $approvedMarks) {
                $marksPending = false;
            } else {
                $marksPending = true;
            }
        } else {
            $marksPending = true;
        }

        return $marksPending;
    }

    /**
     * Send an email message
     *
     * @param array $emails content to be passed in the message body
     * @param string $layout Layout of the email message
     * @param string $view body of the email message
     *
     * @return void
     * @throws Exception if email not sent
     *
     */
    public static function sendEmails(array $emails, string $layout, string $view): void
    {
        foreach ($emails as $email) {
            if (!empty($email['recipientEmail'])) {
                $message = Yii::$app->mailer->compose();

                $recipientEmail = $email['recipientEmail'];
                if (YII_ENV_DEV) {
                    $recipientEmail = Yii::$app->params['noReplyEmail'];
                }

                $message->setFrom([Yii::$app->params['noReplyEmail'] => Yii::$app->params['sitename']])
                    ->setTo($recipientEmail)
                    ->setSubject($email['subject']);

                $body = Yii::$app->mailer->render($view, $email['params'], $layout);
                $message->setHtmlBody($body);
                if (!$message->send()) {
                    throw  new Exception('Email not sent.');
                }
            }
        }
    }

    /**
     * Check roles of the logged-in user against those allowed for each functionality of the system.
     * Grant access only if the user has one of the needed roles.
     * We include the dev roles in the dev env to aid with development and testing.
     * The roles allowed to access the particular functionality
     * @param array $userRoles
     * @return void|Response|\yii\web\Response
     * @throws ForbiddenHttpException
     */
    public static function allowAccess(array $userRoles)
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->response->redirect('site/login');
        }

        $roles = self::getUserRoles();

        if (empty($roles)) {
            Yii::$app->user->logout();
            return Yii::$app->response->redirect(['/login']);
        }

        if (!array_intersect($roles, $userRoles)) {
            throw new ForbiddenHttpException("You do not have the necessary privileges to access this function");
        }
    }

    /**
     * Check for only LECTURER role in the user roles
     * @return bool
     */
    public static function hasOnlyLecturerRole(): bool
    {
        $roles = self::getUserRoles();
        if (in_array('LEC_SMIS_LECTURER', $roles)
            && !in_array('LEC_SMIS_HOD', $roles)
            && !in_array('LEC_SMIS_DEAN', $roles)
            && !in_array('LEC_SMIS_FAC_ADMIN', $roles)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check for only HOD role in the user roles
     * @return bool
     */
    public static function hasOnlyHodRole(): bool
    {
        $roles = self::getUserRoles();
        if (!in_array('LEC_SMIS_LECTURER', $roles)
            && in_array('LEC_SMIS_HOD', $roles)
            && !in_array('LEC_SMIS_DEAN', $roles)
            && !in_array('LEC_SMIS_FAC_ADMIN', $roles)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check for only DEAN role in the user roles
     * @return bool
     */
    public static function hasOnlyDeanRole(): bool
    {
        $roles = self::getUserRoles();
        if (!in_array('LEC_SMIS_LECTURER', $roles)
            && !in_array('LEC_SMIS_HOD', $roles)
            && in_array('LEC_SMIS_DEAN', $roles)
            && !in_array('LEC_SMIS_FAC_ADMIN', $roles)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check for only FAC_ADMIN role in the user roles
     * @return bool
     */
    public static function hasOnlyFacAdminRole(): bool
    {
        $roles = self::getUserRoles();
        if (!in_array('LEC_SMIS_LECTURER', $roles)
            && !in_array('LEC_SMIS_HOD', $roles)
            && !in_array('LEC_SMIS_DEAN', $roles)
            && in_array('LEC_SMIS_FAC_ADMIN', $roles)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check for only LECTURER and HOD roles in the user roles
     * @return bool
     */
    public static function hasOnlyLecturerHodRoles(): bool
    {
        $roles = self::getUserRoles();
        if (in_array('LEC_SMIS_LECTURER', $roles)
            && in_array('LEC_SMIS_HOD', $roles)
            && !in_array('LEC_SMIS_DEAN', $roles)
            && !in_array('LEC_SMIS_FAC_ADMIN', $roles)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check for all roles in the user roles
     * @return bool
     */
    public static function hasAllRoles(): bool
    {
        $roles = self::getUserRoles();
        if (in_array('LEC_SMIS_LECTURER', $roles)
            && in_array('LEC_SMIS_HOD', $roles)
            && in_array('LEC_SMIS_DEAN', $roles)
            && in_array('LEC_SMIS_FAC_ADMIN', $roles)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return array user roles
     */
    private static function getUserRoles(): array
    {
        return Yii::$app->session->get('roles');
    }

    /**
     * Get the exam assessment for courses with single exam components
     * @param string $marksheetId
     * @return array|ActiveRecord|null
     */
    public static function getExamAssessment(string $marksheetId)
    {
        return CourseWorkAssessment::find()->alias('CW')
            ->joinWith(['assessmentType AT'])
            ->where(['CW.MARKSHEET_ID' => $marksheetId, 'AT.ASSESSMENT_NAME' => 'EXAM'])
            ->one();
    }

    /**
     * Get coursework assessments or exam components for a marksheet
     * @param string $type
     * @param string $marksheetId
     * @return array
     */
    public static function getAssessments(string $type, string $marksheetId): array
    {
        if ($type === 'coursework') {
            $assessments = CourseWorkAssessment::find()->alias('CW')
                ->select(['CW.ASSESSMENT_ID', 'CW.MARKSHEET_ID', 'CW.DIVIDER', 'CW.ASSESSMENT_TYPE_ID'])
                ->where(['CW.MARKSHEET_ID' => $marksheetId])
                ->joinWith(['assessmentType AT' => function (ActiveQuery $q) {
                    $q->select(['AT.ASSESSMENT_TYPE_ID', 'AT.ASSESSMENT_NAME',]);
                }], true, 'INNER JOIN')
                ->andWhere(['NOT', ['AT.ASSESSMENT_NAME' => 'EXAM']])
                ->andWhere(['NOT', ['like', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT']])
                ->asArray()->all();
        } else {
            $assessments = CourseWorkAssessment::find()->alias('CW')
                ->select(['CW.ASSESSMENT_ID', 'CW.MARKSHEET_ID', 'CW.DIVIDER', 'CW.ASSESSMENT_TYPE_ID'])
                ->where(['CW.MARKSHEET_ID' => $marksheetId])
                ->joinWith(['assessmentType AT' => function (ActiveQuery $q) {
                    $q->select(['AT.ASSESSMENT_TYPE_ID', 'AT.ASSESSMENT_NAME',]);
                }], true, 'INNER JOIN')
                ->andWhere(['like', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT'])
                ->asArray()->all();
        }
        return $assessments;
    }

    /**
     * Get all assessments and exams under these marksheet
     * @param string $marksheetId
     * @return array|ActiveRecord[]
     */
    public static function getAllAssessments(string $marksheetId): array
    {
        return CourseWorkAssessment::find()->alias('CW')
            ->select(['CW.ASSESSMENT_ID', 'CW.MARKSHEET_ID', 'CW.DIVIDER', 'CW.ASSESSMENT_TYPE_ID'])
            ->where(['CW.MARKSHEET_ID' => $marksheetId])
            ->joinWith(['assessmentType AT' => function (ActiveQuery $q) {
                $q->select(['AT.ASSESSMENT_TYPE_ID', 'AT.ASSESSMENT_NAME',]);
            }], true, 'INNER JOIN')
            ->asArray()->all();
    }

    /**
     * Get assessment or exam components details
     * @param array $assessments
     * @return array
     */
    public static function getAssessmentDetails(array $assessments): array
    {
        $assessmentsTotal = 0;
        $assessmentIds = [];
        foreach ($assessments as $assessment) {
            $assessmentsTotal += $assessment['DIVIDER'];
            $arr = ['assessmentId' => $assessment['ASSESSMENT_ID']];
            $assessmentIds[] = $arr;
            unset($arr);
        }
        return [
            'assessmentIds' => $assessmentIds,
            'assessmentsTotal' => $assessmentsTotal
        ];
    }

    /**
     * Get students in a marksheet for purposes of marks consolidation
     * @param string $marksheetId
     * @return array
     */
    public static function getStudentsInAMarksheet(string $marksheetId): array
    {
        return Marksheet::find()->select(['REGISTRATION_NUMBER', 'EXAM_TYPE'])
            ->where(['MRKSHEET_ID' => $marksheetId])->asArray()->all();
    }

    /**
     * @param string $regNumber
     * @param string $marksheetId
     * @return array|ActiveRecord|null
     */
    public static function getMarksheetStudent(string $regNumber, string $marksheetId)
    {
        return Marksheet::find()->select(['EXAM_TYPE'])
            ->where(['MRKSHEET_ID' => $marksheetId, 'REGISTRATION_NUMBER' => $regNumber])
            ->one();
    }

    /**
     * Get student's marks
     * @param int $assessmentId
     * @param string $registrationNumber
     * @return array|ActiveRecord|null
     */
    public static function getStudentCourseWork(int $assessmentId, string $registrationNumber)
    {
        return StudentCoursework::find()
            ->where([
                'ASSESSMENT_ID' => $assessmentId,
                'REGISTRATION_NUMBER' => $registrationNumber
            ])->one();
    }

    /**
     * @param array $assessmentIds
     * @return array
     */
    public static function getStudentsInAssessments(array $assessmentIds): array
    {
        return StudentCoursework::find()
            ->select(['REGISTRATION_NUMBER'])
            ->where(['IN', 'ASSESSMENT_ID', $assessmentIds])
            ->distinct()
            ->asArray()
            ->all();
    }

    /**
     * Programmes in some faculties eg G have their exams divided into multiple components.
     * @param string $marksheetId
     * @return true if a course timetable belongs to these programmes. Else false.
     * @throws Exception
     */
    public static function hasMultipleExamComponents(string $marksheetId): bool
    {
        $marksheet = MarksheetDef::find()->select(['SEMESTER_ID'])->where(['MRKSHEET_ID' => $marksheetId])
            ->asArray()->one();

        $semester = Semester::find()->select(['DEGREE_CODE'])->where(['SEMESTER_ID' => $marksheet['SEMESTER_ID']])
            ->asArray()->one();

        $degree = DegreeProgramme::find()->select(['FACUL_FAC_CODE'])->where(['DEGREE_CODE' => $semester['DEGREE_CODE']])
            ->asArray()->one();

        if (in_array($degree['FACUL_FAC_CODE'], Yii::$app->params['facultiesWithMultipleExams'])) {
            return true;
        }

        return false;
    }

    /**
     * Check if a marksheet exists
     * @param string $marksheetId
     * @return bool
     */
    public static function marksheetExists(string $marksheetId): bool
    {
        if (MarksheetDef::findOne($marksheetId)) {
            return true;
        }
        return false;
    }

    /**
     * Get timetables in a faculty departments
     * @param string $facCode
     * @param string $academicYear
     * @return ArrayDataProvider
     * @throws Exception
     */
    public static function departmentTimetables(string $facCode, string $academicYear): ArrayDataProvider
    {
        $depts = Department::find()->select(['DEPT_CODE', 'DEPT_NAME'])
            ->where(['FAC_CODE' => $facCode, 'DEPT_TYPE' => 'ACADEMIC'])->asArray()->all();

        $timetables = [];
        foreach ($depts as $dept) {
            $deptCode = $dept['DEPT_CODE'];
            $timetableCount = self::totalTimetablesInDepartment($facCode, $deptCode, $academicYear);
            $timetable = [
                'deptCode' => $deptCode,
                'deptName' => $dept['DEPT_NAME'],
                'timetableCount' => $timetableCount
            ];
            $timetables[] = $timetable;
        }

        return new ArrayDataProvider([
            'allModels' => $timetables,
            'sort' => false,
            'pagination' => [
                'pageSize' => 50,
            ]
        ]);
    }

    /**
     * @param string $facCode
     * @param string $deptCode
     * @param string $academicYear
     * @return int
     * @throws Exception
     */
    private static function totalTimetablesInDepartment(string $facCode, string $deptCode, string $academicYear): int
    {
        $params = [
            ':academicYear' => $academicYear,
            ':facCode' => $facCode,
            ':deptCode' => $deptCode
        ];

        $sql = "SELECT
            COUNT(DISTINCT(substr(MUTHONI.MARKSHEET_DEF.MRKSHEET_ID, 1, instr(MUTHONI.MARKSHEET_DEF.MRKSHEET_ID, '_', 1, 5))))         
            AS NUMBER_OF_TIMETABLES
            FROM MUTHONI.MARKSHEET_DEF 
            INNER JOIN MUTHONI.SEMESTERS ON MUTHONI.MARKSHEET_DEF.SEMESTER_ID = MUTHONI.SEMESTERS.SEMESTER_ID
            INNER JOIN MUTHONI.DEGREE_PROGRAMMES ON MUTHONI.SEMESTERS.DEGREE_CODE = MUTHONI.DEGREE_PROGRAMMES.DEGREE_CODE
            INNER JOIN MUTHONI.COURSES ON MUTHONI.MARKSHEET_DEF.COURSE_ID = MUTHONI.COURSES.COURSE_ID
            INNER JOIN MUTHONI.DEPARTMENTS ON MUTHONI.COURSES.DEPT_CODE = MUTHONI.DEPARTMENTS.DEPT_CODE
            WHERE MUTHONI.MARKSHEET_DEF.MRKSHEET_ID LIKE CONCAT(:academicYear, '%')
            AND MUTHONI.DEPARTMENTS.FAC_CODE = :facCode
            AND MUTHONI.DEPARTMENTS.DEPT_CODE = :deptCode";

        $connection = Yii::$app->getDb();
        try {
            return $connection->createCommand($sql)->bindValues($params)->queryScalar();
        } catch (Exception $ex) {
            throw new Exception('Request to get the number of timetables failed.');
        }
    }

    /**
     * Get created timetables in a programme
     * @param string $deptCode
     * @param string $academicYear
     * @return array
     */
    public static function programmesTimetables(string $deptCode, string $academicYear): array
    {
        $createdMarksheetsSearch = new CreatedMarksheetsSearch();
        $createdMarksheetsProvider = $createdMarksheetsSearch->search(Yii::$app->request->queryParams, [
            'deptCode' => $deptCode,
            'academicYear' => $academicYear
        ]);

        $department = Department::find()->select(['FAC_CODE', 'DEPT_NAME'])->where(['DEPT_CODE' => $deptCode])->one();
        $faculty = Faculty::find()->select(['FACULTY_NAME'])
            ->where(['FAC_CODE' => $department->FAC_CODE])->one();

        return [
            'createdMarksheetsSearch' => $createdMarksheetsSearch,
            'createdMarksheetsProvider' => $createdMarksheetsProvider,
            'department' => $department,
            'faculty' => $faculty
        ];
    }

    /**
     * @param string $facCode
     * @param string $academicYear
     * @return ArrayDataProvider
     */
    public static function courseAllocationsInDepartments(string $facCode, string $academicYear): ArrayDataProvider
    {
        // Get the number of lecturers per department in a faculty
        $departments = EmpVerifyView::find()
            ->select(['DEPT_CODE', 'DEPT_NAME', 'COUNT(*) AS staffNumber'])
            ->where(['STATUS_DESC' => 'ACTIVE', 'JOB_CADRE' => 'ACADEMIC'])
            ->andWhere(['FAC_CODE' => $facCode])
            ->groupBy(['DEPT_CODE', 'DEPT_NAME'])
            ->orderBy(['DEPT_CODE' => SORT_ASC])
            ->asArray()->all();

        /**
         * Get all lectures in EACH department,
         * who have been allocated at least 1 course for the academic year given
         */
        $departmentAllocations = [];
        foreach ($departments as $department) {
            $deptCode = $department['DEPT_CODE'];

            $lecturersAllocated = CourseAssignment::find()->alias('CW')->select(['CW.PAYROLL_NO'])
                ->where(['LIKE', 'CW.MRKSHEET_ID', $academicYear . '%', false])
                ->joinWith(['staff ST' => function (ActiveQuery $q) {
                    $q->select([
                        'ST.PAYROLL_NO',
                        'ST.DEPT_CODE'
                    ]);
                }], true, 'INNER JOIN')
                ->andWhere(['ST.DEPT_CODE' => $deptCode])
                ->distinct()->asArray()->all();

            $lecturersWithCourses = EmpVerifyView::find()
                ->where(['DEPT_CODE' => $deptCode])
                ->andWhere(['IN', 'PAYROLL_NO', $lecturersAllocated])
                ->count();
            $departmentAllocation = [
                'deptCode' => $deptCode,
                'deptName' => $department['DEPT_NAME'],
                'lecturers' => $department['staffNumber'],
                'lecturersWithCourses' => $lecturersWithCourses
            ];
            $departmentAllocations[] = $departmentAllocation;
        }

        return new ArrayDataProvider([
            'allModels' => $departmentAllocations,
            'sort' => false,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
    }

    /**
     * Number of courses allocated per lecturer
     * @param string $deptCode
     * @param string $academicYear
     * @return array
     */
    public static function numberOfCourseAllocationsPerLecturer(string $deptCode, string $academicYear): array
    {
        // Get number of courses per lecturer in a department for the academic year
        $courseSearch = new NoOfAllocatedCoursesPerLecturerSearch();
        $coursesProvider = $courseSearch->search(Yii::$app->request->queryParams, [
            'academicYear' => $academicYear,
            'deptCode' => $deptCode
        ]);

        $department = Department::find()->select(['FAC_CODE', 'DEPT_NAME'])
            ->where(['DEPT_CODE' => $deptCode])->one();

        $faculty = Faculty::find()->select(['FAC_CODE', 'FACULTY_NAME'])
            ->where(['FAC_CODE' => $department->FAC_CODE])->one();

        return [
            'courseSearch' => $courseSearch,
            'coursesProvider' => $coursesProvider,
            'department' => $department,
            'faculty' => $faculty
        ];
    }

    /**
     * Get courses for each lecturer
     * @param string $payroll
     * @param string $academicYear
     * @return array
     */
    public static function courseAllocationsPerLecturer(string $payroll, string $academicYear): array
    {
        // Get courses per lecturer in a department for the academic year
        $courseSearch = new AllocatedCoursesPerLecturerSearch();
        $coursesProvider = $courseSearch->search(Yii::$app->request->queryParams, [
            'academicYear' => $academicYear,
            'payroll' => $payroll
        ]);

        $staff = EmpVerifyView::find()->select([
            'DEPT_CODE',
            'DEPT_NAME',
            'EMP_TITLE',
            'SURNAME',
            'OTHER_NAMES'
        ])->where(['PAYROLL_NO' => $payroll])->asArray()->one();

        return [
            'courseSearch' => $courseSearch,
            'coursesProvider' => $coursesProvider,
            'staff' => $staff
        ];
    }

    /**
     * Get cw created per department
     * @param string $facCode
     * @param string $academicYear
     * @return ArrayDataProvider
     */
    public static function courseWorkDefinitionInDepartments(string $facCode, string $academicYear): ArrayDataProvider
    {
        $departments = Department::find()->select(['DEPT_CODE', 'DEPT_NAME'])
            ->where(['FAC_CODE' => $facCode, 'DEPT_TYPE' => 'ACADEMIC'])->asArray()->all();

        // Get total no.of course works created in EACH department
        $courseWorks = [];
        foreach ($departments as $department) {
            $deptCode = $department['DEPT_CODE'];

            $courseworkCount = self::totalCourseworkDefined([
                'academicYear' => $academicYear,
                'facCode' => null,
                'deptCode' => $deptCode
            ]);

            $courseWork = [
                'deptCode' => $deptCode,
                'deptName' => $department['DEPT_NAME'],
                'courseworkCount' => $courseworkCount
            ];
            $courseWorks[] = $courseWork;
        }

        return new ArrayDataProvider([
            'allModels' => $courseWorks,
            'sort' => false,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
    }

    /**
     * Get course works defined
     * @param array $params
     * @return bool|int|string|null
     */
    private static function totalCourseworkDefined(array $params)
    {
        $courseWorkCount = CourseWorkAssessment::find()->alias('CW')
            ->select(['CW.MARKSHEET_ID'])
            ->joinWith(['assessmentType AT'], true, 'INNER JOIN')
            ->joinWith(['marksheetDef MD' => function (ActiveQuery $q) {
                $q->select([
                    'MD.MRKSHEET_ID',
                    'MD.COURSE_ID',
                    'MD.SEMESTER_ID'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['marksheetDef.semester SM' => function (ActiveQuery $q) {
                $q->select([
                    'SM.SEMESTER_ID',
                    'SM.ACADEMIC_YEAR'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['marksheetDef.course CS' => function (ActiveQuery $q) {
                $q->select([
                    'CS.COURSE_ID',
                    'CS.DEPT_CODE'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['marksheetDef.course.dept DEPT' => function (ActiveQuery $q) {
                $q->select([
                    'DEPT.DEPT_CODE',
                    'DEPT.FAC_CODE'
                ]);
            }], true, 'INNER JOIN')
            ->where(['SM.ACADEMIC_YEAR' => $params['academicYear']])
            ->andWhere(['NOT', ['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT']])
            ->andWhere(['NOT', ['AT.ASSESSMENT_NAME' => 'EXAM']]);

        if (!is_null($params['deptCode']) && is_null($params['facCode'])) {
            $courseWorkCount->andWhere(['DEPT.DEPT_CODE' => $params['deptCode']]);
        }

        if (is_null($params['deptCode']) && !is_null($params['facCode'])) {
            $courseWorkCount->andWhere(['DEPT.FAC_CODE' => $params['facCode']]);
        }

        return $courseWorkCount->count();
    }

    /**
     * Get cw created per programme course
     * @param string $deptCode
     * @param string $academicYear
     * @return array
     */
    public static function programmeCourseWorkDefinition(string $deptCode, string $academicYear): array
    {
        $programmeCourseWorkSearch = new ProgrammeCourseWorkSearch();
        $programmeCourseWorkProvider = $programmeCourseWorkSearch->search(Yii::$app->request->queryParams, [
            'deptCode' => $deptCode,
            'academicYear' => $academicYear
        ]);

        $department = Department::find()->select(['FAC_CODE', 'DEPT_NAME'])
            ->where(['DEPT_CODE' => $deptCode])->one();
        $faculty = Faculty::find()->select(['FACULTY_NAME'])
            ->where(['FAC_CODE' => $department->FAC_CODE])->one();

        return [
            'programmeCourseWorkSearch' => $programmeCourseWorkSearch,
            'programmeCourseWorkProvider' => $programmeCourseWorkProvider,
            'department' => $department,
            'faculty' => $faculty
        ];
    }

    /**
     * Get cw created per course
     * @param string $marksheetId
     * @param string $deptCode
     * @param string $academicYear
     * @return array
     * @throws Exception
     */
    public static function courseWorkDefinitions(string $marksheetId, string $deptCode, string $academicYear): array
    {
        if (empty($marksheetId) || empty($deptCode) || empty($academicYear)) {
            throw new Exception('All report parameters must be provided');
        }

        $assessmentsSearch = new CourseworkAssessmentSearch();
        $assessmentsProvider = $assessmentsSearch->search(['marksheetId' => $marksheetId, 'type' => null]);

        $marksheetDef = MarksheetDef::find()->select(['COURSE_ID'])->where(['MRKSHEET_ID' => $marksheetId])
            ->one();

        $course = Course::find()->select(['COURSE_CODE', 'COURSE_NAME'])
            ->where(['COURSE_ID' => $marksheetDef->COURSE_ID])->one();

        $department = Department::find()->select(['FAC_CODE', 'DEPT_NAME'])
            ->where(['DEPT_CODE' => $deptCode])->one();

        return [
            'assessmentsSearch' => $assessmentsSearch,
            'assessmentsProvider' => $assessmentsProvider,
            'course' => $course,
            'department' => $department
        ];
    }

    /**
     * Check if a course requires coursework
     * @param string $marksheetId
     * @return bool
     */
    public static function requiresCoursework(string $marksheetId): bool
    {
        $mkModel = MarksheetDef::find()->select(['COURSE_ID', 'SEMESTER_ID'])
            ->where(['MRKSHEET_ID' => $marksheetId])->asArray()->one();

        $course = Course::find()->select(['HAS_COURSE_WORK'])->where(['COURSE_ID' => $mkModel['COURSE_ID']])
            ->asArray()->one();

        if (intval($course['HAS_COURSE_WORK']) === 1) {
            return true;
        }
        return false;
    }

    /**
     * Get details of a marksheet
     * @param string $marksheetId
     * @return array
     */
    public static function marksheetDetails(string $marksheetId): array
    {
        $mkModel = MarksheetDef::find()->select(['COURSE_ID', 'SEMESTER_ID'])->where(['MRKSHEET_ID' => $marksheetId])
            ->asArray()->one();

        $courseModel = Course::find()->select(['COURSE_CODE', 'COURSE_NAME', 'DEPT_CODE'])
            ->where(['COURSE_ID' => $mkModel['COURSE_ID']])->asArray()->one();

        $dept = Department::find()->select(['DEPT_CODE', 'FAC_CODE'])->where(['DEPT_CODE' => $courseModel['DEPT_CODE']])
            ->asArray()->one();

        $semester = Semester::find()->select(['SEMESTER_TYPE'])->where(['SEMESTER_ID' => $mkModel['SEMESTER_ID']])
            ->asArray()->one();

        return [
            'courseCode' => $courseModel['COURSE_CODE'],
            'courseName' => $courseModel['COURSE_NAME'],
            'semesterType' => $semester['SEMESTER_TYPE'],
            'deptCode' => $dept['DEPT_CODE'],
            'facCode' => $dept['FAC_CODE']
        ];
    }

    /**
     * @param string $marksheetId
     * @param string|null $assessmentId
     * @return array details to be printed on class performance reports
     */
    public static function performanceReportDetails(string $marksheetId, string $assessmentId = null): array
    {
        $marksheetDef = MarksheetDef::find()->select(['SEMESTER_ID', 'GROUP_CODE', 'COURSE_ID'])
            ->where(['MRKSHEET_ID' => $marksheetId])->asArray()->one();

        $course = Course::find()->select(['COURSE_CODE', 'COURSE_NAME'])
            ->where(['COURSE_ID' => $marksheetDef['COURSE_ID']])->asArray()->one();

        $semester = Semester::find()->select(['ACADEMIC_YEAR', 'DEGREE_CODE', 'LEVEL_OF_STUDY', 'SEMESTER_CODE'])
            ->where(['SEMESTER_ID' => $marksheetDef['SEMESTER_ID']])->asArray()->one();

        $degree = DegreeProgramme::find()->select(['DEGREE_CODE', 'DEGREE_NAME'])->where(['DEGREE_CODE' => $semester['DEGREE_CODE']])
            ->asArray()->one();

        $level = LevelOfStudy::find()->select(['NAME'])
            ->where(['LEVEL_OF_STUDY' => $semester['LEVEL_OF_STUDY']])->asArray()->one();

        $group = Group::find()->select(['GROUP_NAME'])->where(['GROUP_CODE' => $marksheetDef['GROUP_CODE']])
            ->asArray()->one();

        $semesterDesc = Semester::find()->alias('SM')
            ->select(['SM.SEMESTER_ID', 'SM.SEMESTER_CODE', 'SM.DESCRIPTION_CODE'])
            ->joinWith(['semesterDescription SD' => function ($q) {
                $q->select(['SD.DESCRIPTION_CODE', 'SD.SEMESTER_DESC']);
            }], true, 'INNER JOIN')
            ->where(['SM.SEMESTER_ID' => $marksheetDef['SEMESTER_ID']])
            ->asArray()->one();
        $semesterFullName = $semesterDesc['SEMESTER_CODE'] . ' (' . strtoupper($semesterDesc['semesterDescription']['SEMESTER_DESC']) . ')';

        $assessmentName = '';
        if (!is_null($assessmentId)) {
            $assessment = CourseWorkAssessment::find()->alias('AS')->joinWith(['assessmentType AT'])
                ->where(['AS.ASSESSMENT_ID' => $assessmentId])->asArray()->one();
            $assessmentName = str_replace('EXAM_COMPONENT ', '', $assessment['assessmentType']['ASSESSMENT_NAME']);
        }

        return [
            'marksheetId' => $marksheetId,
            'academicYear' => $semester['ACADEMIC_YEAR'],
            'degreeCode' => $degree['DEGREE_CODE'],
            'degreeName' => $degree['DEGREE_NAME'],
            'level' => $level['NAME'],
            'group' => $group['GROUP_NAME'],
            'semesterFullName' => $semesterFullName,
            'courseCode' => $course['COURSE_CODE'],
            'courseName' => $course['COURSE_NAME'],
            'assessmentName' => $assessmentName
        ];
    }

    /**
     * Update the consolidated flag on all marks of assessments under a marksheet
     * @param string $marksheetId
     * @return void
     * @throws Exception
     */
    public static function updateMarksConsolidatedFlag(string $marksheetId)
    {
        $assessments = self::getAllAssessments($marksheetId);
        $details = self::getAssessmentDetails($assessments);
        $marksheetAssessments = $details['assessmentIds'];

        $marksheetAssessmentsIds = [];
        foreach ($marksheetAssessments as $marksheetAssessment) {
            $assessmentId = $marksheetAssessment['assessmentId'];
            if (StudentCoursework::find()->where(['ASSESSMENT_ID' => $assessmentId])->count() > 0) {
                $marksheetAssessmentsIds[] = $assessmentId;
            }
        }

        if (count($marksheetAssessmentsIds) > 0) {
            $updatedCount = StudentCoursework::updateAll(['IS_CONSOLIDATED' => 0], ['IN', 'ASSESSMENT_ID', $marksheetAssessmentsIds]);
            if ($updatedCount === 0) {
                throw new Exception('Failed to update marks when recalculating weights.');
            }
        }
    }

    /**
     * Get the exam and coursework ratios.
     * @param string $marksheetId
     * @return array
     * @throws Exception
     */
    public static function getRatios(string $marksheetId): array
    {
        $courseCode = self::marksheetDetails($marksheetId)['courseCode'];

        /**
         * We have some courses with deviating ratios from the program ratios. They have their ratios defined in a
         * separate table. We first check if a course is among these.
         */
        $courseRatio = MarksheetRatio::find()->where(['COURSE_CODE' => $courseCode])->one();
        if (!is_null($courseRatio)) {
            return [
                'examRatio' => $courseRatio->EXAM_RATIO,
                'cwRatio' => $courseRatio->CW_RATIO
            ];
        }

        if (self::requiresCoursework($marksheetId)) {
            $mkModel = MarksheetDef::find()->select(['SEMESTER_ID'])->where(['MRKSHEET_ID' => $marksheetId])
                ->asArray()->one();

            $semester = Semester::find()->select(['DEGREE_CODE'])->where(['SEMESTER_ID' => $mkModel['SEMESTER_ID']])
                ->asArray()->one();

            $degreeAssessmentModel = DegreeAssessment::find()
                ->select(['EXAM_RATIO', 'COURSE_WORK_RATIO'])
                ->where(['DEGREE_CODE' => $semester['DEGREE_CODE']])
                ->asArray()->one();

            if (empty($degreeAssessmentModel) || is_null($degreeAssessmentModel['EXAM_RATIO']) ||
                is_null($degreeAssessmentModel['COURSE_WORK_RATIO'])) {
                throw new Exception('Exam and Coursework ratios for this marksheet are not defined.');
            }

            $examRatio = $degreeAssessmentModel['EXAM_RATIO'];
            $cwRatio = $degreeAssessmentModel['COURSE_WORK_RATIO'];
        } else {
            $examRatio = 100;
            $cwRatio = null;
        }

        return [
            'examRatio' => $examRatio,
            'cwRatio' => $cwRatio
        ];
    }

    /**
     * Log messages
     * @param string $message message to log
     * @param string $category category of a message
     * @param string $method method to use when logging a message
     * @return void
     * @throws Exception
     */
    public static function logMessage(string $message, string $category, string $method = 'info')
    {
        echo $message . PHP_EOL;

        if ($method === 'info') {
            Yii::info($message, $category);
        } elseif ($method === 'error') {
            Yii::error($message, $category);
        } elseif ($method === 'warning') {
            Yii::warning($message, $category);
        } else {
            throw new Exception('Specify the correct logging method.');
        }
    }

    /**
     * Get a record from LEC_MARKSHEETS table
     * @param string $marksheetId
     * @param string $regNumber
     * @return array|ActiveRecord|null
     */
    public static function getTempMarksheet(string $marksheetId, string $regNumber)
    {
        return TempMarksheet::find()
            ->where(['MRKSHEET_ID' => $marksheetId, 'REGISTRATION_NUMBER' => $regNumber])
            ->one();
    }

    /**
     * For each student registered in a marksheet, consolidate marks in each assessment,
     * convert out of the cw ratio and save the marks in the temp marksheet.
     * @param string $marksheetId
     * @param string|null $applicationType
     * @return void
     * @throws Exception
     */
    public static function consolidateCwMarks(string $marksheetId, string $applicationType = null)
    {
        $assessments = SmisHelper::getAssessments('coursework', $marksheetId);
        $details = SmisHelper::getAssessmentDetails($assessments);
        $assessmentIds = $details['assessmentIds'];

        $marksheetDetails = SmisHelper::marksheetDetails($marksheetId);

        $cwAssessmentIds = [];
        foreach ($assessments as $assessment) {
            $cwAssessmentIds[] = $assessment['ASSESSMENT_ID'];
        }

        $students = self::getStudentsInAssessments($cwAssessmentIds);

        foreach ($students as $student) {
            $assessmentMarksScored = null;

            foreach ($assessmentIds as $assessment) {
                $studentCw = SmisHelper::getStudentCourseWork($assessment['assessmentId'], $student['REGISTRATION_NUMBER']);

                if (is_null($studentCw) || is_null($studentCw->RAW_MARK)) {
                    continue;
                }

                $cwAssessment = CourseWorkAssessment::find()->select(['WEIGHT', 'DIVIDER'])
                    ->where(['ASSESSMENT_ID' => $assessment['assessmentId']])->asArray()->one();

                $assessmentMarksScored += ($studentCw->RAW_MARK / $cwAssessment['DIVIDER']) * $cwAssessment['WEIGHT'];
            }

            if ($marksheetStudent = self::getMarksheetStudent($student['REGISTRATION_NUMBER'], $marksheetId)) {

                $tempMarksheet = self::getTempMarksheet($marksheetId, $student['REGISTRATION_NUMBER']);

                if (is_null($tempMarksheet)) {
                    $tempMarksheet = new TempMarksheet();
                    $tempMarksheet->ENTRY_DATE = new Expression('CURRENT_DATE');
                }

                $tempMarksheet->MRKSHEET_ID = $marksheetId;
                $tempMarksheet->REGISTRATION_NUMBER = $student['REGISTRATION_NUMBER'];
                $tempMarksheet->COURSE_CODE = $marksheetDetails['courseCode'];
                $tempMarksheet->EXAM_TYPE = $marksheetStudent->EXAM_TYPE;;
                $tempMarksheet->COURSE_MARKS = (is_null($assessmentMarksScored)) ? null : round($assessmentMarksScored, 2);
                $tempMarksheet->MARKS_COMPLETE = self::MARKS_NOT_COMPLETE;
                $tempMarksheet->PUBLISH_STATUS = self::MARKS_NOT_PUBLISHED;
                $tempMarksheet->LAST_UPDATE = new Expression('CURRENT_DATE');
                $tempMarksheet->save();

                if (!empty($tempMarksheet->getErrors())) {
                    throw new Exception('Error updating temp marksheet for ' . $marksheetId);
                }
            }

            foreach ($assessmentIds as $assessment) {
                $studentCw = SmisHelper::getStudentCourseWork($assessment['assessmentId'],
                    $student['REGISTRATION_NUMBER']);

                if (is_null($studentCw)) {
                    continue;
                }

                $studentCw->IS_CONSOLIDATED = self::MARKS_CONSOLIDATED;
                $studentCw->save();

                if (!empty($studentCw->getErrors())) {
                    throw new Exception('Error updating consolidated flag for ' . $marksheetId);
                }
            }

            self::markMarksAsNotTransferred($marksheetId, $student['REGISTRATION_NUMBER']);
        }
    }

    /**
     * @throws Exception
     */
    private static function markMarksAsNotTransferred(string $marksheetId, string $regNum)
    {
        $marksheetStudent = Marksheet::find()->where(['MRKSHEET_ID' => $marksheetId, 'REGISTRATION_NUMBER' => $regNum])
            ->one();

        /**
         * If a student has marks but no longer in the marksheet, do nothing
         */
        if ($marksheetStudent) {
            $marksheetStudent->TRANSFER_STATUS = self::MARKS_NOT_TRANSFERRED;
            if (!$marksheetStudent->save()) {
                $message = $regNum . ' in ' . $marksheetId . '  failed to change TRANSFER STATUS.';

                if (!empty($marksheetStudent->getErrors())) {
                    $message = $regNum . ' in ' . $marksheetId . ' failed to change TRANSFER STATUS. Errors: ' .
                        json_encode($marksheetStudent->getErrors());
                }

                SmisHelper::logMessage($message, __METHOD__);
            }
        }
    }

    /**
     * This method is called on marksheets with single exam components.
     * @param string $marksheetId
     * @param string|null $applicationType
     * @return void
     * @throws Exception
     */
    public static function consolidateSingleExamMarks(string $marksheetId, string $applicationType = null)
    {
        $examAssessment = self::getExamAssessment($marksheetId);

        if (is_null($examAssessment)) {
            if ($applicationType === 'console') {
                self::logMessage('Exam for ' . $marksheetId . ' is not found', __METHOD__);
                return;
            }
        }

        $examAssessmentId = $examAssessment->ASSESSMENT_ID;

        $students = self::getStudentsInAssessments([$examAssessmentId]);

        foreach ($students as $student) {
            $studentCw = SmisHelper::getStudentCourseWork($examAssessmentId, $student['REGISTRATION_NUMBER']);

            if (is_null($studentCw->RAW_MARK)) {
                $examMarksScored = null;
            } else {
                $examMarksScored = $studentCw->RAW_MARK;
            }

            if ($marksheetStudent = self::getMarksheetStudent($student['REGISTRATION_NUMBER'], $marksheetId)) {
                self::updateExamMarks([
                    'marksheetId' => $marksheetId,
                    'regNumber' => $student['REGISTRATION_NUMBER'],
                    'examType' => $marksheetStudent->EXAM_TYPE,
                    'examMarksScored' => $examMarksScored
                ]);
            }

            $studentCw->IS_CONSOLIDATED = self::MARKS_CONSOLIDATED;
            $studentCw->save();

            if (!empty($studentCw->getErrors())) {
                throw new Exception('Error updating consolidated flag for ' . $marksheetId);
            }
        }
    }

    /**
     * This method is called on marksheets with many exam components.
     * @param string $marksheetId
     * @param string|null $applicationType
     * @return void
     * @throws Exception
     */
    public static function consolidateMultipleExamMarks(string $marksheetId, string $applicationType = null)
    {
        $assessments = SmisHelper::getAssessments('exam', $marksheetId);
        $details = SmisHelper::getAssessmentDetails($assessments);
        $componentIds = $details['assessmentIds'];

        $examComponentIds = [];
        foreach ($assessments as $assessment) {
            $examComponentIds[] = $assessment['ASSESSMENT_ID'];
        }

        $students = self::getStudentsInAssessments($examComponentIds);

        foreach ($students as $student) {
            $examMarksScored = null;

            foreach ($componentIds as $component) {
                $studentCw = SmisHelper::getStudentCourseWork($component['assessmentId'], $student['REGISTRATION_NUMBER']);

                if (is_null($studentCw) || is_null($studentCw->RAW_MARK)) {
                    continue;
                }

                $examComponent = CourseWorkAssessment::find()->select(['WEIGHT', 'DIVIDER'])
                    ->where(['ASSESSMENT_ID' => $component['assessmentId']])->asArray()->one();

                $examMarksScored += ($studentCw->RAW_MARK / $examComponent['DIVIDER']) * $examComponent['WEIGHT'];
            }

            if ($marksheetStudent = self::getMarksheetStudent($student['REGISTRATION_NUMBER'], $marksheetId)) {
                self::updateExamMarks([
                    'marksheetId' => $marksheetId,
                    'regNumber' => $student['REGISTRATION_NUMBER'],
                    'examType' => $marksheetStudent->EXAM_TYPE,
                    'examMarksScored' => $examMarksScored
                ]);
            }

            foreach ($componentIds as $component) {
                $studentCw = SmisHelper::getStudentCourseWork($component['assessmentId'], $student['REGISTRATION_NUMBER']);

                if (is_null($studentCw)) {
                    continue;
                }

                $studentCw->IS_CONSOLIDATED = self::MARKS_CONSOLIDATED;
                $studentCw->save();

                if (!empty($studentCw->getErrors())) {
                    throw new Exception('Error updating consolidated flag for ' . $marksheetId);
                }
            }
        }
    }

    /**
     * Check if marksheet belongs to a supplementary semester.
     * @param string $marksheetId
     * @return bool
     */
    public static function isSupplementary(string $marksheetId): bool
    {
        $mkModel = MarksheetDef::find()->select(['COURSE_ID', 'SEMESTER_ID'])
            ->where(['MRKSHEET_ID' => $marksheetId])->asArray()->one();

        $semester = Semester::find()->select(['SEMESTER_TYPE'])
            ->where(['SEMESTER_ID' => $mkModel['SEMESTER_ID']])->asArray()->one();

        if ($semester['SEMESTER_TYPE'] === 'SUPPLEMENTARY') {
            return true;
        }

        return false;
    }

    /**
     * Update student exam and final marks and grading.
     * @param array $updateData
     * @return void
     * @throws Exception on failure to create/update a record in LEC_MARKSHEETS
     */
    private static function updateExamMarks(array $updateData)
    {
        $marksheetId = $updateData['marksheetId'];
        $regNumber = $updateData['regNumber'];
        $examType = $updateData['examType'];
        $examMarksScored = $updateData['examMarksScored'];

        $marksheetDetails = self::getMarksheetDetails($marksheetId);

        $tempMarksheet = self::getTempMarksheet($marksheetId, $regNumber);
        if (is_null($tempMarksheet)) {
            $tempMarksheet = new TempMarksheet();
            $tempMarksheet->ENTRY_DATE = new Expression('CURRENT_DATE');
        }
        $tempMarksheet->MRKSHEET_ID = $marksheetId;
        $tempMarksheet->REGISTRATION_NUMBER = $regNumber;
        $tempMarksheet->COURSE_CODE = $marksheetDetails['courseCode'];
        $tempMarksheet->EXAM_TYPE = $examType;

        // For special exams, read the course marks for the latest entry of a student for the same course
        if ($examType === 'SPECIAL') {
            $temp = TempMarksheet::find()->select(['COURSE_MARKS'])
                ->where(['COURSE_CODE' => $marksheetDetails['courseCode'], 'REGISTRATION_NUMBER' => $regNumber])
                ->andWhere(['not', ['COURSE_MARKS' => null]])
                ->orderBy(['LAST_UPDATE' => SORT_DESC])
                ->one();
            if ($temp) {
                $tempMarksheet->COURSE_MARKS = $temp->COURSE_MARKS;
            }
        }

        $tempMarksheet->EXAM_MARKS = (is_null($examMarksScored)) ? null : round($examMarksScored, 2);
        $tempMarksheet->PUBLISH_STATUS = self::MARKS_NOT_PUBLISHED;
        $tempMarksheet->LAST_UPDATE = new Expression('CURRENT_DATE');

        $requiresCoursework = SmisHelper::requiresCoursework($marksheetId);

        $isSupplementary = false;
        if ($tempMarksheet->EXAM_TYPE === 'SUPP') {
            $isSupplementary = true;
        }

        /**
         * Try to grade the student
         * First, default these values to null:
         */
        $tempMarksheet->FINAL_MARKS = null;
        $tempMarksheet->GRADE = null;
        $tempMarksheet->MARKS_COMPLETE = self::MARKS_NOT_COMPLETE;

        /**
         * For example SPH101:
         * is FA and requires that we have both CAT and Exam marks
         */
        if (!$isSupplementary && $requiresCoursework && !is_null($tempMarksheet->COURSE_MARKS) && !is_null($tempMarksheet->EXAM_MARKS)) {
            $tempMarksheet->FINAL_MARKS = $tempMarksheet->COURSE_MARKS + $tempMarksheet->EXAM_MARKS;
            $tempMarksheet->MARKS_COMPLETE = self::MARKS_COMPLETE;
        } /**
         * For example SPH102:
         * is FA and requires that we only have Exam marks
         */
        elseif (!$isSupplementary && !$requiresCoursework && !is_null($tempMarksheet->EXAM_MARKS)) {
            $tempMarksheet->FINAL_MARKS = $tempMarksheet->EXAM_MARKS;
            $tempMarksheet->MARKS_COMPLETE = self::MARKS_COMPLETE;
        } /**
         * For example SPH103:
         * is supplementary we only have Exam marks
         */
        elseif ($isSupplementary && !is_null($tempMarksheet->EXAM_MARKS)) {
            $passmark = SmisHelper::getPassmark($tempMarksheet->MRKSHEET_ID);
            if (intval($tempMarksheet->EXAM_MARKS) >= $passmark) {
                $tempMarksheet->FINAL_MARKS = $passmark;
            } else {
                $tempMarksheet->FINAL_MARKS = $tempMarksheet->EXAM_MARKS;
            }

            $tempMarksheet->MARKS_COMPLETE = self::MARKS_COMPLETE;
        }

        if ($tempMarksheet->MARKS_COMPLETE === self::MARKS_COMPLETE) {
            $tempMarksheet = self::grade($tempMarksheet, $isSupplementary);
        }

        $tempMarksheet->save();

        if (!empty($tempMarksheet->getErrors())) {
            throw new Exception('Error updating grades ' . $marksheetId);
        }

        if (SmisHelper::isAProjectCourse($tempMarksheet->MRKSHEET_ID)) {
            self::updateProjectMarksAndGrade($tempMarksheet);
        }

        self::markMarksAsNotTransferred($marksheetId, $regNumber);
    }

    /**
     * Get the marksheet details
     * @param string $marksheetId
     * @return array marksheet details
     * @throws Exception
     */
    public static function getMarksheetDetails(string $marksheetId): array
    {
        $mkModel = MarksheetDef::findOne($marksheetId);
        $courseId = $mkModel->COURSE_ID;
        $courseCode = $mkModel->course->COURSE_CODE;
        $courseName = $mkModel->course->COURSE_NAME;

        return [
            'courseId' => $courseId,
            'courseCode' => $courseCode,
            'courseName' => $courseName
        ];
    }

    /**
     * @param TempMarksheet $tempMarksheet
     * @return void
     * @throws Exception
     */
    public static function updateProjectMarksAndGrade(TempMarksheet $tempMarksheet)
    {
        $marksheet = MarksheetDef::find()->select(['COURSE_ID'])->where(['MRKSHEET_ID' => $tempMarksheet->MRKSHEET_ID])
            ->asArray()->one();
        $courseId = $marksheet['COURSE_ID'];

        $projectDescription = ProjectDescription::find()
            ->where([
                'REGISTRATION_NUMBER' => $tempMarksheet->REGISTRATION_NUMBER,
                'PROJECT_CODE' => $courseId
            ])->one();

        if ($projectDescription) {
            $projectDescription->MARKS = $tempMarksheet->FINAL_MARKS;
            $projectDescription->GRADE = $tempMarksheet->GRADE;
            if (!$projectDescription->save()) {
                if (!$projectDescription->validate()) {
                    throw new Exception(SmisHelper::getModelErrors($projectDescription->getErrors()));
                }
                throw new Exception('Failed to update project marks and grade');
            }
        }
    }

    /**
     * get passmark of a course
     * @param string $marksheetId
     * @return int
     * @throws Exception
     */
    public static function getPassmark(string $marksheetId): int
    {
        $passmark = null;

        $grades = self::getGradingDetails($marksheetId);

        foreach ($grades as $grade) {
            $gradingId = intval($grade['gradingId']);

            if ($gradingId === 1 || $gradingId === 2 || $gradingId === 5 || $gradingId === 6 || $gradingId === 9 ||
                $gradingId === 12 || $gradingId === 15) {
                if ($grade['legend'] === 'PASS') {
                    $passmark = $grade['lowerBound'];
                    break;
                }
            } elseif ($gradingId === 4 || $gradingId === 7 || $gradingId === 8) {
                if ($grade['legend'] === 'SATISFACTORY') {
                    $passmark = $grade['lowerBound'];
                    break;
                }
            } elseif ($gradingId === 13 || $gradingId === 16) {
                $passmark = 50;
                break;
            }
        }

        if (empty($passmark)) {
            throw new Exception('The pass mark for this course is missing.');
        }

        return intval($passmark);
    }

    /**
     * Determine which grading to use
     * @param TempMarksheet $tempMarksheet
     * @param bool $isSupplementary
     * @return TempMarksheet
     * @throws Exception
     */
    private static function grade(TempMarksheet $tempMarksheet, bool $isSupplementary): TempMarksheet
    {
        $grades = self::getGradingDetails($tempMarksheet->MRKSHEET_ID);

        $passmark = self::getPassmark($tempMarksheet->MRKSHEET_ID);

        foreach ($grades as $grade) {
            if ($isSupplementary && intval($tempMarksheet->FINAL_MARKS) >= $passmark) {
                if (self::hasMultipleExamComponents($tempMarksheet->MRKSHEET_ID)) {
                    if ($grade['legend'] === 'PASS') {
                        $tempMarksheet->GRADE = trim($grade['grade']) . '*    ';
                        break;
                    } else if ($grade['legend'] === 'SATISFACTORY') {
                        $tempMarksheet->GRADE = trim($grade['grade']) . '*    ';
                        break;
                    }
                } else {
                    if ($grade['legend'] === 'FAIL') {
                        $tempMarksheet->GRADE = trim($grade['grade']) . '*    ';
                        break;
                    }
                }
            } else {
                if ($tempMarksheet->FINAL_MARKS >= $grade['lowerBound'] && $tempMarksheet->FINAL_MARKS <= $grade['upperBound']) {
                    $tempMarksheet->GRADE = $grade['grade'];
                    break;
                }
            }
        }

        return $tempMarksheet;
    }

    /**
     * Get the grading system for the programme under which a marksheet belongs.
     * @param string $marksheetId marksheet identifier
     * @return array programme grades
     * @todo move to helpers
     */
    public static function getGradingDetails(string $marksheetId): array
    {
        $marksheet = MarksheetDef::find()->alias('MD')
            ->select([
                'MD.MRKSHEET_ID',
                'MD.SEMESTER_ID'
            ])
            ->joinWith(['semester SM' => function (ActiveQuery $q) {
                $q->select([
                    'SM.SEMESTER_ID',
                    'SM.DEGREE_CODE'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['semester.degreeProgramme DEG' => function (ActiveQuery $q) {
                $q->select([
                    'DEG.DEGREE_CODE',
                    'DEG.GRADINGSYSTEM'
                ]);
            }], true, 'INNER JOIN')
            ->joinWith(['semester.degreeProgramme.gradingSystem GS' => function (ActiveQuery $q) {
                $q->select(['GS.GRADINGCODE']);
            }], true, 'INNER JOIN')
            ->where(['MD.MRKSHEET_ID' => $marksheetId])
            ->one();

        $gradingSystem = $marksheet->semester->degreeProgramme->gradingSystem;
        $gradingDetails = GradingSystemDetails::find()
            ->select([
                'GRADINGID',
                'GRADE',
                'UPPERBOUND',
                'LOWERBOUND',
                'LEGEND'
            ])
            ->where(['GRADINGID' => $gradingSystem->GRADINGCODE])
            ->asArray()
            ->all();

        $grades = [];
        foreach ($gradingDetails as $gradingDetail) {
            $grades[] = [
                'gradingId' => $gradingDetail['GRADINGID'],
                'grade' => $gradingDetail['GRADE'],
                'upperBound' => $gradingDetail['UPPERBOUND'],
                'lowerBound' => $gradingDetail['LOWERBOUND'],
                'legend' => $gradingDetail['LEGEND'],
            ];
        }
        return $grades;
    }

    /**
     * Update and read the consolidated marks for a marksheet
     * @param string $marksheetId
     * @return void
     * @throws Exception
     */
    public static function updateAndReadConsolidatedMarks(string $marksheetId)
    {
        $assessments = self::getAllAssessments($marksheetId);
        $details = self::getAssessmentDetails($assessments);
        $marksheetAssessments = $details['assessmentIds'];

        $marksheetAssessmentsIds = [];
        foreach ($marksheetAssessments as $marksheetAssessment) {
            $marksheetAssessmentsIds[] = $marksheetAssessment['assessmentId'];
        }

        $notConsolidatedCount = StudentCoursework::find()->where(['IS_CONSOLIDATED' => 0])
            ->andWhere(['IN', 'ASSESSMENT_ID', $marksheetAssessmentsIds])->count();

        if ($notConsolidatedCount > 0) {
            self::consolidateCwMarks($marksheetId);
            if (self::hasMultipleExamComponents($marksheetId)) {
                self::consolidateMultipleExamMarks($marksheetId);
            } else {
                self::consolidateSingleExamMarks($marksheetId);
            }
        }
    }

    /**
     * If the registration number is given, we get the highest number of courses registered for by the student in the
     * given timetable. Else we look for the highest number of courses among all the students in the timetable.
     * @param StudentConsolidatedMarksFilter $filter
     * @param string|null $regNumber
     * @return false|string|DataReader|null
     * @throws \yii\db\Exception
     */
    public static function getMaximumCoursesRegisteredFor(StudentConsolidatedMarksFilter $filter, string $regNumber = null)
    {
        $bindParams = [
            ':academicYear' => $filter->academicYear,
            ':studyProgram' => $filter->degreeCode,
            ':studyLevel' => $filter->levelOfStudy,
            ':studyGroup' => $filter->group
        ];

        $whereCondition = "WHERE 
            SEM.ACADEMIC_YEAR = :academicYear AND
            SEM.DEGREE_CODE = :studyProgram AND
            SEM.LEVEL_OF_STUDY = :studyLevel AND
            SEM.GROUP_CODE = :studyGroup";

        if ($regNumber) {
            $bindParams[':regNumber'] = $regNumber;

            $whereCondition = "WHERE
                MS.REGISTRATION_NUMBER = :regNumber AND
                SEM.ACADEMIC_YEAR = :academicYear AND
                SEM.DEGREE_CODE = :studyProgram AND
                SEM.LEVEL_OF_STUDY = :studyLevel AND
                SEM.GROUP_CODE = :studyGroup";
        }

        $studentCoursesQuery = "SELECT MAX(TOTAL_COURSES) FROM (
            SELECT MS.REGISTRATION_NUMBER, COUNT(MS.MRKSHEET_ID) TOTAL_COURSES
            FROM MUTHONI.MARKSHEETS MS
            INNER JOIN MUTHONI.MARKSHEET_DEF MD ON MS.MRKSHEET_ID = MD.MRKSHEET_ID
            INNER JOIN MUTHONI.SEMESTERS SEM ON MD.SEMESTER_ID = SEM.SEMESTER_ID "
            . $whereCondition .
            " GROUP BY MS.REGISTRATION_NUMBER ORDER BY MS.REGISTRATION_NUMBER ASC)";

        $connection = Yii::$app->getDb();
        return $connection->createCommand($studentCoursesQuery)->bindValues($bindParams)->queryScalar();
    }

    /**
     * @param string $marksheetId
     * @return bool
     * @throws Exception
     */
    public static function isAProjectCourse(string $marksheetId): bool
    {
        return false;

        $marksheet = MarksheetDef::find()->select(['COURSE_ID'])->where(['MRKSHEET_ID' => $marksheetId])->asArray()->one();
        if (empty($marksheet)) {
            throw new Exception('Marksheet ' . $marksheetId . ' not found.');
        }

        $course = Course::find()->select(['PROJECT_COURSE'])->where(['COURSE_ID' => $marksheet['COURSE_ID']])->asArray()
            ->one();
        if (empty($course)) {
            throw new Exception('Course ' . $marksheet['COURSE_ID'] . ' not found.');
        }

        if ((int)$course['PROJECT_COURSE'] === 1) {
            return true;
        }

        return false;
    }

    /**
     * @param array $modelErrors
     * @return string
     */
    public static function getModelErrors(array $modelErrors): string
    {
        $errorMsg = '';
        foreach ($modelErrors as $attributeErrors) {
            for ($i = 0; $i < count($attributeErrors); $i++) {
                $errorMsg .= ' ' . $attributeErrors[$i];
            }
        }
        return $errorMsg;
    }

    /**
     * @param string $marksheetId
     * @return void
     * @throws \yii\db\Exception
     * @throws Exception
     */
    public static function consolidateAndPublishMarks(string $marksheetId)
    {
        if (self::marksheetExists($marksheetId)) {
            self::consolidateCwMarks($marksheetId);

            if (self::hasMultipleExamComponents($marksheetId)) {
                self::consolidateMultipleExamMarks($marksheetId);
            } else {
                self::consolidateSingleExamMarks($marksheetId);
            }

            self::publishMarks($marksheetId);
        }
    }

    /**
     * @throws \yii\db\Exception
     * @throws Exception
     */
    public static function publishMarks(string $marksheetId)
    {
        $connection = Yii::$app->getDb();

        $studentsToPublishSql = "
            SELECT
                MUTHONI.LEC_MARKSHEETS.MRKSHEET_ID,
                MUTHONI.LEC_MARKSHEETS.REGISTRATION_NUMBER,
                MUTHONI.LEC_CW_ASSESSMENT.ASSESSMENT_ID,
                MUTHONI.LEC_ASSESSMENT_TYPES.ASSESSMENT_NAME
            FROM
                MUTHONI.LEC_MARKSHEETS
                INNER JOIN MUTHONI.LEC_CW_ASSESSMENT 
                    ON MUTHONI.LEC_MARKSHEETS.MRKSHEET_ID = MUTHONI.LEC_CW_ASSESSMENT.MARKSHEET_ID
                INNER JOIN MUTHONI.LEC_ASSESSMENT_TYPES 
                    ON MUTHONI.LEC_CW_ASSESSMENT.ASSESSMENT_TYPE_ID = MUTHONI.LEC_ASSESSMENT_TYPES.ASSESSMENT_TYPE_ID
                INNER JOIN MUTHONI.LEC_STUDENT_COURSE_WORK 
                    ON MUTHONI.LEC_CW_ASSESSMENT.ASSESSMENT_ID = MUTHONI.LEC_STUDENT_COURSE_WORK.ASSESSMENT_ID
                    AND MUTHONI.LEC_STUDENT_COURSE_WORK.REGISTRATION_NUMBER = MUTHONI.LEC_MARKSHEETS.REGISTRATION_NUMBER
            WHERE 
                MUTHONI.LEC_MARKSHEETS.GRADE IS NOT NULL
                AND MUTHONI.LEC_MARKSHEETS.PUBLISH_STATUS = :notPublished
                AND MUTHONI.LEC_STUDENT_COURSE_WORK.MARK_TYPE = :markType
                AND MUTHONI.LEC_STUDENT_COURSE_WORK.LECTURER_APPROVAL_STATUS = :approvalStatus
                AND MUTHONI.LEC_STUDENT_COURSE_WORK.HOD_APPROVAL_STATUS = :approvalStatus
                AND MUTHONI.LEC_STUDENT_COURSE_WORK.DEAN_APPROVAL_STATUS = :approvalStatus
                AND MUTHONI.LEC_ASSESSMENT_TYPES.ASSESSMENT_NAME LIKE :assessmentName
                AND MUTHONI.LEC_MARKSHEETS.MRKSHEET_ID = :marksheetId
            ORDER BY
                MUTHONI.LEC_MARKSHEETS.MRKSHEET_ID ASC
        ";

        $studentsToPublishParams = [
            ':notPublished' => self::MARKS_NOT_PUBLISHED,
            ':markType' => 'EXAM',
            ':approvalStatus' => 'APPROVED',
            ':assessmentName' => 'EXAM%',
            ':marksheetId' => $marksheetId
        ];

        $studentsToPublish = $connection->createCommand($studentsToPublishSql)->bindValues($studentsToPublishParams)->queryAll();

        foreach ($studentsToPublish as $studentToPublish) {
            $regNumber = $studentToPublish['REGISTRATION_NUMBER'];

//            $latestBalance = StudentBalance::find()
//                ->from('MUTHONI.STUDENT_BALANCES')
//                ->where(['REGISTRATION_NUMBER' => $regNumber])
//                ->andWhere(new Expression('ACADEMIC_YEAR = (
//                    SELECT MAX(sb2.ACADEMIC_YEAR) FROM MUTHONI.STUDENT_BALANCES sb2
//                    WHERE sb2.REGISTRATION_NUMBER = MUTHONI.STUDENT_BALANCES.REGISTRATION_NUMBER
//                )'))->one();

            $latestBalance = StudentBalanceAll::find()->where(['REGISTRATION_NUMBER' => $regNumber])->one();

            // Skip student if balance is positive (i.e., owes money)
            if ($latestBalance && $latestBalance->BALANCE > 0) {
                continue;
            }

            $tempMarksheet = TempMarksheet::find()->where(['MRKSHEET_ID' => $marksheetId, 'REGISTRATION_NUMBER' => $regNumber])->one();

            $studentIsInMarksheet = Marksheet::find()->where(['MRKSHEET_ID' => $marksheetId, 'REGISTRATION_NUMBER' => $regNumber])->count();

            /**
             * If a student has marks but no longer in the marksheet, update their marks complete to 1 and
             * publish status to 1 but not push the marks to smis
             */
            if (intval($studentIsInMarksheet) === 0) {
                $tempMarksheet->MARKS_COMPLETE = self::MARKS_COMPLETE;
                $tempMarksheet->PUBLISH_STATUS = self::MARKS_PUBLISHED;
                if (!$tempMarksheet->save()) {
                    if (!empty($tempMarksheet->getErrors())) {
                        $message = 'Missing ' . $regNumber . ' in ' . $marksheetId . ' failed to update publish and marks complete. '
                            . json_encode($tempMarksheet->getErrors());
                        throw new Exception($message);
                    } else {
                        throw new Exception('Missing student failed to update publish and marks complete status.');
                    }
                }
                continue;
            }

            $marksheetStudent = Marksheet::find()->where(['MRKSHEET_ID' => $marksheetId, 'REGISTRATION_NUMBER' => $regNumber])->one();
            $marksheetStudent->COURSE_MARKS = $tempMarksheet->COURSE_MARKS;
            $marksheetStudent->EXAM_MARKS = $tempMarksheet->EXAM_MARKS;
            $marksheetStudent->FINAL_MARKS = $tempMarksheet->FINAL_MARKS;
            $marksheetStudent->GRADE = $tempMarksheet->GRADE;
            if (is_null($marksheetStudent->ENTRY_DATE)) {
                $marksheetStudent->ENTRY_DATE = new Expression('CURRENT_DATE');
            }
            $marksheetStudent->LAST_UPDATE = new Expression('CURRENT_DATE');

            if ($marksheetStudent->save()) {

                $tempMarksheet->PUBLISH_STATUS = self::MARKS_PUBLISHED;

                if (!$tempMarksheet->save()) {
                    if (!empty($tempMarksheet->getErrors())) {
                        $message = $marksheetId . ' failed to update publish status. Errors: ' . json_encode($marksheetStudent->getErrors());
                        throw new Exception($message);
                    } else {
                        throw new Exception('Marks failed to publish');
                    }
                }

                SmisHelper::pushToStudentCourses($marksheetStudent);

                SmisHelper::pushToSmisPortalMarksheets($marksheetStudent);

            } else {
                if (!empty($marksheetStudent->getErrors())) {
                    $message = $marksheetId . ' not published in smis. Errors: ' . json_encode($marksheetStudent->getErrors());
                    throw new Exception($message);
                } else {
                    throw new Exception('Marks failed to publish');
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public static function pushToStudentCourses($marksheet)
    {
        /**
         * Example formats:
         *
         * 2021/2022_B66_3_1_20_BQS301_0083 - marksheet id
         * B66/137739/2019-2021/2022-BQS301_0083-FA course reg id
         * B66/137739/2019-2021/2022 progress code
         */
        $parsed = self::parseMarksheetId($marksheet->MRKSHEET_ID);
        $courseRegId = $marksheet->REGISTRATION_NUMBER . '-' . $parsed['year'] . '-' . $parsed['courseId'] . '-' . $marksheet->EXAM_TYPE;
        $progressCode = $marksheet->REGISTRATION_NUMBER . '-' . $parsed['year'];

        // Populate student courses details
        $studentCourse = StudentCourse::find()->where(['COURSE_REGISTRATION_ID' => $courseRegId])->one();
        if (!$studentCourse) {
            $studentCourse = new StudentCourse();
        }

        $studentCourse->COURSE_REGISTRATION_ID = $courseRegId;
        $studentCourse->PROGRESS_CODE = $progressCode;
        $studentCourse->COURSE_ID = $parsed['courseId'];
        $studentCourse->EXAMTYPE_CODE = $marksheet->EXAM_TYPE;
        $studentCourse->FINAL_MARK = $marksheet->FINAL_MARKS;
        $studentCourse->GRADE = $marksheet->GRADE;
        $studentCourse->PAY_PER_COURSE = '';
        $studentCourse->RESULT_STATUS = 'TRANSCRIPT';
        $studentCourse->LAST_UPDATE = new Expression('CURRENT_TIMESTAMP');
        $studentCourse->USERID = 'LEC_AUTO_USER';
        $studentCourse->GROUP_CODE = $parsed['groupCode'];
        $studentCourse->LOCK_STATUS = 'FALSE';
        $studentCourse->LEVEL_OF_STUDY = $parsed['levelOfStudy'];
        $studentCourse->FINAL = 1;
        $studentCourse->MRKSHEET_ID = $marksheet->MRKSHEET_ID;
        $studentCourse->COURSE_MARK = $marksheet->COURSE_MARKS;
        $studentCourse->EXAM_MARK = $marksheet->EXAM_MARKS;
        $studentCourse->REMARKS = $marksheet->REMARKS ?? '';
        $studentCourse->PUBLISH_STATUS = 1;

        if (!$studentCourse->save()) {
            if (!empty($studentCourse->getErrors())) {
                $message = $marksheet->MRKSHEET_ID . ' failed to publish to student courses. Errors: ' . json_encode($studentCourse->getErrors());
                throw new Exception($message);
            } else {
                throw new Exception($marksheet->MRKSHEET_ID . ' Marks failed to publish to student courses.');
            }
        }
    }

    private static function parseMarksheetId(string $marksheetId): array
    {
        $parts = explode('_', $marksheetId);

        return [
            'year' => $parts[0],                           // 2021/2022
            'levelOfStudy' => $parts[2],                   // 3
            'groupCode' => $parts[4],                      // 20
            'courseId' => $parts[5] . '_' . $parts[6],     // BQS301_0083
        ];
    }

    /**
     * @throws Exception
     */
    public static function pushToSmisPortalMarksheets($marksheet): array
    {
        $publishMarksApiUrl = Yii::$app->params['publishMarksApiUrl'];

        // Prepare data for API call
        $postData = array(
            'marksheet_id' => $marksheet->MRKSHEET_ID,
            'registration_number' => $marksheet->REGISTRATION_NUMBER,
            'course_marks' => $marksheet->COURSE_MARKS,
            'grade' => trim($marksheet->GRADE),
            'remarks' => $marksheet->REMARKS ?? '',
            'user_ip' => Yii::$app->has('request') && Yii::$app->request instanceof yii\web\Request ? Yii::$app->request->userIP : 'console'
        );

        // Make API call
        $response = self::makeApiCall($publishMarksApiUrl, $postData);

        if (!$response['success']) {
            $message = $marksheet->MRKSHEET_ID . ' Marks failed to publish to SMIS Portal. Error: ' . $response['message'];
            throw new Exception($message);
        }

        return $response;
    }

    /**
     * Make HTTP POST request to API endpoint
     * @param string $url The API endpoint URL
     * @param array $data The data to send
     * @return array The decoded JSON response
     * @throws Exception if HTTP request fails
     */
    private static function makeApiCall(string $url, array $data): array
    {
        $jsonData = json_encode($data);

        // Initialize cURL
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ),
            CURLOPT_SSL_VERIFYPEER => false, // Only for development
            CURLOPT_SSL_VERIFYHOST => false  // Only for development
        ));

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);

        curl_close($curl);

        if ($curlError) {
            throw new Exception('cURL Error: ' . $curlError);
        }

        if ($httpCode !== 200) {
            throw new Exception('HTTP Error: ' . $httpCode . ' - ' . $response);
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from API: ' . $response);
        }

        return $decodedResponse;
    }

//    public static function pushToSmisPortalMarksheets_($marksheet)
//    {
//        $student = SPMarksheet::find()->where(['marksheet_id' => $marksheet->MRKSHEET_ID,
//            'registration_number' => $marksheet->REGISTRATION_NUMBER])->one();
//
//        if ($student) {
//            $student->course_work = $marksheet->COURSE_MARKS;
//            $student->final_grade = trim($marksheet->GRADE);
//            $student->course_remarks = $marksheet->REMARKS;
//            $student->terminal_ip = Yii::$app->has('request') && Yii::$app->request instanceof yii\web\Request
//                ? Yii::$app->request->userIP
//                : 'console';
//            $student->last_update = $marksheet->LAST_UPDATE;
//            $student->publish_status = 1;
//            $student->publish_date = new Expression('CURRENT_DATE');
//
//            if (!$student->save()) {
//                if (!empty($student->getErrors())) {
//                    $message = $marksheet->MRKSHEET_ID . ' Marks failed to publish to SMIS Portal. Errors: ' . json_encode($student->getErrors());
//                    throw new Exception($message);
//                } else {
//                    throw new Exception('Marks failed to publish to SMIS Portal');
//                }
//            }
//        }
//    }

}