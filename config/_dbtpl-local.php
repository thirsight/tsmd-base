<?php

return function($table) {
    return [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;port=8889;dbname=' . $table,
        'username' => 'root',
        'password' => '123456',
        'charset' => 'utf8',
    ];
};