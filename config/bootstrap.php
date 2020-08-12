<?php

// Request
\Yii::$container->set('yii\web\Request', [
    'class' => 'tsmd\base\yii\YiiRequest',
    'parsers' => [
        'application/json' => 'yii\web\JsonParser',
        'text/plain' => 'yii\web\JsonParser',
    ],
    'acceptableContentTypes' => ['*/*' => []],
]);

// Formatter
\Yii::$container->set('yii\i18n\Formatter', [
    'nullDisplay' => '',
    'defaultTimeZone' => 'Asia/Shanghai',
    'dateFormat' => "php:Y-m-d",
    'timeFormat' => "php:H:i:s",
    'datetimeFormat' => "php:Y-m-d H:i:s",

    'as common' => ['class' => 'tsmd\base\yii\YiiFormatterBehavior'],
]);
