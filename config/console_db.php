<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 07-09-2021 11:14:04
 * @modify date 07-09-2021 11:14:04
 * @desc database configuration for the console app
 */

require __DIR__ . '/const.php';

return [
    'admin' => [
        'class' => 'neconix\yii2oci8\Oci8Connection',
        'dsn' => 'oci:dbname=' . ORA_DB_SERVER . '/' . ORA_DB_DATABASE, // Oracle Connection,
        'username' => AUTO_USER,
        'password' => AUTO_PASS,
        'charset' => 'utf8',
        'attributes' => [PDO::ATTR_PERSISTENT => false],
        'enableSchemaCache' => false,
    ],
    'smisportal' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=' . SMIS_PORTAL_DB_SERVER . ';dbname=' . SMIS_PORTAL_DB_NAME . ';charset=utf8mb4',
        'username' => SMIS_PORTAL_DB_USER,
        'password' => SMIS_PORTAL_DB_PASS,
        'charset' => 'utf8mb4',
        'enableSchemaCache' => true,
        'schemaCacheDuration' => (60 * 60 * 24 * 7), //1 week
        'schemaCache' => 'cache',
    ]
];
