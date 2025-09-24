<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 15-12-2020 20:50:22 
 * @modify date 15-12-2020 20:50:22 
 * @desc configuration for the web app
 */

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'name' => 'Exam and coursework management module',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@views' => '@app/views'
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'AuTtxO41YMju8oB8vd7AKQlZQFq9VZLz',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableSession'	=> true,
            'enableAutoLogin' => false,
            'authTimeout' => 600000
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
            'maxSourceLines' => 20,
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
//                 'host' => 'smtp.gmail.com',
//                 'username' => "examadmin@uonbi.ac.ke",
//                 'password' => "@719053_nyake#.",
//                 'port' => '587',
//                 'encryption' => 'tls'
//                'host' => 'smtp.mailtrap.io',
//                'username' => 'd38acd23973124',
//                'password' => '4badb45ed6fd76',
//                'port' => '2525',
//                'encryption' => 'tls',
            ],
            'enableSwiftMailerLogging' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db['admin'],
        'db2' => $db['smisportal'],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'home' => '/site/index',
                'login' => '/site/login',
                'logout' => '/site/logout',
            ],
        ],
        'formatter' => [
            'defaultTimeZone' => 'Africa/Nairobi',
            'dateFormat' => 'd-M-Y',
            'datetimeFormat' => 'd-M-Y H:i:s'
        ],
        'assetManager' => [
            'appendTimestamp' => true,
        ],
        'i18n' => [
            'translations' => [
                'yii2-ajaxcrud' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@vendor/dmstr/yii2-ajaxcrud/messages', 
                    'sourceLanguage' => 'en-US', 
                    'fileMap' => [
                        'yii2-ajaxcrud' => 'ajaxcrud.php',
                    ],
                ],
                
            ],
        ],
    ],
    'modules' => [
        'gridview' => ['class' => 'kartik\grid\Module'],
        'datecontrol' =>  [ 'class' => '\kartik\datecontrol\Module',],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
        'allowedIPs' => ['*']
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
        'allowedIPs' => ['*']
    ];
}

return $config;
