<?php

/**
 * TSMD 模块配置文件
 *
 * 须将此配置文件导入到 /api/web/index.php, /api/config/test.php 文件的 $config 参数
 *
 * @link https://tsmd.thirsight.com/
 * @copyright Copyright (c) 2008 thirsight
 * @license https://tsmd.thirsight.com/license/
 */

$dbTpl = require '_dbtpl.php';
$dbTplLocal = require '_dbtpl-local.php';

$baseConfig = [
    'id' => 'tsmd-api',
    'language' => 'zh-CN',
    'timeZone' => 'Asia/Shanghai',
    'params' => require 'params.php',

    // 设置路径别名，以便 Yii::autoload() 可自动加载 TSMD 自定的类
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',

        // 模块 yii2-tsmd-base
        '@tsmd/base' => __DIR__ . '/../src',
        '@tsmd/base/tests' => __DIR__ . '/../tests',
    ],

    // 设置 Yii 初始化时运行的组件
    'bootstrap' => [
        'log'
    ],

    // 设置控制器
    'controllerMap' => [
        'site' => 'tsmd\base\controllers\SiteController',
    ],

    // 设置 TSMD 模块
    'modules' => [
        'option' => [
            'class' => 'tsmd\base\option\Module',
        ],
        'aws' => [
            'class' => 'tsmd\base\aws\Module',
            's3ControllerSignKey' => '[[signKey]]',
        ],
        'dynlog' => [
            'class' => 'tsmd\base\dynlog\Module',
        ],
        'apidoc' => [
            'class' => 'tsmd\base\apidoc\Module',
            'baseUrl' => 'http://apidoc.thirsight.com',
            'outputDir' => '[[dirname]]',
        ],
        'user' => [
            'class' => 'tsmd\base\user\Module',
        ],
        'rbac' => [
            'class' => 'tsmd\base\rbac\Module',
        ],
        'captcha' => [
            'class' => 'tsmd\base\captcha\Module',
        ],
    ],

    // 组件设置
    'components' => [
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error'],
                ]
            ],
        ],
        // 数据库配置
        'db' => $dbTpl('tsmddb'),
        'dynamodb' => [
            'class' => 'tsmd\base\aws\urbanindo\dynamodb\Connection',
            'config' => [
                'credentials' => [
                    'key' => '[[key]]',
                    'secret' => '[[secret]]',
                ],
                'endpoint'   => 'https://dynamodb.ap-northeast-2.amazonaws.com',
                'region'   => 'ap-northeast-2',
                'version'  => 'latest',
            ],
        ],

        // --------------------------------------------------

        // 用于同步线上数据库到本地或沙箱
        // 生产环境数据库（只读）
        'prodDb' => $dbTpl('tsmddb', 'ro'),

        // 本地环境数据库
        'localDb' => $dbTplLocal('tsmddb'),

        // --------------------------------------------------

        // 设置核心组件参数 \yii\base\Application::coreComponents
        'i18n' => [
            'translations' => [
                'base' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@tsmd/base/messages',
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // 解决跨域请求预发送问题
                'OPTIONS <controller:.*>' => '/site/index',
            ],
        ],
        'security' => [
            'class' => 'yii\base\Security',
            'passwordHashCost' => 8,
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '[[cookieValidationKey]]',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'cache' => '\yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'tsmd\base\user\models\User',
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            //'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            //'useFileTransport' => true,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.live.com',
                'username' => 'thirsight@hotmail.com',
                'password' => '[[password]]',
                'port' => '587',
                'encryption' => 'tls',
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        's3Cache' => [
            'class' => 'tsmd\base\aws\s3\S3Cache',
            'endpoint' => 'https://s3-ap-northeast-2.amazonaws.com',
            'bucket' => 'tsmd-cache',
        ],

        // AWS
        'AWSConfig' => [
            'class' => 'tsmd\base\aws\Config',
            'credentialKey'    => '[[credentialKey]]',
            'credentialSecret' => '[[credentialSecret]]',
            'region'  => 'ap-northeast-2',
            'version' => 'latest',
        ],

        // Google Recaptcha
        'recaptcha' => [
            'class'     => 'tsmd\base\captcha\components\Recaptcha',
            'siteKey'   => '[[siteKey]]',
            'secretKey' => '[[secretKey]]',
        ],
    ],
];

// 获取 TSMD 项目下的所有模块的配置文件的值
$getModuleConfigs = function($basename) {
    $tsmdPath = dirname(dirname(__DIR__));
    $ds = DIRECTORY_SEPARATOR;
    return array_map(function($dir) use (&$tsmdPath, &$basename, &$ds) {
        $configFile = "{$tsmdPath}{$ds}{$dir}{$ds}config{$ds}{$basename}.php";
        if (is_file($configFile) && stripos($configFile, __DIR__) === false) {
            return require $configFile;
        }
        return [];
    }, scandir($tsmdPath));
};
$configs = array_filter(array_merge($getModuleConfigs('main'), [$baseConfig]));
return count($configs) == 1 ? reset($configs) : call_user_func_array(['yii\helpers\ArrayHelper', 'merge'], $configs);