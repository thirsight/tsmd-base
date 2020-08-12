<?php

return function($table, $user = 'rw') {
    $us = [
        'rw' => ['rootrc', '123456'],
        'ro' => ['rootro', '123456'],
    ];
    return [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;port=8889;dbname=' . $table,
        'username' => $us[$user][0],
        'password' => $us[$user][1],
        'charset' => 'utf8',
        'on afterOpen' => function($event) {
            $event->sender->createCommand("SET time_zone = '+08:00'")->execute();
        }
    ];
};