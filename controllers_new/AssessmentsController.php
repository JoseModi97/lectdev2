<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 28-06-2021 11:16:16 
 * @modify date 28-06-2021 11:16:16 
 * @desc Manage assessments
 */

namespace app\controllers;

use Yii;
use Exception;
use yii\web\Response;
use app\models\Course;
use yii\db\ActiveQuery;
use app\models\Department;
use app\models\MarksheetDef;
use app\components\SmisHelper;
use app\models\AssessmentType;
use yii\filters\AccessControl;
use app\models\StudentCoursework;
use yii\web\ForbiddenHttpException;
use app\models\CourseWorkAssessment;
use yii\base\InvalidConfigException;
use yii\db\Exception as dbException;
use yii\web\ServerErrorHttpException;
use app\models\search\CourseworkAssessmentSearch;

class AssessmentsController extends BaseController
{
    /**
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     */
    public function init()
    {
        parent::init();
        SmisHelper::allowAccess(['LEC_SMIS_LECTURER']);
    }

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

    /**
     * For courses with single exam components, we get the list of created course works.
     * For courses with multiple components, we get the list of created or created exam components
     * @param string $marksheetId
     * @param string $type coursework/component
     * @return string page to list course works / exam components
     * @throws ServerErrorHttpException
     */
    public function actionIndex(string $marksheetId, string $type, ?string $section = null): string
    {
        try {
           
            
            if (empty($marksheetId)) {
                throw new Exception('The marksheet must be specified.');
            }

            if ($type !== 'component' && $type !== 'coursework') {
                throw new Exception('The correct assessment type must be specified.');
            }
            if($section !== 'coursework' && $section !== 'manage-marks'){
                throw new Exception('The correct section must be specified.');
            }

            $searchModel = new CourseworkAssessmentSearch();
            $cwProvider = $searchModel->search([
                'marksheetId' => $marksheetId,
                'type' => $type
            ]);

            if (SmisHelper::requiresCoursework($marksheetId) && !$this->isSupplementary($marksheetId)) {
                $ratios = SmisHelper::getRatios($marksheetId);
                $examWeight = $ratios['examRatio'];
                $cwWeight = $ratios['cwRatio'];
            } else {
                $examWeight = 100;
                $cwWeight = null;
            }

            if ($type === 'component') {
                $title = 'Exam components definition';
            } else {
                $title = 'Course work definition';
            }
            return $this->render('index', [
                'title' => $title,
                'searchModel' => $searchModel,
                'cwProvider' => $cwProvider,
                'marksheetId' => $marksheetId,
                'cwWeight' => $cwWeight,
                'examWeight' => $examWeight,
                'type' => $type,
                'section' => $section
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
     * Create new coursework/component
     * @param string $marksheetId marksheet
     * @param string $type coursework/component
     * @return Response|string
     * @throws InvalidConfigException
     */
    public function actionCreate(string $marksheetId, string $type)
    {
        try {
            $cwModel = new CourseWorkAssessment();
            $assessmentTypeModel = new AssessmentType();
            $marksheetDefModel = MarksheetDef::findOne($marksheetId);

            if (!$this->isUserCourseLeader($marksheetId)) {
                $this->setFlash(
                    'danger',
                    'Create assessment',
                    'You may not have the necessary permissions to continue. 
                 Only course leaders can create course works and exam components'
                );
                return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
            }

            $requiresCoursework = SmisHelper::requiresCoursework($marksheetId);

            if ($type === 'component') {
                $isExamComponent = true;
                if (!$this->isSupplementary($marksheetId) && $requiresCoursework) {
                    // Make sure at least one course work is available before creating an exam component
                    $cwAssessments = CourseWorkAssessment::find()->alias('CW')
                        ->joinWith(['assessmentType AT'])
                        ->where(['CW.MARKSHEET_ID' => $marksheetId])
                        ->andWhere(['NOT', ['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT']])
                        ->asArray()->all();

                    if (empty($cwAssessments)) {
                        throw new Exception('One or more course work must be created before creating an 
                        exam component for non supplementary marksheets.');
                    }
                }
            } elseif ($type === 'coursework') {
                $isExamComponent = false;
            } else {
                throw new Exception('The assessment type to create must be specified.');
            }

            // If all assessments are locked, redirect back. Else proceed to adjust the weights
            if ($this->allAssessmentsLocked($marksheetId, $isExamComponent)) {
                $this->setFlash(
                    'danger',
                    'Create assessment',
                    'Unlock one or more assessments for this marksheet and continue.'
                );
                return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
            }

            $courseModel = Course::findOne($marksheetDefModel->COURSE_ID);
            return $this->renderAjax('_createAssessment', [
                'cwModel' => $cwModel,
                'marksheetDefModel' => $marksheetDefModel,
                'assessmentTypeModel' => $assessmentTypeModel,
                'courseModel' => $courseModel,
                'title' => 'Create assessment',
                'type' => $type
            ]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return Yii::createObject([
                'class' => 'yii\web\Response',
                'format' => Response::FORMAT_JSON,
                'data' => [
                    'success' => false,
                    'message' => $message
                ]
            ]);
        }
    }

    /**
     * Edit coursework/component
     * @param string $assessmentId
     * @param string $type
     * @return Response|string
     * @throws InvalidConfigException
     */
    public function actionEdit(string $assessmentId, string $type)
    {
        try {
            $cwModel = CourseWorkAssessment::findOne($assessmentId);
            $marksheetId = $cwModel->MARKSHEET_ID;

            if (!$this->isUserCourseLeader($marksheetId)) {
                $this->setFlash(
                    'danger',
                    'Edit assessment',
                    'You may not have the necessary permissions to continue. 
                        Only course leaders can edit course works and exam components'
                );
                return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
            }

            $assessmentTypeModel = AssessmentType::findOne($cwModel->ASSESSMENT_TYPE_ID);

            // Only unlocked assessments can be updated 
            if ($assessmentTypeModel->LOCKED === 1) {
                $this->setFlash(
                    'danger',
                    'Edit assessment',
                    'Please unlock this assessment and continue.'
                );
                return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
            }

            $courseModel = $cwModel->marksheetDef->course;

            return $this->renderAjax('_editAssessment', [
                'cwModel' => $cwModel,
                'courseModel' => $courseModel,
                'assessmentTypeModel' => $assessmentTypeModel,
                'title' => 'Edit course work',
                'type' => $type
            ]);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return Yii::createObject([
                'class' => 'yii\web\Response',
                'format' => Response::FORMAT_JSON,
                'data' => [
                    'success' => false,
                    'message' => $message
                ]
            ]);
        }
    }

    /**
     * Save coursework/component
     * @return Response
     * @throws ServerErrorHttpException
     */
    public function actionSave(): Response
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $marksheetDefPost = Yii::$app->request->post('MarksheetDef');
            $courseworkPost = Yii::$app->request->post('CourseWorkAssessment');
            $assessmentTypePost = Yii::$app->request->post('AssessmentType');
            $marksheetId = $marksheetDefPost['MRKSHEET_ID'];

            $assessmentName = trim(strtoupper($assessmentTypePost['ASSESSMENT_NAME']), ' ');

            if (empty($assessmentName)) {
                $this->setFlash('danger', 'Create assessment', 'The assessment name must be provided.');
                return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
            }

            if ($assessmentName === 'CAT' || $assessmentName === 'EXAM') {
                $this->setFlash(
                    'danger',
                    'Create assessment',
                    'The assessment name must not be CAT or EXAM.'
                );
                return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
            }

            if (strpos($assessmentName, 'EXAM_COMPONENT') !== false) {
                $this->setFlash(
                    'danger',
                    'Create assessment',
                    'The assessment name must not contain the word EXAM_COMPONENT.'
                );
                return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
            }

            $assessmentToCreate = Yii::$app->request->post('TYPE');
            if ($assessmentToCreate === 'component') {
                $assessmentType = CourseWorkAssessment::find()->alias('CW')->select(['CW.ASSESSMENT_TYPE_ID'])
                    ->joinWith(['assessmentType AT' => function (ActiveQuery $q) {
                        $q->select(['AT.ASSESSMENT_TYPE_ID']);
                    }], true, 'INNER JOIN')
                    ->where([
                        'CW.MARKSHEET_ID' => $marksheetId,
                        'AT.ASSESSMENT_NAME' => 'EXAM_COMPONENT ' . $assessmentName
                    ])->one();

                if (is_null($assessmentType)) {
                    $isExamComponent = true;
                } else {
                    $this->setFlash(
                        'danger',
                        'Create assessment',
                        'Another exam component with the same name already exists.'
                    );
                    return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
                }
            } elseif ($assessmentToCreate === 'coursework') {
                $assessmentType = CourseWorkAssessment::find()->alias('CW')->select(['CW.ASSESSMENT_TYPE_ID'])
                    ->joinWith(['assessmentType AT' => function (ActiveQuery $q) {
                        $q->select(['AT.ASSESSMENT_TYPE_ID']);
                    }], true, 'INNER JOIN')
                    ->where([
                        'CW.MARKSHEET_ID' => $marksheetId,
                        'AT.ASSESSMENT_NAME' => $assessmentName
                    ])->one();

                if (is_null($assessmentType)) {
                    $isExamComponent = false;
                } else {
                    $this->setFlash(
                        'danger',
                        'Create assessment',
                        'Another assessment with the same name already exists.'
                    );
                    return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
                }
            } else {
                throw new Exception('The assessment type to create must be specified.');
            }

            // Only course leaders can save an assessment
            if (!$this->isUserCourseLeader($marksheetId)) {
                $this->setFlash(
                    'danger',
                    'Create assessment',
                    'You may not have the necessary permissions to continue. 
                        Only course leaders can create course works and exam components'
                );
                return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
            }

            // If all assessments are locked, redirect back. Else proceed to adjust the weights
            if ($this->allAssessmentsLocked($marksheetId, $isExamComponent)) {
                $this->setFlash(
                    'danger',
                    'Create assessment',
                    'Unlock one or more assessments and continue.'
                );
                return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
            }

            $requiresCoursework = SmisHelper::requiresCoursework($marksheetId);

            $isSupplementary = $this->isSupplementary($marksheetId);

            // Marksheets with exam components don't need another exam entry.
            if (!$this->hasMultipleExamComponents($marksheetId)) {
                $this->createExamEntry($marksheetId, $requiresCoursework, $isSupplementary);
            }

            /** 
             * If no other assessment exists, this new assessment carries all the weight.
             * Else recalculate the weights of each unlocked assessment.
             */
            $count = $this->allAssessmentsCount($marksheetId, $isExamComponent);
            if ($count === '0') {
                $ratios = SmisHelper::getRatios($marksheetId);
                if ($isExamComponent) {
                    if ($requiresCoursework && !$this->isSupplementary($marksheetId)) {
                        $weight = $ratios['examRatio'];
                    } else {
                        $weight = 100;
                    }
                } else {
                    $weight = $ratios['cwRatio'];
                }
            } else {
                $assessmentDetails = $this->recalculateWeights([
                    'marksheetId' => $marksheetId,
                    'divider' => $courseworkPost['DIVIDER'],
                    'oldDivider' => null,
                    'weight' => null,
                    'assessmentID' => null,
                    'create' => true,
                    'updateOld' => false,
                    'updateNew' => false,
                    'delete' => false,
                ], $isExamComponent);
                $assessmentTotal = $assessmentDetails['assessmentTotal'];
                $weightTotal = $assessmentDetails['weightTotal'];
                $weight = ($courseworkPost['DIVIDER'] / $assessmentTotal) * $weightTotal;
            }

            $assessmentTypeModel = new AssessmentType();
            if ($isExamComponent) {
                $assessmentTypeModel->ASSESSMENT_NAME = 'EXAM_COMPONENT ' . $assessmentName;
            } else {
                $assessmentTypeModel->ASSESSMENT_NAME = $assessmentName;
            }
            if (!$assessmentTypeModel->save()) {
                throw new Exception('Failed to create assessment.');
            }
            $cwModel = new CourseWorkAssessment();
            $cwModel->MARKSHEET_ID = $marksheetId;
            $cwModel->ASSESSMENT_TYPE_ID = $assessmentTypeModel->getPrimaryKey();
            $cwModel->WEIGHT = $weight;
            $cwModel->DIVIDER = (float)$courseworkPost['DIVIDER'];
            $cwModel->RESULT_DUE_DATE = $this->getSaveDate($courseworkPost['RESULT_DUE_DATE']);
            if (!$cwModel->save()) {
                throw new Exception('Failed to create assessment.');
            }

            $transaction->commit();

            $this->setFlash('success', 'Create assessment', 'This assessment has been created successfully!');
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        } catch (Exception $ex) {
            $transaction->rollBack();
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * Update coursework/component
     * @return Response
     * @throws ServerErrorHttpException
     */
    public function actionUpdate(): Response
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $courseworkPost = Yii::$app->request->post('CourseWorkAssessment');
            $assessmentTypePost = Yii::$app->request->post('AssessmentType');
            $cwModel = CourseWorkAssessment::findOne($courseworkPost['ASSESSMENT_ID']);
            $assessmentTypeModel = AssessmentType::findOne($assessmentTypePost['ASSESSMENT_TYPE_ID']);
            $marksheetId = $cwModel->MARKSHEET_ID;
            $assessmentTypeId = $assessmentTypePost['ASSESSMENT_TYPE_ID'];

            $assessmentName = trim(strtoupper(Yii::$app->request->post('ASSESSMENT_NAME')), ' ');
            if (empty($assessmentName)) {
                $this->setFlash('danger', 'Create assessment', 'The assessment name must be provided.');
                return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
            }

            if ($assessmentName === 'CAT' || $assessmentName === 'EXAM') {
                $this->setFlash(
                    'danger',
                    'Create assessment',
                    'The assessment name must not be CAT or EXAM.'
                );
                return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
            }

            if (strpos($assessmentName, 'EXAM_COMPONENT') !== false) {
                $this->setFlash(
                    'danger',
                    'Create assessment',
                    'The assessment name must not contain the word EXAM_COMPONENT.'
                );
                return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
            }

            $assessmentToCreate = Yii::$app->request->post('TYPE');
            if ($assessmentToCreate === 'component') {
                $assessmentType = CourseWorkAssessment::find()->alias('CW')->select(['CW.ASSESSMENT_TYPE_ID'])
                    ->joinWith(['assessmentType AT' => function (ActiveQuery $q) {
                        $q->select(['AT.ASSESSMENT_TYPE_ID']);
                    }], true, 'INNER JOIN')
                    ->where([
                        'CW.MARKSHEET_ID' => $marksheetId,
                        'AT.ASSESSMENT_NAME' => 'EXAM_COMPONENT ' . $assessmentName
                    ])
                    ->andWhere(['NOT', ['AT.ASSESSMENT_TYPE_ID' => $assessmentTypeId]])
                    ->one();

                if (is_null($assessmentType)) {
                    $isExamComponent = true;
                } else {
                    $this->setFlash(
                        'danger',
                        'Update assessment',
                        'Another exam component with the same name already exists.'
                    );
                    return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
                }
            } elseif ($assessmentToCreate === 'coursework') {
                $assessmentType = CourseWorkAssessment::find()->alias('CW')->select(['CW.ASSESSMENT_TYPE_ID'])
                    ->joinWith(['assessmentType AT' => function (ActiveQuery $q) {
                        $q->select(['AT.ASSESSMENT_TYPE_ID']);
                    }], true, 'INNER JOIN')
                    ->where([
                        'CW.MARKSHEET_ID' => $marksheetId,
                        'AT.ASSESSMENT_NAME' => $assessmentName
                    ])
                    ->andWhere(['NOT', ['AT.ASSESSMENT_TYPE_ID' => $assessmentTypeId]])
                    ->one();

                if (is_null($assessmentType)) {
                    $isExamComponent = false;
                } else {
                    $this->setFlash(
                        'danger',
                        'Update assessment',
                        'Another assessment with the same name already exists.'
                    );
                    return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
                }
            } else {
                throw new Exception('The assessment type to update must be specified.');
            }

            // Only course leaders can save an assessment
            if (!$this->isUserCourseLeader($marksheetId)) {
                $this->setFlash(
                    'danger',
                    'Update assessment',
                    'You may not have the necessary permissions to continue. 
                        Only course leaders can update course works and exam components'
                );
                return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
            }

            // If all assessments are locked, redirect back. Else proceed to adjust the weights
            if ($this->allAssessmentsLocked($marksheetId, $isExamComponent)) {
                $this->setFlash(
                    'danger',
                    'Update assessment',
                    'Unlock one or more assessments and continue.'
                );
                return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
            }

            /** 
             * If only this assessment exists, it carries all the weight.
             * Else recalculate the weights of each unlocked assessment.
             */
            $count = $this->allAssessmentsCount($marksheetId, $isExamComponent);
            if ($count === '1') {
                $ratios = SmisHelper::getRatios($marksheetId);
                if ($isExamComponent) {
                    $weight = $ratios['examRatio'];
                } else {
                    $weight = $ratios['cwRatio'];
                }
            } else {
                if (strval($cwModel->WEIGHT) === $courseworkPost['WEIGHT']) {
                    $assessmentDetails = $this->recalculateWeights([
                        'marksheetId' => $marksheetId,
                        'divider' => $courseworkPost['DIVIDER'],
                        'oldDivider' => $cwModel->DIVIDER,
                        'weight' => NULL,
                        'assessmentID' => $courseworkPost['ASSESSMENT_ID'],
                        'create' => false,
                        'updateOld' => true,
                        'updateNew' => false,
                        'delete' => false,
                    ], $isExamComponent);
                    $assessmentTotal =  $assessmentDetails['assessmentTotal'];
                    $weightTotal =  $assessmentDetails['weightTotal'];
                    $weight = ($courseworkPost['DIVIDER'] / $assessmentTotal) * $weightTotal;
                } else {
                    $allTypesCount = $this->allAssessmentsCount($marksheetId, $isExamComponent);
                    $lockedTypesCount = $this->lockedAssessmentsCount($marksheetId, $isExamComponent);

                    if ((int)$allTypesCount - (int)$lockedTypesCount === 1) {
                        $this->setFlash(
                            'danger',
                            'Update assessment',
                            'Unlock one or more of the remaining assessment and continue.'
                        );
                        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
                    } else {
                        $assessmentDetails = $this->recalculateWeights([
                            'marksheetId' => $marksheetId,
                            'divider' => NULL,
                            'oldDivider' => $cwModel->DIVIDER,
                            'weight' => $courseworkPost['WEIGHT'],
                            'assessmentID' => $courseworkPost['ASSESSMENT_ID'],
                            'create' => false,
                            'updateOld' => false,
                            'updateNew' => true,
                            'delete' => false,
                        ], $isExamComponent);
                    }

                    if ($assessmentDetails === 1) {
                        $this->setFlash(
                            'danger',
                            'Update assessment',
                            'The new weight value exceeds the available weight.'
                        );
                        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
                    } else {
                        $weight = $courseworkPost['WEIGHT'];
                    }
                }
            }

            if ($isExamComponent) {
                $assessmentTypeModel->ASSESSMENT_NAME = 'EXAM_COMPONENT ' . $assessmentName;
            } else {
                $assessmentTypeModel->ASSESSMENT_NAME = $assessmentName;
            }
            if (!$assessmentTypeModel->save()) {
                throw new Exception('Failed to update the assessment.');
            }

            $cwModel->ASSESSMENT_TYPE_ID = $assessmentTypePost['ASSESSMENT_TYPE_ID'];
            $cwModel->DIVIDER = $courseworkPost['DIVIDER'];
            $cwModel->WEIGHT = $weight;
            $cwModel->RESULT_DUE_DATE = $this->getSaveDate($courseworkPost['RESULT_DUE_DATE']);
            if ($cwModel->save()) {
                $this->updateAssessmentMarks($cwModel->ASSESSMENT_ID, $cwModel->WEIGHT, $cwModel->DIVIDER);
            } else {
                throw new Exception('Failed to update the assessment.');
            }

            $transaction->commit();

            $this->setFlash(
                'success',
                'Update assessment',
                'This assessment and its associated marks have been updated successfully!'
            );
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        } catch (Exception $ex) {
            $transaction->rollBack();
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
    }

    /**
     * Lock and Unlock assessments.
     * When creating/updating/deleting assessments:
     * The weights of locked assessments are not recalculated.
     * The weights of unlocked assessments are recalculated.
     *
     * @throws InvalidConfigException
     */
    public function actionLockAndUnlock()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = Yii::$app->request->post();
            if (!empty($post['lockedIds'])) {
                foreach ($post['lockedIds'] as $value) {
                    $cwModel = CourseWorkAssessment::findOne($value);
                    $type = AssessmentType::findOne($cwModel->ASSESSMENT_TYPE_ID);
                    $type->LOCKED = 1;
                    if (!$type->save()) {
                        throw new Exception('Failed to lock an assessment.');
                    }
                }
            }
            if (!empty($post['unlockedIds'])) {
                foreach ($post['unlockedIds'] as $value) {
                    $cwModel = CourseWorkAssessment::findOne($value);
                    $type = AssessmentType::findOne($cwModel->ASSESSMENT_TYPE_ID);
                    $type->LOCKED = 0;
                    if (!$type->save()) {
                        throw new Exception('Failed to unlock an assessment.');
                    }
                }
            }
            $transaction->commit();
            $this->setFlash(
                'success',
                'Lock/Unlock Assessments',
                'Submitted assessments have been updated successfully!'
            );
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        } catch (Exception | dbException $ex) {
            $transaction->rollBack();
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return Yii::createObject([
                'class' => 'yii\web\Response',
                'format' => Response::FORMAT_JSON,
                'data' => [
                    'success' => false,
                    'message' => $message
                ]
            ]);
        }
    }

    /**
     * Delete coursework/component
     * @param string $assessmentId
     * @param string $type
     * @return string|Response
     * @throws InvalidConfigException
     * @throws \Throwable
     */
    public function actionDelete(string $assessmentId, string $type)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $cwModel = CourseWorkAssessment::findOne($assessmentId);
            $marksheetId = $cwModel->MARKSHEET_ID;
            $assessmentTypeId =  $cwModel->ASSESSMENT_TYPE_ID;

            if ($type === 'component') {
                $isExamComponent = true;
            } elseif ($type === 'coursework') {
                $isExamComponent = false;
            } else {
                throw new Exception('The assessment type to create must be specified.');
            }

            // Only course leaders can delete an assessment
            if (!$this->isUserCourseLeader($marksheetId)) {
                $this->setFlash(
                    'danger',
                    'Create assessment',
                    'You may not have the necessary permissions to continue.  
                     Only course leaders can delete course works and exam components'
                );
                return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
            }

            $allTypesCount = $this->allAssessmentsCount($marksheetId, $isExamComponent);
            $lockedTypesCount = $this->lockedAssessmentsCount($marksheetId, $isExamComponent);

            /** 
             * If there's only one assessment left, just delete. Locked or otherwise.
             * If only two assessments are left, the remaining assessment automatically carries all the weight. 
             * Locked or otherwise.
             */
            if ($allTypesCount === '1') {
                $cwModel->delete();
                AssessmentType::findOne($assessmentTypeId)->delete();
            } elseif ($allTypesCount === '2') {
                if ($isExamComponent) {
                    $otherModel = CourseWorkAssessment::find()->alias('CW')->joinWith(['assessmentType AT'])
                        ->where(['CW.MARKSHEET_ID' => $cwModel->MARKSHEET_ID])
                        ->andWhere(['NOT', ['CW.ASSESSMENT_ID' => $assessmentId]])
                        ->andWhere(['like', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT'])
                        ->one();
                } else {
                    $otherModel = CourseWorkAssessment::find()->alias('CW')->joinWith(['assessmentType AT'])
                        ->where(['CW.MARKSHEET_ID' => $cwModel->MARKSHEET_ID])
                        ->andWhere(['NOT', ['CW.ASSESSMENT_ID' => $assessmentId]])
                        ->andWhere(['NOT', ['AT.ASSESSMENT_NAME' => 'EXAM']])
                        ->andWhere(['NOT', ['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT']])
                        ->one();
                }
                $weight = $otherModel->WEIGHT;
                $otherModel->WEIGHT =  $weight  + $cwModel->WEIGHT;
                if ($otherModel->save()) {
                    $this->updateAssessmentMarks($otherModel->ASSESSMENT_ID, $otherModel->WEIGHT, $otherModel->DIVIDER);
                } else {
                    throw new Exception('Failed to update the remaining assessment.');
                }
                $cwModel->delete();
                AssessmentType::findOne($assessmentTypeId)->delete();
            } else {
                if ((int)$allTypesCount - (int)$lockedTypesCount === 1) {
                    if ($cwModel->assessmentType->LOCKED === 1)
                        $this->setFlash(
                            'danger',
                            'Delete assessment',
                            'Unlock this assessment to proceed.'
                        );
                    else
                        $this->setFlash(
                            'danger',
                            'Delete assessment',
                            'Unlock one or more of the remaining assessments and continue.'
                        );
                    return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
                }

                /**
                 * If there exists more than 2 unlocked assessments adjust the weights.
                 * If all assessments are locked, redirect back. 
                 */
                if ($this->allAssessmentsLocked($marksheetId, $isExamComponent)) {
                    $this->setFlash(
                        'danger',
                        'Lock/Unlock Assessments',
                        'Unlock one or more of the remaining assessments and continue'
                    );
                    return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
                } else {
                    $cwModel->delete();
                    AssessmentType::findOne($assessmentTypeId)->delete();

                    $this->recalculateWeights([
                        'marksheetId' => $marksheetId,
                        'divider' => NULL,
                        'oldDivider' => NULL,
                        'weight' => NULL,
                        'assessmentID' => NULL,
                        'create' => false,
                        'updateOld' => false,
                        'updateNew' => false,
                        'delete' => true,
                    ], $isExamComponent);
                }
            }

            StudentCoursework::deleteAll(['ASSESSMENT_ID' => $assessmentId]);
            $transaction->commit();

            $this->setFlash(
                'success',
                'Delete assessment',
                'This assessment and its associated marks have been deleted successfully!'
            );
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        } catch (Exception | dbException $ex) {
            $transaction->rollBack();
            $message = $ex->getMessage();
            if (YII_ENV_DEV) {
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            return Yii::createObject([
                'class' => 'yii\web\Response',
                'format' => Response::FORMAT_JSON,
                'data' => [
                    'success' => false,
                    'message' => $message
                ]
            ]);
        }
    }

    /**
     * Check if the user is course leader.
     * Managing assessments requires that the user must be the course leader.
     * @param string $marksheetId identifies the marksheet definition.
     * @return bool True if user is course leader. Else false.
     */
    private function isUserCourseLeader(string $marksheetId): bool
    {
        $marksheetDefModel = MarksheetDef::find()->select(['PAYROLL_NO'])
            ->where(['MRKSHEET_ID' => $marksheetId])->asArray()->one();
        if (intval($marksheetDefModel['PAYROLL_NO']) == $this->payrollNo) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if all the assessments to this marksheet are locked.
     * Locked assessments are not used in weight recalculation.
     * @param string $marksheetId
     * @param bool $isExamComponent
     * @return bool
     */
    private function allAssessmentsLocked(string $marksheetId, bool $isExamComponent): bool
    {
        // Get no. of all assessments to this marksheet
        $allTypes = $this->allAssessmentsCount($marksheetId, $isExamComponent);

        //Get no. of locked assessments to this marksheet
        $lockedTypes = $this->lockedAssessmentsCount($marksheetId, $isExamComponent);

        if ($allTypes !== '0' && $lockedTypes !== '0') {
            if ($allTypes === $lockedTypes) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Get no. of all assessments to this marksheet. Locked and Unlocked
     * @param string $marksheetId
     * @param bool $isExamComponent
     * @return string
     */
    private function allAssessmentsCount(string $marksheetId, bool $isExamComponent): string
    {
        if ($isExamComponent) {
            $allTypes = CourseWorkAssessment::find()->alias('CW')
                ->joinWith(['assessmentType AT'])
                ->where(['CW.MARKSHEET_ID' => $marksheetId])
                ->andWhere(['like', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT'])
                ->count();
        } else {
            $allTypes = CourseWorkAssessment::find()->alias('CW')
                ->joinWith(['assessmentType AT'])
                ->where(['CW.MARKSHEET_ID' => $marksheetId])
                ->andWhere(['NOT', ['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT']])
                ->andWhere(['NOT', ['AT.ASSESSMENT_NAME' => 'EXAM']])
                ->count();
        }
        return $allTypes;
    }

    /**
     * Get no. of all locked assessments to this marksheet
     * @param string $marksheetId
     * @param bool $isExamComponent
     * @return string
     */
    private function lockedAssessmentsCount(string $marksheetId, bool $isExamComponent): string
    {
        if ($isExamComponent) {
            $lockedTypes = CourseWorkAssessment::find()->alias('CW')
                ->joinWith(['assessmentType AT'])
                ->where(['CW.MARKSHEET_ID' => $marksheetId, 'AT.LOCKED' => 1])
                ->andWhere(['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT'])
                ->count();
        } else {
            $lockedTypes = CourseWorkAssessment::find()->alias('CW')
                ->joinWith(['assessmentType AT'])
                ->where(['CW.MARKSHEET_ID' => $marksheetId, 'AT.LOCKED' => 1])
                ->andWhere(['NOT', ['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT']])
                ->andWhere(['NOT', ['AT.ASSESSMENT_NAME' => 'EXAM']])
                ->count();
        }
        return $lockedTypes;
    }

    /**
     * Get the locked/unlocked assessments for a marksheet.
     * We use the isExamComponent flag to limit the assessments to exam components type
     * when dealing with marksheets with multiple exam components. 
     * isExamComponent is set true when assessment is exam component. Else false.
     * 
     * @param string $marksheetId the marksheet 
     * @param bool $isExamComponent identifies the assessment type
     * 
     * @return array [locked assessments, unlocked assessments]
     */
    private function getLockedAndUnlockedAssessments(string $marksheetId, bool $isExamComponent): array
    {
        if ($isExamComponent) {
            $unlockedAssessments = CourseWorkAssessment::find()->alias('CW')
                ->joinWith(['assessmentType AT'])
                ->where(['CW.MARKSHEET_ID' => $marksheetId, 'AT.LOCKED' => 0])
                ->andWhere(['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT'])
                ->all();

            $lockedAssessments = CourseWorkAssessment::find()->alias('CW')
                ->joinWith(['assessmentType AT'])
                ->where(['CW.MARKSHEET_ID' => $marksheetId, 'AT.LOCKED' => 1])
                ->andWhere(['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT'])
                ->all();
        } else {
            $unlockedAssessments = CourseWorkAssessment::find()->alias('CW')
                ->joinWith(['assessmentType AT'])
                ->where(['CW.MARKSHEET_ID' => $marksheetId, 'AT.LOCKED' => 0])
                ->andWhere(['NOT', ['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT']])
                ->andWhere(['NOT', ['AT.ASSESSMENT_NAME' => 'EXAM']])
                ->all();

            $lockedAssessments = CourseWorkAssessment::find()->alias('CW')
                ->joinWith(['assessmentType AT'])
                ->where(['CW.MARKSHEET_ID' => $marksheetId, 'AT.LOCKED' => 1])
                ->andWhere(['NOT', ['LIKE', 'AT.ASSESSMENT_NAME', 'EXAM_COMPONENT']])
                ->andWhere(['NOT', ['AT.ASSESSMENT_NAME' => 'EXAM']])
                ->all();
        }

        return [
            'unlockedAssessments' => $unlockedAssessments,
            'lockedAssessments' => $lockedAssessments
        ];
    }

    /**
     * Readjust the previous weights of unlocked assessments.
     * @param array $assessmentDetails Various configs for weight adjustments
     * @throws Exception
     * @return int|array [$assessmentTotal, $weightTotal]
     */
    private function recalculateWeights(array $assessmentDetails, bool $isExamComponent)
    {
        $assessmentTotal = 0;
        $weightTotal = 0;
        $marksheetId =  $assessmentDetails['marksheetId'];

        $assessments = $this->getLockedAndUnlockedAssessments($marksheetId, $isExamComponent);
        $unlockedTypes = $assessments['unlockedAssessments'];
        $lockedTypes = $assessments['lockedAssessments'];

        // Create new assessment
        if ($assessmentDetails['create']) {
            $assessmentTotal = $assessmentDetails['divider'];
            foreach ($unlockedTypes as $type) {
                $assessmentTotal += $type->DIVIDER;
            }

            foreach ($unlockedTypes as $type) {
                $weightTotal += $type->WEIGHT;
            }
        }
        // Update an assessment with the old weight value
        elseif ($assessmentDetails['updateOld']) {
            $assessmentTotal = $assessmentDetails['divider'];
            foreach ($unlockedTypes as $type) {
                $assessmentTotal += $type->DIVIDER;
            }
            $assessmentTotal -= $assessmentDetails['oldDivider'];

            foreach ($unlockedTypes as $type) {
                $weightTotal += $type->WEIGHT;
            }
        }
        // Update an assessment with a new weight value
        elseif ($assessmentDetails['updateNew']) {
            foreach ($unlockedTypes as $type) {
                $assessmentTotal += $type->DIVIDER;
            }
            $assessmentTotal -= $assessmentDetails['oldDivider'];

            foreach ($unlockedTypes as $type) {
                $weightTotal += $type->WEIGHT;
            }

            if ($weightTotal < $assessmentDetails['weight']) {
                return 1; // Indicates the new weight exceeds the available weight
            } else {
                $weightTotal -= $assessmentDetails['weight'];
            }
        }
        // Delete an assessment  
        elseif ($assessmentDetails['delete']) {
            $lockedWeight = 0;

            $ratios = SmisHelper::getRatios($marksheetId);
            if ($isExamComponent) {
                if (SmisHelper::requiresCoursework($marksheetId) && !$this->isSupplementary($marksheetId)) {
                    $weight = $ratios['examRatio'];
                } else {
                    $weight = 100;
                }
            } else {
                $weight = $ratios['cwRatio'];
            }

            if ($lockedTypes === NULL) {
                $weightTotal = $weight;
            } else {
                foreach ($lockedTypes as $type) {
                    $lockedWeight += $type->WEIGHT;
                }
                $weightTotal = $weight - $lockedWeight;
            }

            foreach ($unlockedTypes as $type) {
                $assessmentTotal += $type->DIVIDER;
            }
        }

        // Recalculate the new weights of unlocked assessments and recompute the marks
        foreach ($unlockedTypes as $type) {
            if ($weightTotal === 0)
                $type->WEIGHT = 0;
            else
                $type->WEIGHT =  ($type->DIVIDER / $assessmentTotal) * $weightTotal;
            $saved = $type->save();

            if ($saved) {
                $this->updateAssessmentMarks($type->ASSESSMENT_ID, $type->WEIGHT, $type->DIVIDER);
            } else {
                throw new Exception('Failed to update the assessment.');
            }
        }

        return [
            'assessmentTotal' => $assessmentTotal,
            'weightTotal' => $weightTotal
        ];
    }

    /**
     * Update the assessment marks, when the assessment weights change
     * @param $assessmentId
     * @param $weight
     * @param $divider
     * @return void
     * @throws Exception
     * @todo seek clarification on what happens when marks have been submitted
     */
    private function updateAssessmentMarks($assessmentId, $weight, $divider)
    {
        $scModel = StudentCoursework::find()->where(['ASSESSMENT_ID' => $assessmentId])->all();
        $cwAssessment = CourseWorkAssessment::findOne($assessmentId);
        if (!empty($scModel)) {
            foreach ($scModel as $student) {
                $student->MARK = round(($student->RAW_MARK / $divider) * $weight, 2);
                $student->save();
            }
        }
        SmisHelper::updateMarksConsolidatedFlag($cwAssessment->MARKSHEET_ID);
    }

    /**
     * Some marksheets have their exams divided into multiple components.
     * @param string $marksheetId
     * @return bool
     * @throws Exception
     */
    private function hasMultipleExamComponents(string $marksheetId): bool
    {
        $marksheet = MarksheetDef::find()->select(['COURSE_ID'])->where(['MRKSHEET_ID' => $marksheetId])->one();

        $course = Course::find()->select(['DEPT_CODE'])->where(['COURSE_ID' => $marksheet->COURSE_ID])->one();

        $department = Department::find()->select(['FAC_CODE'])->where(['DEPT_CODE' => $course->DEPT_CODE])->one();

        if (is_null($department) || is_null($department->FAC_CODE)) {
            throw new Exception('This marksheet is missing the department.');
        }

        if (in_array($department->FAC_CODE, Yii::$app->params['facultiesWithMultipleExams'])) {
            return true;
        } else {
            return false;
        }
    }
}
