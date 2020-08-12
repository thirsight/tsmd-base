<?php

namespace tsmd\base\user\migrations;

use Yii;
use yii\db\Migration;
use yii\rbac\Item;
use tsmd\base\user\models\User;

/**
 * Handles the creation of table `{{%user}}`.
 */
class M200601235958CreateUserTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = '{{%user}}';
        $sql = <<<SQL
CREATE TABLE {$table} (
    `uid`      INT(11) UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `alpha2`   CHAR(2) NOT NULL DEFAULT '',
    `mobile`   VARCHAR(16),
    `email`    VARCHAR(128),
    `username` VARCHAR(64),
    `realname` VARCHAR(128) NOT NULL DEFAULT '',
    `nickname` VARCHAR(128) NOT NULL DEFAULT '',
    `role`     TINYINT(2) NOT NULL DEFAULT 0,
    `status`   SMALLINT(3) NOT NULL DEFAULT 0,
    `slug`     VARCHAR(64),
    `authKey`  VARCHAR(64) NOT NULL,
    `passwordHash` TEXT NOT NULL,
    `createdTime`  INT(11) NOT NULL,
    `updatedTime`  INT(11) NOT NULL,
    UNIQUE KEY `mobile` (`mobile`),
    UNIQUE KEY `email` (`email`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `slug` (`slug`),
    INDEX `status` (`status`),
    INDEX `role` (`role`),
    INDEX `createdTime` (`createdTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE {$table} AUTO_INCREMENT = 100001;
SQL;
        $this->getDb()->createCommand($sql)->execute();

        $this->initUsers();
        $this->initRbac();
    }

    /**
     * 初始用户（管理员与普通用户）
     */
    private function initUsers()
    {
        $user = new User();
        $user->uid =  1;
        $user->username = 'admin';
        $user->status = User::STATUS_OK;
        $user->role = User::ROLE_ADMIN;
        $user->setAuthKey();
        $user->setPassword('123456');
        $user->insert(false);

        $user = new User();
        $user->uid =  1000;
        $user->username = 'member';
        $user->status = User::STATUS_OK;
        $user->role = User::ROLE_MEMBER;
        $user->setAuthKey();
        $user->setPassword('123456');
        $user->insert(false);
    }

    /**
     * 初始管理员权限
     */
    private function initRbac()
    {
        $item = new Item(['type' => Item::TYPE_PERMISSION, 'name' => '/*']);
        Yii::$app->authManager->add($item);
        Yii::$app->authManager->assign($item, 1);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
