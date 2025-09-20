<?php

/**
 * @author Jack Jmm<jackmutiso37@gmail.com>
 */

namespace app\helpers;


use Yii;
use DateTime;
use Exception;
use DateTimeZone;
use yii\helpers\Url;
use yii\bootstrap5\Html;
use kartik\grid\GridView;
use kartik\widgets\Growl;
use yii\web\ForbiddenHttpException;

class GraduateHelper
{

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
     * @return array user roles
     */
    private static function getUserRoles(): array
    {
        return Yii::$app->session->get('roles');
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
     * Get the format used for dates
     * @return string
     */
    public static function getDateFormat(): string
    {
        return Yii::$app->components['formatter']['dateFormat'];
    }

    /**
     * Get the format used for dates and time
     * @return string
     */
    public static function getDateTimeFormat(): string
    {
        return Yii::$app->components['formatter']['datetimeFormat'];
    }

    /**
     * Get the format used for dates and time
     * @return string
     */
    public static function getDefaultTimezone(): string
    {
        return Yii::$app->components['formatter']['defaultTimeZone'];
    }

    /**
     * Format date and/or time into various formats
     * @throws Exception
     */
    public static function formatDate(string $dateToFormat, string $format): string
    {
        $newDate = new DateTime($dateToFormat, new DateTimeZone(self::getDefaultTimezone()));
        return $newDate->format($format);
    }

    /**
     * Creates breadcrumb based on the given urls. Url link is null if address is not needed
     * @param array $urls ['url name' => 'Url link]
     * @return string
     */
    public static function createBreadcrumb(array $urls): string
    {
        $breadcrumb = '<div class="content-header" style=" background:#f5f5f5;">';
        $breadcrumb .= '<div class="page-header" style="display: flex; align-items: center; padding: 10px 20px;">';
        $breadcrumb .= '<h6 style="margin: 0; font-size: 14px; font-weight: normal; color: #6c757d;">';

        $breadcrumb .= '<a href="' . Yii::$app->homeUrl . 'graduate-system" class="btn-link" style="color: #007bff; text-decoration: none;">Home</a>';
        $breadcrumb .= ' <i class="bi bi-chevron-right" aria-hidden="true" style="margin: 0 8px; color: #6c757d;"></i> ';

        foreach ($urls as $key => $url) {
            if (empty($url)) {
                $breadcrumb .= '<span style="color: #6c757d;">' . $key . '</span>';
            } else {
                $breadcrumb .= '<a href="' . $url . '" class="btn-link" style="color: #007bff; text-decoration: none;">' . $key . '</a>';
                $breadcrumb .= ' <i class="bi bi-chevron-right" aria-hidden="true" style="margin: 0 8px; color: #6c757d;"></i> ';
            }
        }

        $breadcrumb .= '</h6></div></div>';
        return $breadcrumb;
    }
    public static function createBreadcrumbWithoutHome(array $urls): string
    {
        $breadcrumb = '<div class="content-header" style="padding: 10px 20px;">';
        $breadcrumb .= '<div class="page-header" style="display: flex; align-items: center;">';
        $breadcrumb .= '<h6 style="margin: 0; font-size: 14px; font-weight: normal; color: #6c757d;">';


        foreach ($urls as $key => $url) {
            if (empty($url)) {
                $breadcrumb .= '<span style="color: #6c757d;">' . $key . '</span>';
            } else {
                $breadcrumb .= '<a href="' . $url . '" class="btn-link" style="color: #007bff; text-decoration: none;">' . $key . '</a>';
                $breadcrumb .= ' <i class="bi bi-chevron-right" aria-hidden="true" style="margin: 0 8px; color: #6c757d;"></i> ';
            }
        }

        $breadcrumb .= '</h6></div></div>';
        return $breadcrumb;
    }

    public static function sendMail($to, $subject, $textBody, $htmlBody)
    {
        $from = Yii::$app->params['adminEmail'];

        try {
            $result = Yii::$app->mailer->compose()
                ->setFrom($from)
                ->setTo($to)
                ->setSubject($subject)
                ->setTextBody($textBody)
                ->setHtmlBody($htmlBody)
                ->send();

            if (!$result) {
                Yii::error("Email to {$to} failed to send.", 'email');
            }

            return $result;
        } catch (\Exception $e) {
            Yii::error("Email error: " . $e->getMessage(), 'email');
            return false;
        }
    }
    public static function sendMailAttachment($email, $subject, $htmlBody, $filePath)
    {
        $from = Yii::$app->params['adminEmail'];

        try {
            Yii::$app->mailer->compose()
                ->setTo($email)
                ->setFrom([$from => 'University of Nairobi Graduate School'])
                ->setSubject($subject)
                ->setHtmlBody($htmlBody)
                ->attach($filePath)
                ->send();

            // return true;
        } catch (\Exception $e) {
            Yii::error("Email error: " . $e->getMessage(), 'email');
            return false;
        }
    }


    public static function generateGridView($dataProvider, $searchModel, $heading, $before, $columns, $id)
    {
        $gridView = '<div id="ajaxCrudDatatable">';
        $gridView .=
            GridView::widget([
                'id' => 'crud-datatable',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'export' => false,
                'pjax' => true,
                'striped' => true,
                'hover' => true,
                'condensed' => true,
                'responsive' => true,
                'columns' => $columns,

            ]);
        $gridView .= '</div>';

        return $gridView;
    }

    public static function growl($title, $body)
    {
        $growl = Growl::widget([
            'type' => Growl::TYPE_SUCCESS,
            'title' => Yii::$app->session->getFlash('title'),
            'icon' => 'bi bi-check-circle',
            'body' => $body,
            'showSeparator' => true,
            'delay' => 0,
            'pluginOptions' => [
                'showProgressbar' => true,
                'placement' => [
                    'from' => 'top',
                    'align' => 'right',
                ],
                'closeButton' => false,
            ]
        ]);

        return $growl;
    }
    public static function getApprovalLevel($model)
    {
        $level = "";
        if ($model['CHAIRMAN_APPROVED'] == 1) {
            $level = 'CHAIRMAN APPROVED';
        }
        if ($model['CHAIRMAN_APPROVED'] == 2) {
            $level = 'CHAIRMAN(HOD)';
        }
        if ($model['FACULTY_APPROVED'] == 1) {
            $level = 'FACULTY APPROVED';
        }
        if ($model['FACULTY_APPROVED'] == 2) {
            $level = 'FACULTY';
        }
        if ($model['BPS_APPROVED'] == 1) {
            $level = 'BPS APPROVED';
        }
        if ($model['BPS_APPROVED'] == 2) {
            $level = 'GRADUATE SECTOR';
        }

        return "<i class='bi bi-arrow-up-circle'></i> " . $level;
    }

    public static function renderStatusLine($model, $prefix)
    {
        return
            Html::tag(
                'span',
                $model["pending_$prefix"] . ' Pending',
                ['class' => 'status-pending']
            ) . ' ' .
            Html::tag(
                'span',
                $model["approved_$prefix"] . ' Approved',
                ['class' => 'status-approved']
            ) . ' ' .
            Html::tag(
                'span',
                $model["rejected_$prefix"] . ' Rejected',
                ['class' => 'status-rejected']
            );
    }
    public static function date($column)
    {
        $date = date('Y-m-d H:i:s') . '.000';
        return new \yii\db\Expression(
            "TO_TIMESTAMP(:$column, 'YYYY-MM-DD HH24:MI:SS.FF3')",
            [":$column" => $date]
        );
    }

    /**
     * Generates the next ID with a given prefix based on the maximum existing ID in the table.
     *
     * @param string $column The name of the column to search for the max value.
     * @param string $table The name of the table.
     * @param string $prefix The prefix to strip and re-append.
     * @return string The next ID with the prefix.
     */
    public static function generateId($column, $table, $prefix)
    {
        $maxId = \Yii::$app->db->createCommand("SELECT MAX($column) FROM $table")->queryScalar();

        // If no existing ID is found, start with 1
        if (!$maxId || strpos($maxId, $prefix) !== 0) {
            $nextNumber = 1;
        } else {
            $numberPart = substr($maxId, strlen($prefix));
            $nextNumber = (int)$numberPart + 1;
        }

        // You can pad the number part with zeros if needed
        $formattedNumber = $nextNumber; // adjust padding as needed

        return  $formattedNumber;
    }
}
