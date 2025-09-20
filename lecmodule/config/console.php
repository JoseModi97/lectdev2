<?php
/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @desc configuration for the console app
 */

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/console_db.php';

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands\controllers',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'timeZone' => 'Africa/Nairobi',
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\EmailTarget',
                    'logVars' => [],
                    'levels' => ['error', 'warning', 'info'],
                    'except' => [
                        'yii\db\Command:*',
                    ],
                    'message' => [
                        'from' => ['examadmin@uonbi.ac.ke'],
                        'to' => ['examadmin@uonbi.ac.ke'],
                        'subject' => 'Lecturer module cron logs on dev',
                    ],
                ],
            ],
        ],
        'db' => $db['admin'],
        'db2' => $db['smisportal'],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.mailtrap.io',
                'username' => 'd38acd23973124',
                'password' => '4badb45ed6fd76',
                'port' => '2525',
                'encryption' => 'tls',
            ],
        ],
    ],
    'params' => $params,
];

return $config;
