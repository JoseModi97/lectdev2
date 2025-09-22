<?php
/**
 * Generate reports for the HOD within the main application context.
 */

namespace app\controllers;

use app\components\SmisHelper;
use app\models\Department;
use app\models\Faculty;
use app\models\MarksheetDef;
use app\models\search\DepartmentCoursesSearch;
use app\models\search\SubmittedMarksSearch;
use Exception;
use Yii;
use yii\db\Exception as dbException;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

class HodReportsController extends BaseController
{
    /**
     * Configure controller behaviours.
     *
     * @return array
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
     * Display courses in the HOD's department.
     *
     * @throws ServerErrorHttpException
     */
    public function actionDepartmentCourses(): string
    {
        try {
            $request = Yii::$app->request;
            $level = $request->get('level', 'hod');
            $deptCode = $request->get('deptCode', $this->deptCode);
            $type = $request->get('type', 'reports');

            if ($level !== 'hod') {
                throw new Exception('You must give the correct approval level.');
            }

            if ($deptCode !== $this->deptCode) {
                throw new Exception('The HOD is not allowed to view courses in another department.');
            }

            $department = Department::find()
                ->select(['DEPT_NAME'])
                ->where(['DEPT_CODE' => $deptCode])
                ->one();
            if ($department === null) {
                throw new Exception('The department could not be found.');
            }

            $faculty = Faculty::find()
                ->select(['FACULTY_NAME'])
                ->where(['FAC_CODE' => $this->facCode])
                ->one();

            $academicYear = $this->getCurrentAcademicYear();

            $searchModel = new DepartmentCoursesSearch();
            $departmentCoursesProvider = $searchModel->search(Yii::$app->request->queryParams, [
                'deptCode' => $deptCode,
                'academicYear' => $academicYear,
            ]);

            return $this->render('index', [
                'title' => 'Courses in the department of ' . strtolower($department->DEPT_NAME),
                'searchModel' => $searchModel,
                'departmentCoursesProvider' => $departmentCoursesProvider,
                'deptCode' => $deptCode,
                'deptName' => $department->DEPT_NAME,
                'academicYear' => $academicYear,
                'level' => $level,
                'type' => $type,
                'facName' => $faculty?->FACULTY_NAME,
            ]);
        } catch (Exception | dbException $ex) {
            if (YII_ENV_PROD) {
                $message = $ex instanceof dbException ? 'This request failed to process.' : $ex->getMessage();
            } else {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * List the submitted marks for a marksheet.
     *
     * @throws ServerErrorHttpException
     */
    public function actionSubmittedMarks(): string
    {
        try {
            $request = Yii::$app->request;
            $marksheetId = $request->get('marksheetId');
            $level = $request->get('level');
            $deptCode = $request->get('deptCode');
            $type = $request->get('type');

            $mkModel = MarksheetDef::findOne($marksheetId);
            if ($mkModel === null) {
                throw new Exception('The requested marksheet was not found.');
            }

            $courseCode = $mkModel->course->COURSE_CODE;
            $courseName = $mkModel->course->COURSE_NAME;

            $searchModel = new SubmittedMarksSearch();
            $submittedMarkskProvider = $searchModel->search(Yii::$app->request->queryParams, [
                'deptCode' => $this->deptCode,
                'facCode' => $this->facCode,
                'marksheetId' => $marksheetId,
                'level' => 'hod',
            ]);

            $department = Department::find()->select(['DEPT_NAME'])
                ->where(['DEPT_CODE' => $deptCode])->one();
            $deptName = $department?->DEPT_NAME;

            $faculty = Faculty::find()->select(['FACULTY_NAME'])->where(['FAC_CODE' => $this->facCode])
                ->one();

            return $this->render('submittedMarks', [
                'title' => 'Submitted marks for marksheet ' . $marksheetId,
                'searchModel' => $searchModel,
                'submittedMarkskProvider' => $submittedMarkskProvider,
                'marksheetId' => $marksheetId,
                'courseCode' => $courseCode,
                'courseName' => $courseName,
                'facName' => $faculty?->FACULTY_NAME,
                'academicYear' => $this->getCurrentAcademicYear(),
                'level' => $level,
                'deptCode' => $deptCode,
                'deptName' => $deptName,
                'type' => $type,
            ]);
        } catch (Exception | dbException $ex) {
            if (YII_ENV_PROD) {
                $message = $ex instanceof dbException ? 'This request failed to process.' : $ex->getMessage();
            } else {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }
}
