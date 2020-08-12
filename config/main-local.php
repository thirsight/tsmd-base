<?php

/**
 * TSMD 模块配置文件
 *
 * 须将此配置文件导入到 /api/web/index.php 文件的 $config 参数
 *
 * @link https://tsmd.thirsight.com/
 * @copyright Copyright (c) 2008 thirsight
 * @license https://tsmd.thirsight.com/license/
 */

$dbTpl = require '_dbtpl-local.php';
$baseConfig = [
    'params' => require 'params-local.php',
    'components' => [
        'db' => $dbTpl('tsmddb'),
    ],
];
if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $baseConfig['bootstrap'][] = 'debug';
    $baseConfig['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['*']
    ];

    $baseConfig['bootstrap'][] = 'gii';
    $baseConfig['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

$configs = array_filter(array_merge($getModuleConfigs('main-local'), [$baseConfig]));
return count($configs) == 1 ? reset($configs) : call_user_func_array(['yii\helpers\ArrayHelper', 'merge'], $configs);