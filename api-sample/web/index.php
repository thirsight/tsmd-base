<?php

// 是否在 localhost 或者沙箱环境下运行
if (in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    defined('YII_DEBUG') or define('YII_DEBUG', true);
    defined('YII_ENV') or define('YII_ENV', 'dev');
}

$appPath = dirname(__DIR__);
$tsmdPath = $appPath . '/../vendor/thirsight';

require $appPath . '/../vendor/autoload.php';
require $appPath . '/../vendor/yiisoft/yii2/Yii.php';
require $tsmdPath . '/yii2-tsmd-base/config/bootstrap.php';

if (YII_ENV_DEV) {
    $config = \yii\helpers\ArrayHelper::merge(
        require $tsmdPath . '/yii2-tsmd-base/config/main.php',
        require $tsmdPath . '/yii2-tsmd-base/config/main-local.php',
        require $appPath . '/config/main.php'
    );

} else {
    $config = \yii\helpers\ArrayHelper::merge(
        require $tsmdPath . '/yii2-tsmd-base/config/main.php',
        require $appPath . '/config/main.php'
    );
}
(new yii\web\Application($config))->run();