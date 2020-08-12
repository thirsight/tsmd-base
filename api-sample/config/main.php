<?php

$appPath = dirname(__DIR__);
return [
    'basePath' => $appPath,
    'vendorPath' => dirname($appPath) . '/vendor',
    'controllerNamespace' => 'api\controllers',
    'aliases' => [
        '@api' => $appPath,
    ],
];