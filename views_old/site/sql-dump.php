<?php

$db = Yii::$app->get('admin');

$sql = "
    SELECT
        ev.PAYROLL_NO,
        ev.SURNAME,
        ev.OTHER_NAMES,
        ev.EMP_TITLE,
        CASE
            WHEN md.PAYROLL_NO IS NOT NULL THEN 'Lead'
            ELSE 'Other'
        END AS LECTURER_ROLE,
        ca.MRKSHEET_ID
    FROM
        smis.ADM_COURSE_ASSIGNMENT ca
    JOIN
        smis.HR_EMP_VERIFY_V ev ON ca.PAYROLL_NO = ev.PAYROLL_NO
    LEFT JOIN
        smis.EXM_MARKSHEET_DEF md ON ca.PAYROLL_NO = md.PAYROLL_NO AND ca.MRKSHEET_ID = md.MRKSHEET_ID;
";

try {
    $result = $db->createCommand($sql)->queryAll();
    echo "<pre>";
    print_r($result);
    echo "</pre>";
} catch (Exception $e) {
    echo "An error occurred while executing the query: <br>";
    echo $e->getMessage();
}
