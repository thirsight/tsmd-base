<?php

$appPath = dirname(__DIR__);
$tsmdPath = $appPath . '/../vendor/thirsight';

$config = yii\helpers\ArrayHelper::merge(
    require $tsmdPath . '/yii2-tsmd-base/config/main.php',
    require $appPath . '/config/main.php',
    [
        'id' => 'tsmd-api-tests',
    ]
);

return $config;