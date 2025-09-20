<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 15-12-2020 20:50:22 
 * @modify date 15-12-2020 20:50:22 
 * @desc database configuration for the web app
 */

return [
     'admin' => [
        'class' => 'app\components\DbConnection',
        'dsn' => 'oci:dbname='.ORA_DB_SERVER.'/'.ORA_DB_DATABASE, 
        'attributes' => [PDO::ATTR_PERSISTENT => false],
        'enableSchemaCache' => true, //Oracle dictionaries are too slow :(, enable caching
        'schemaCacheDuration' => (60 * 60 * 24 * 7), //1 week
        'cachedSchema' => [
            'class' => 'neconix\yii2oci8\CachedSchema',
            'cachingSchemas' => ['MUTHONI']
        ],
        'on afterOpen' => function ($event) {
            $event->sender->createCommand("ALTER SESSION SET NLS_DATE_FORMAT='DD-MON-YYYY'")->execute();     
        }
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

  


