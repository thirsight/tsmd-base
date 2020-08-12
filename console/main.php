<?php

/**
 * TSMD 模块配置文件
 *
 * 须将此配置文件导入到 /yii 文件的 $config 参数
 *
 * @link https://tsmd.thirsight.com/
 * @copyright Copyright (c) 2008 thirsight
 * @license https://tsmd.thirsight.com/license/
 */

$consolePath = __DIR__;
$dbTpl = require dirname($consolePath) . '/config/_dbtpl-local.php';

$baseConfig = [
    'id' => 'tsmd-console',
    'language' => 'zh-CN',
    'timeZone' => 'Asia/Shanghai',
    'basePath' => $consolePath,
    'vendorPath' => $consolePath . '/../../../',
    'params' => require dirname($consolePath) . '/config/params.php',

    // 设置路径别名，以便 Yii::autoload() 可自动加载 TSMD 自定的类
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',

        // 模块 yii2-tsmd-base
        '@tsmd/base' => $consolePath . '/../src',
        '@tsmd/base/tests' => $consolePath . '/../tests',
    ],

    // 设置命令行模式控制器
    // ./yii migrate-base/create 'tsmd\base\option\migrations\createOptionTable'
    'controllerMap' => [
        'help' => [
            'class' => 'yii\console\controllers\HelpController',
        ],
        'server' => [
            'class' => 'yii\console\controllers\ServeController',
        ],
        'migrate-base' => [
            'class' => 'yii\console\controllers\MigrateController',
            'db' => 'db',
            'migrationPath' => [
                '@yii/rbac/migrations'
            ],
            'migrationNamespaces' => [
                'tsmd\base\option\migrations',
                'tsmd\base\user\migrations',
            ]
        ],
    ],

    // 设置 Yii 初始化时运行的组件
    'bootstrap' => [
        'log',
        'gii',
    ],

    // 模块设置
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],

    // 组件设置
    'components' => [
        'db' => $dbTpl('tsmddb'),
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error'],
                ]
            ],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'cache' => '\yii\caching\FileCache',
        ],
    ],
];

// 获取 TSMD 项目下的所有模块的 console 配置文件的值
$getModuleConfigs = function() {
    $tsmdPath = dirname(dirname(__DIR__));
    $ds = DIRECTORY_SEPARATOR;
    return array_map(function($dir) use (&$tsmdPath, &$ds) {
        $configFile = "{$tsmdPath}{$ds}{$dir}{$ds}console{$ds}main.php";
        if (is_file($configFile) && stripos($configFile, __DIR__) === false) {
            return require $configFile;
        }
        return [];
    }, scandir($tsmdPath));
};
$configs = array_filter(array_merge($getModuleConfigs(), [$baseConfig]));
return count($configs) == 1 ? reset($configs) : call_user_func_array(['yii\helpers\ArrayHelper', 'merge'], $configs);