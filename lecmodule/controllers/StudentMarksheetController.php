<?php

/**
 * @author Jack
 * @email jackmutiso37@gmail.com
 */

namespace app\controllers;

use Yii;
use yii\base\Model;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use app\models\Marksheet;
use app\models\UonStudent;
use app\models\LecLateMark;
use app\models\StudentCourses;
use yii\filters\AccessControl;
use app\models\GradingSystemDetails;
use app\models\LecLateMarkComment;
use app\models\portal\SmisMarksheet;
use app\models\search\LateMarksStudentMarksheetSearch;
use app\components\SmisHelper;

class StudentMarksheetController extends BaseController
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
                    ]
                ]
            ]
        ];
    }
    public function actionIndex()
    {
        $model = new UonStudent();
        $student = null;
        $marksheets = [];
        if ($model->load(Yii::$app->request->post())) {
            if (empty($model->REGISTRATION_NUMBER)) {
                Yii::$app->session->setFlash('error', 'Registration number is required.');
                return $this->redirect(['index']);
            }
            $registrationNumber = $model->REGISTRATION_NUMBER;
            $student = UonStudent::findOne(['REGISTRATION_NUMBER' => $registrationNumber]);

            if ($student) {

                $marksheets = Marksheet::find()
                    ->alias('m')
                    ->joinWith([
                        'examType et',
                        'marksheetDef.course c',
                        'marksheetDef.semester s'
                    ])
                    ->where(['REGISTRATION_NUMBER' => $registrationNumber])
                    ->select([
                        'm.MRKSHEET_ID',
                        'm.REGISTRATION_NUMBER',
                        'm.COURSE_MARKS',
                        'm.EXAM_MARKS',
                        'm.FINAL_MARKS',
                        'm.GRADE',
                        'm.POST_STATUS',
                        'm.EXAM_TYPE',
                        'et.EXAMTYPE_CODE',
                        'c.COURSE_CODE',
                        'c.COURSE_NAME',
                        'c.CREDIT_FACTOR',
                        's.SEMESTER_CODE',
                        's.ACADEMIC_YEAR',
                    ])
                    ->limit(200)
                    ->cache(3600)
                    ->all();
            } else {
                Yii::$app->session->setFlash('error', 'Student not found.');
            }
        }

        return $this->render('index', [
            'model' => $model,
            'student' => $student,
            'marksheets' => $marksheets,
        ]);
    }

    /**
     * Updates or creates a LecLateMark record based on the unique combination of MRKSHEET_ID and REGISTRATION_NUMBER.
     *
     * @param string $editableKey       The MRKSHEET_ID from the Editable widget.
     * @param string $editableAttribute The attribute being updated (e.g., COURSE_MARKS).
     * @param mixed  $value             The new value for the attribute.
     * @param string $regNumber         The registration number that must be passed in the POST.
     *
     * @return array JSON response containing output, message, and successMessage.
     */
    protected function updateLecLateMark($editableKey, $editableAttribute, $value, $regNumber)
    {
        $date = date('Y-m-d H:i:s') . '.000';
        /**
         * Validate input parameters
         */
        if (!$editableKey || !$editableAttribute || !$regNumber) {
            return ['output' => '', 'message' => 'Missing attribute name, key, or registration number'];
        }

        $marksheet_id = $editableKey;
        $condition = [
            'MRKSHEET_ID' => $editableKey,
            'REGISTRATION_NUMBER' => $regNumber
        ];

        /**
         * Fetch original Marksheet record
         */
        $originalRecord = Marksheet::find()->where($condition)->one();
        if (!$originalRecord) {
            return ['output' => '', 'message' => 'Original marksheet record not found'];
        }
        $lateMarkCondition = [
            'MRKSHEET_ID' => $editableKey,
            'REGISTRATION_NUMBER' => $regNumber,
            // 'RECORD_VALIDITY' => 'VALID',
            'RECORD_STATUS' => 1
        ];

        /**
         * Fetch existing LecLateMark record
         */
        $lateMarkModel = LecLateMark::findOne($lateMarkCondition);

        if (isset($lateMarkModel) &&  $lateMarkModel->LECTURER_APPROVAL == 1) {
            return [
                'output' => '',
                'message' => '<span class="text-danger">This record has been submitted for approval and cannot be edited.</span>'
            ];
        }
        /**
         * Handle REMARKS updates
         */
        if ($editableAttribute === 'REMARKS') {
            if ($lateMarkModel) {
                /**
                 * Update remarks in existing record (even if invalid)
                 */
                $lateMarkModel->REMARKS = $value;
                $lateMarkModel->LAST_UPDATE =  new \yii\db\Expression("TO_TIMESTAMP(:LAST_UPDATE, 'YYYY-MM-DD HH24:MI:SS.FF3')", [
                    ':LAST_UPDATE' => $date,
                ]);
            } else {
                return [
                    'output' => '',
                    'message' => '<span class="text-danger">Please update or enter course or exam marks to be able to enter the remarks.</span>',
                ];

                // Create new record only for remarks
                // $lateMarkModel = new LecLateMark();
                // $lateMarkModel->MRKSHEET_ID = $editableKey;
                // $lateMarkModel->REGISTRATION_NUMBER = $regNumber;
                // $lateMarkModel->RECORD_STATUS = 1;
                // $lateMarkModel->RECORD_VALIDITY = 'VALID';
                // $lateMarkModel->LEC_LATE_MARKS_ID = Yii::$app->db->createCommand("SELECT MUTHONI.LEC_LATE_MARKS_SEQ.NEXTVAL FROM dual")->queryScalar();
                // $lateMarkModel->REMARKS = $value;
                // $lateMarkModel->ENTRY_DATE = new \yii\db\Expression("TO_TIMESTAMP(:ENTRY_DATE, 'YYYY-MM-DD HH24:MI:SS.FF3')", [
                //     ':ENTRY_DATE' => $date,
                // ]);
            }

            try {
                if ($lateMarkModel->save()) {
                    $message = "Remarks updated successfully.";
                    if ($value == null) {
                        $value = "(not set)";
                        $message = "Reverted to empty value.";
                    }
                    return [

                        'output' => $value,
                        'message' => '',
                        'successMessage' => $message
                    ];
                }
            } catch (\Exception $e) {
                return ['output' => '', 'message' => "Error saving remarks: " . $e->getMessage()];
            }
        }

        /**
         * Handle COURSE/EXAM reverts
         */
        if ($originalRecord->$editableAttribute == $value) {
            if ($lateMarkModel) {
                /**
                 *  Remove the reverted field
                 */
                $lateMarkModel->$editableAttribute = null;
                $lateMarkModel->LAST_UPDATE = new Expression("TO_TIMESTAMP(:LAST_UPDATE, 'YYYY-MM-DD HH24:MI:SS.FF3')", [
                    ':LAST_UPDATE' => $date,
                ]);

                /**
                 * Recalculate FINAL_MARKS
                 */
                $course = $lateMarkModel->COURSE_MARKS ?? (float)$originalRecord->COURSE_MARKS;
                $exam = $lateMarkModel->EXAM_MARKS ?? (float)$originalRecord->EXAM_MARKS;
                $lateMarkModel->FINAL_MARKS = $course + $exam;

                //GRADE
                $lateMarkModel->GRADE = $this->getGrade($marksheet_id, $lateMarkModel->FINAL_MARKS);





                /**
                 * Check for other changes
                 */
                $hasChanges = $lateMarkModel->COURSE_MARKS !== null || $lateMarkModel->EXAM_MARKS !== null;

                if ($hasChanges) {
                    $lateMarkModel->RECORD_STATUS = 1;
                    $lateMarkModel->RECORD_VALIDITY = 'VALID';
                    $lateMarkModel->LAST_UPDATE = new \yii\db\Expression("TO_TIMESTAMP(:LAST_UPDATE, 'YYYY-MM-DD HH24:MI:SS.FF3')", [
                        ':LAST_UPDATE' => $date,
                    ]);

                    $lateMarkModel->save();
                } else {
                    /**
                     * Invalidate if no changes remain
                     */
                    $lateMarkModel->RECORD_STATUS = 0;
                    $lateMarkModel->RECORD_VALIDITY = 'INVALID';
                    $lateMarkModel->LAST_UPDATE = new \yii\db\Expression("TO_TIMESTAMP(:LAST_UPDATE, 'YYYY-MM-DD HH24:MI:SS.FF3')", [
                        ':LAST_UPDATE' => $date,
                    ]);

                    $lateMarkModel->save();
                }
            }

            return [
                'output' => '<span class="text-success">' . $value . '</span>',
                'message' => '',
                'target' => '#' . $editableKey . "_" . $regNumber,
                'successMessage' => "Reverted to original value."
            ];
        }

        /**
         * Create/update LecLateMark for non-REMARKS fields
         */
        $isNewRecord = false;
        if (!$lateMarkModel) {
            $lateMarkModel = new LecLateMark();
            $lateMarkModel->MRKSHEET_ID = $editableKey;
            $lateMarkModel->REGISTRATION_NUMBER = $regNumber;
            $lateMarkModel->RECORD_STATUS = 1;
            $lateMarkModel->RECORD_VALIDITY = 'VALID';
            $lateMarkModel->ENTRY_DATE = new \yii\db\Expression("TO_TIMESTAMP(:ENTRY_DATE, 'YYYY-MM-DD HH24:MI:SS.FF3')", [
                ':ENTRY_DATE' => $date,
            ]);
            $lateMarkModel->LEC_LATE_MARKS_ID = Yii::$app->db->createCommand("SELECT MUTHONI.LEC_LATE_MARKS_SEQ.NEXTVAL FROM dual")->queryScalar();
            $isNewRecord = true;
        } elseif ($lateMarkModel->RECORD_STATUS == 0) {
            /**
             * Reactivate invalid record for new changes
             */
            $lateMarkModel->RECORD_STATUS = 1;
            $lateMarkModel->RECORD_VALIDITY = 'VALID';
            $lateMarkModel->LAST_UPDATE = new \yii\db\Expression("TO_TIMESTAMP(:LAST_UPDATE, 'YYYY-MM-DD HH24:MI:SS.FF3')", [
                ':LAST_UPDATE' => $date,
            ]);
        }

        /**
         * Update the field
         */
        $lateMarkModel->$editableAttribute = $value;

        /**
         * Calculate FINAL_MARKS
         */
        $course = isset($lateMarkModel->COURSE_MARKS) ? (float)$lateMarkModel->COURSE_MARKS : (float)$originalRecord->COURSE_MARKS;
        $exam = isset($lateMarkModel->EXAM_MARKS) ? (float)$lateMarkModel->EXAM_MARKS : (float)$originalRecord->EXAM_MARKS;
        $lateMarkModel->FINAL_MARKS = $course + $exam;


        //GRADE
        $lateMarkModel->GRADE = $this->getGrade($marksheet_id, $lateMarkModel->FINAL_MARKS);

        // return [
        //     'output' => '',
        //     'message' =>" GRADE - ". $lateMarkModel->GRADE
        // ];


        try {
            if ($lateMarkModel->save()) {
                return [
                    'output' => '<span class="text-danger">' . $value . '</span>',
                    'finalMarks' => $lateMarkModel->FINAL_MARKS,
                    'message' => '',
                    'target' => '#' . $editableKey . "_" . $regNumber,
                    'successMessage' => $isNewRecord ? "New record created." : "Record updated."
                ];
            } else {
                /**
                 * Get validation errors
                 */
                $errors = $lateMarkModel->getErrors();
                return [
                    'output' => '',
                    'message' => 'Validation failed: ' . json_encode($errors)
                ];
            }
        } catch (\Exception $e) {
            return [
                'output' => '',
                'message' => "Database error: " . $e->getMessage()
            ];
        }
        return [
            'output' => '',
            'message' => 'Unexpected error occurred'
        ];
    }
    /**
     * Calculates grade based on course code and mark
     * 
     * @param string $courseCode The course code (e.g., "CEC3102")
     * @param float $mark The student's mark
     * @return string The determined grade
     */
    private function getGrade($marksheet_id, $mark)
    {
        $parts = explode('_', $marksheet_id);
        $courseCode = $parts[count($parts) - 2];

        $grade = GradingSystemDetails::find()
            ->select(['GRADINGSYSDETAILS.GRADE'])
            ->innerJoin('MUTHONI.DEGREE_PROGRAMMES', 'DEGREE_PROGRAMMES.GRADINGSYSTEM = GRADINGSYSDETAILS.GRADINGID')
            ->innerJoin('MUTHONI.DEGREE_COURSES', 'DEGREE_COURSES.DEGREE_CODE = DEGREE_PROGRAMMES.DEGREE_CODE')
            ->innerJoin('MUTHONI.COURSES', 'COURSES.COURSE_ID = DEGREE_COURSES.COURSE_ID')
            ->where(['COURSES.COURSE_CODE' => $courseCode])
            ->andWhere(['<=', 'GRADINGSYSDETAILS.LOWERBOUND', $mark])
            ->andWhere(['>=', 'GRADINGSYSDETAILS.UPPERBOUND', $mark])
            ->scalar();

        return $grade ?: 'UNGRADED';
    }
    public function actionUpdateCourseMark()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $regNumber = Yii::$app->request->post('registrationNumber');

        $editableKey = Yii::$app->request->post('editableKey');
        $editableAttribute = Yii::$app->request->post('editableAttribute');
        $editableIndex = Yii::$app->request->post('editableIndex');
        $posted = Yii::$app->request->post('Marksheet', []);
        $value = isset($posted[$editableIndex][$editableAttribute])
            ? $posted[$editableIndex][$editableAttribute]
            : null;

        return $this->updateLecLateMark($editableKey, $editableAttribute, $value, $regNumber);
    }
    public function actionUpdateExamMarks()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $regNumber = Yii::$app->request->post('registrationNumber');

        $editableKey = Yii::$app->request->post('editableKey');
        $editableAttribute = Yii::$app->request->post('editableAttribute');
        $editableIndex = Yii::$app->request->post('editableIndex');
        $posted = Yii::$app->request->post('Marksheet', []);
        $value = isset($posted[$editableIndex][$editableAttribute])
            ? $posted[$editableIndex][$editableAttribute]
            : null;

        return $this->updateLecLateMark($editableKey, $editableAttribute, $value, $regNumber);
    }
    public function actionUpdateRemarks()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $regNumber = Yii::$app->request->post('registrationNumber');

        $editableKey = Yii::$app->request->post('editableKey');
        $editableAttribute = Yii::$app->request->post('editableAttribute');
        $editableIndex = Yii::$app->request->post('editableIndex');
        $posted = Yii::$app->request->post('Marksheet', []);
        $value = isset($posted[$editableIndex][$editableAttribute])
            ? $posted[$editableIndex][$editableAttribute]
            : null;

        return $this->updateLecLateMark($editableKey, $editableAttribute, $value, $regNumber);
    }
    public function actionSubmitMarksheet()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $registrationNumber = Yii::$app->request->post('registrationNumber');
        $date = date('Y-m-d H:i:s') . '.000';
        if (empty($registrationNumber)) {
            return ['success' => false, 'message' => 'Registration number missing.'];
        }

        /**
         * Update all LecLateMark records with this registration number that are in 'EDITING' status.
         */
        $updated = LecLateMark::updateAll(
            [
                //'RECORD_STATUS' => 0,
                'LECTURER_APPROVAL' => 1,
                'TIME_STAMP' => new Expression("TO_TIMESTAMP(:TIME_STAMP, 'YYYY-MM-DD HH24:MI:SS.FF3')", [
                    ':TIME_STAMP' => $date,
                ]),
            ],

            ['and', ['REGISTRATION_NUMBER' => $registrationNumber], ['RECORD_VALIDITY' => 'VALID']]
        );

        if ($updated) {
            return ['success' => true, 'message' => 'Marksheet submitted successfully.', 'registrationNumber' => $registrationNumber];
        }

        return ['success' => false, 'message' => 'No marksheet records in editing status found.' . $updated];
    }
    public function actionViewHistory($mrksheet_id, $reg_number)
    {

        $history = LecLateMark::find()
            ->where(['MRKSHEET_ID' => $mrksheet_id, 'REGISTRATION_NUMBER' => $reg_number])
            ->orderBy([
                //'LEC_LATE_MARKS_ID' => SORT_ASC,
                'LEC_LATE_MARKS.LECTURER_APPROVAL' => SORT_ASC,
                'LEC_LATE_MARKS.HOD_APPROVAL' => SORT_ASC,
                'LEC_LATE_MARKS.DEAN_APPROVAL' => SORT_ASC,
            ])
            ->all();

        return $this->renderAjax('_history', ['history' => $history]);
    }



    /**
     * HOD
     */
    public function actionHodApproval()
    {

        $searchModel = new LateMarksStudentMarksheetSearch();
        $dataProvider = $searchModel->searchPendingMissingMarksHod($this->request->queryParams);

        return $this->render('hod-approval', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }
    public function actionApprove()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $selected = Yii::$app->request->post('selected', []);

        if (empty($selected)) {
            return ['status' => 'error', 'message' => 'No records selected'];
        }

        foreach ($selected as $id) {
            $record = LecLateMark::findOne($id);
            if ($record) {
                $record->HOD_APPROVAL = 1; // Approved
                $record->save();
            }
        }

        return ['status' => 'success', 'message' => 'Records approved successfully'];
    }
    public function actionDisapprove()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // if (Yii::$app->user->isGuest) {
        //     return ['status' => 'error', 'message' => 'User is not logged in. Please log in and try again.'];
        // }

        $selected = Yii::$app->request->post('selected', []);
        $comment = Yii::$app->request->post('comment', '');
        $date = date('Y-m-d H:i:s') . '.000';

        if (empty($selected)) {
            return ['status' => 'error', 'message' => 'No records selected'];
        }

        if (empty($comment)) {
            return ['status' => 'error', 'message' => 'Comment is required'];
        }

        foreach ($selected as $id) {
            $record = LecLateMark::findOne($id);
            if ($record) {
                $record->HOD_APPROVAL = 2;
                $record->RECORD_STATUS = 0;
                $record->RECORD_VALIDITY = "REJECTED";

                $commentModel = new LecLateMarkComment();
                $commentModel->LATE_MARK_ID = $id;
                $commentModel->APPROVER_LEVEL = 'HOD';
                $commentModel->USERNAME = (string) $this->payrollNo;
                $commentModel->COMMENT_DATE = new Expression(
                    "TO_TIMESTAMP(:COMMENT_DATE, 'YYYY-MM-DD HH24:MI:SS.FF3')",
                    [':COMMENT_DATE' => $date]
                );
                $commentModel->APPROVER_COMMENT = $comment;

                if (!$commentModel->save()) {
                    return ['status' => 'error', 'message' => 'Failed to save comment', 'errors' => $commentModel->errors];
                }

                if (!$record->save()) {
                    return ['status' => 'error', 'message' => 'Failed to update record', 'errors' => $record->errors];
                }
            }
        }

        return ['status' => 'success', 'message' => 'Records disapproved successfully'];
    }
    public function actionApprovedMarks()
    {
        $searchModel = new LateMarksStudentMarksheetSearch();

        $queryParams = $this->request->queryParams;
        $level = trim(strtolower($queryParams['level'] ?? null));
        if ($level == 'hod') {
            $dataProvider = $searchModel->searchApprovedMissingMarksHod($this->request->queryParams);
        } elseif ($level == 'dean') {
            $dataProvider = $searchModel->searchApprovedMissingMarksDean($this->request->queryParams);
        } else {
            throw new \yii\web\BadRequestHttpException('Invalid or missing level parameter.');
        }

        return $this->render('approved-disapproved-marks', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'view' => 'approved-marks',
            'level' => $level
        ]);
    }
    public function actionDisapprovedMarks()
    {
        $searchModel = new LateMarksStudentMarksheetSearch();


        $queryParams = $this->request->queryParams;
        $level = trim(strtolower($queryParams['level'] ?? null));
        if ($level == 'hod') {
            $dataProvider = $searchModel->searchDisapprovedMissingMarksHod($this->request->queryParams);
        } elseif ($level == 'dean') {
            $dataProvider = $searchModel->searchDisapprovedMissingMarksDean($this->request->queryParams);
        } else {
            throw new \yii\web\BadRequestHttpException('Invalid or missing level parameter.');
        }
        return $this->render('approved-disapproved-marks', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'view' => 'disapproved-marks',
            'level' => $level
        ]);
    }

    /**
     * DEAN
     */
    public function actionDeanApproval()
    {

        $searchModel = new LateMarksStudentMarksheetSearch();
        $dataProvider = $searchModel->searchDeanRecord($this->request->queryParams);

        return $this->render('dean-approval', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }
    public function actionPublishedMarks()
    {

        $searchModel = new LateMarksStudentMarksheetSearch();
        $dataProvider = $searchModel->searchPublishedRecord($this->request->queryParams);

        return $this->render('published-marks', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionPublishMarks()
    {
        $searchModel = new LateMarksStudentMarksheetSearch();
        $dataProvider = $searchModel->searchPublishMarks($this->request->queryParams);

        return $this->render('publish-marks', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }
    public function actionPublish()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $selected = Yii::$app->request->post('selected', []);

            if (empty($selected)) {
                return ['status' => 'error', 'message' => 'No records selected'];
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($selected as $id) {
                    $record = LecLateMark::findOne($id);
                    if (!$record) continue;

                    $marksheetStudent = Marksheet::find()->where([
                        'MRKSHEET_ID' => $record->MRKSHEET_ID,
                        'REGISTRATION_NUMBER' => $record->REGISTRATION_NUMBER
                    ])->one();

                    SmisHelper::pushToSmisPortalMarksheets($marksheetStudent);

                    // Only update status if portal push was successful
                    $record->PUBLISH_STATUS = 1;
                    if (!$record->save(false)) {
                        throw new \Exception("Failed to update Oracle record");
                    }
                }

                $transaction->commit();
                return ['status' => 'success', 'message' => 'Record(s) published successfully'];
            } catch (\Exception $e) {
                $transaction->rollBack();
                return ['status' => 'error', 'message' => 'Error publishing records'];
            }
        }
    }
    public function actionDeanApprove()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $selected = Yii::$app->request->post('selected', []);

        $date = date('Y-m-d H:i:s') . '.000';

        if (empty($selected)) {
            return ['status' => 'error', 'message' => 'No records selected'];
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($selected as $id) {
                $record = LecLateMark::findOne($id);
                if (!$record) continue;

                // Update late mark record
                $record->DEAN_APPROVAL = 1;
                $record->RECORD_STATUS = 0;

                if ($record->save()) {
                    // Update MARKSHEETS table
                    $marksheet = Marksheet::findOne([
                        'MRKSHEET_ID' => $record->MRKSHEET_ID,
                        'REGISTRATION_NUMBER' => $record->REGISTRATION_NUMBER
                    ]);

                    if ($marksheet) {
                        // Update available marks (CAT or Exam)
                        if (!empty($record->COURSE_MARKS)) {
                            $marksheet->COURSE_MARKS = $record->COURSE_MARKS;
                        }
                        if (!empty($record->EXAM_MARKS)) {
                            $marksheet->EXAM_MARKS = $record->EXAM_MARKS;
                        }

                        // Always update final marks and grade
                        $marksheet->FINAL_MARKS = $record->FINAL_MARKS;
                        $marksheet->GRADE = trim($record->GRADE);
                        $marksheet->LAST_UPDATE = new Expression(
                            "TO_TIMESTAMP(:LAST_UPDATE, 'YYYY-MM-DD HH24:MI:SS.FF3')",
                            [':LAST_UPDATE' => $date]
                        );
                        $marksheet->save(false);
                    }


                    // Update STUDENT_COURSES 

                    SmisHelper::pushToStudentCourses($marksheet);

                    // $studentCourse = StudentCourses::find()
                    //     ->where(['like', 'COURSE_REGISTRATION_ID', $record->REGISTRATION_NUMBER])
                    //     ->andWhere(['MRKSHEET_ID' => $record->MRKSHEET_ID])
                    //     ->one();

                    // if ($studentCourse) {
                    //     // Update available marks (CAT or Exam)
                    //     if (!empty($record->COURSE_MARKS)) {
                    //         $studentCourse->COURSE_MARK = $record->COURSE_MARKS;
                    //     }
                    //     if (!empty($record->EXAM_MARKS)) {
                    //         $studentCourse->EXAM_MARK = $record->EXAM_MARKS;
                    //     }

                    //     // Always update final marks and grade
                    //     $studentCourse->FINAL_MARK = $record->FINAL_MARKS;
                    //     $studentCourse->GRADE = trim($record->GRADE);
                    //     $studentCourse->LAST_UPDATE = new Expression(
                    //         "TO_TIMESTAMP(:LAST_UPDATE, 'YYYY-MM-DD HH24:MI:SS.FF3')",
                    //         [':LAST_UPDATE' => $date]
                    //     );
                    //     $studentCourse->save(false);
                    // }
                }
            }

            $transaction->commit();
            return ['status' => 'success', 'message' => 'Records approved and marks updated successfully'];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['status' => 'error', 'message' => 'Error updating records: ' . $e->getMessage()];
        }
    }
    public function actionDeanDisapprove()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;


        $selected = Yii::$app->request->post('selected', []);
        $comment = Yii::$app->request->post('comment', '');
        $date = date('Y-m-d H:i:s') . '.000';

        if (empty($selected)) {
            return ['status' => 'error', 'message' => 'No records selected'];
        }

        if (empty($comment)) {
            return ['status' => 'error', 'message' => 'Comment is required'];
        }

        foreach ($selected as $id) {
            $record = LecLateMark::findOne($id);
            if ($record) {
                $record->DEAN_APPROVAL = 2;
                $record->RECORD_STATUS = 0;
                $record->RECORD_VALIDITY = "REJECTED";

                $commentModel = new LecLateMarkComment();
                $commentModel->LATE_MARK_ID = $id;
                $commentModel->APPROVER_LEVEL = 'HOD';
                $commentModel->USERNAME = (string) $this->payrollNo;
                $commentModel->COMMENT_DATE = new Expression(
                    "TO_TIMESTAMP(:COMMENT_DATE, 'YYYY-MM-DD HH24:MI:SS.FF3')",
                    [':COMMENT_DATE' => $date]
                );
                $commentModel->APPROVER_COMMENT = $comment;

                if (!$commentModel->save()) {
                    return ['status' => 'error', 'message' => 'Failed to save comment', 'errors' => $commentModel->errors];
                }

                if (!$record->save()) {
                    return ['status' => 'error', 'message' => 'Failed to update record', 'errors' => $record->errors];
                }
            }
        }

        return ['status' => 'success', 'message' => 'Records disapproved successfully'];
    }








    // /**
    //  * AJAX SEARCH STUDENT MARKSHEET JUST FOR TESTING
    //  */
    // public function actionTest()
    // {
    //     $test = LecLateMark::findAll(['HOD_APPROVAL' => 0]);
    //     dd($test);
    //     $model = new UonStudent();
    //     return $this->render('test', ['model' => $model]);
    // }
    // public function actionSearch()
    // {
    //     Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    //     $model = new UonStudent();
    //     if (!$model->load(Yii::$app->request->post())) {
    //         return ['error' => 'Invalid request'];
    //     }

    //     if (!preg_match('/^[A-Z0-9\/]{8,20}$/', $model->REGISTRATION_NUMBER)) {
    //         return ['error' => 'Invalid registration number format'];
    //     }

    //     $student = UonStudent::find()
    //         ->where(['REGISTRATION_NUMBER' => $model->REGISTRATION_NUMBER])
    //         ->cache(300)
    //         ->one();

    //     if (!$student) {
    //         return ['error' => 'Student not found'];
    //     }

    //     $marksheets = Marksheet::find()
    //         ->with(['examType', 'marksheetDef.course', 'marksheetDef.semester'])
    //         ->where(['REGISTRATION_NUMBER' => $model->REGISTRATION_NUMBER])
    //         ->all();

    //     return [
    //         'html' => $this->renderPartial('_results', [
    //             'student' => $student,
    //             'marksheets' => $marksheets
    //         ])
    //     ];
    // }
}
