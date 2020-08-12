<?php

/**
 * 关于创建、授权数据库用户
 *
 * CREATE USER 'username'@'%' IDENTIFIED BY 'password';
 * SHOW GRANTS FOR 'username';
 * REVOKE ALL ON `tabename`.* FROM 'username'@'%';
 * GRANT SELECT, INSERT, UPDATE, DELETE, CREATE VIEW ON `tabename`.* TO 'username'@'%';
 */

return function($table, $user = 'rw') {
    $us = [
        'rw' => ['usernamerw', 'password'],
        'ro' => ['usernamero', 'password'],
    ];
    return [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;port=3306;dbname=' . $table,
        'username' => $us[$user][0],
        'password' => $us[$user][1],
        'charset' => 'utf8',
        'on afterOpen' => function($event) {
            $event->sender->createCommand("SET time_zone = '+08:00'")->execute();
        }
    ];
};