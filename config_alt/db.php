<?php

require __DIR__ . '/db_constants.php';

return [
    'class' => 'app\components\DbConnection',
    'dsn' => 'oci:dbname=' . ORA_DB_SERVER . '/' . ORA_DB_DATABASE,
    'attributes' => [PDO::ATTR_PERSISTENT => false],
    'enableSchemaCache' => true,
    'schemaCacheDuration' => (60 * 60 * 24 * 7), //1 week

    'on afterOpen' => function ($event) {
        $event->sender->createCommand("ALTER SESSION SET NLS_DATE_FORMAT='DD-MON-YYYY'")->execute();
    }
];
