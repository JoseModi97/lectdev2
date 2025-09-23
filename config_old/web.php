<?php

use yii\base\Model;
use kartik\grid\Module;

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'name' => 'Lecturer Module',
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'kHAhHBEI4cJv-1XcarigjThgzAYCnMh2',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => false,
            'transport' => [
                'dsn' => 'smtp://test@uonbi.ac.ke:dpopaasghbnoxgel@smtp.gmail.com:587?encryption=tls',
            ],
        ],


        // 'mailer' => [
        //     'class' => 'yii\swiftmailer\Mailer',
        //     'transport' => [
        //         'class' => 'Swift_SmtpTransport',
        //         'host' => 'smtp.gmail.com',
        //         'username' => "examadmin@uonbi.ac.ke",
        //         'password' => "@719053_nyake#.",
        //         'port' => '587',
        //         'encryption' => 'tls',
        //         // 'host' => 'smtp.mailtrap.io',
        //         // 'username' => 'd38acd23973124',
        //         // 'password' => '4badb45ed6fd76',
        //         // 'port' => '2525',
        //         // 'encryption' => 'tls',
        //     ],
        //     'enableSwiftMailerLogging' => true,
        // ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'home' => '/site/index',
                'login' => 'site/login',
                'logout' => 'site/logout',

            ],

        ],
        'formatter' => [
            'defaultTimeZone' => 'Africa/Nairobi',
            'dateFormat' => 'd-M-Y',
            'datetimeFormat' => 'd-M-Y H:i:s',
        ],


        'assetManager' => [
            'appendTimestamp' => true,
        ],
        'i18n' => [
            'translations' => [
                'yii2-ajaxcrud' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@yii2ajaxcrud/ajaxcrud/messages',
                    'sourceLanguage' => 'en',
                ],
            ]
        ],
    ],
    'modules' => [

        'gridview' => [
            'class' => Module::class
        ],
        'gridview' => [
            'class' => '\kartik\grid\Module',
        ],
    ],

    'params' => $params,
    'on ' . \yii\web\Application::EVENT_AFTER_REQUEST => function (\yii\base\Event $event) {
        if (\Yii::$app->db) {
            \Yii::$app->db->close();
            Yii::debug('Found db and closed connection.');
        }
        Yii::debug('Not found db. Exiting.');
    },
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
