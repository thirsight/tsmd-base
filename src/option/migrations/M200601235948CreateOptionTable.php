<?php

namespace tsmd\base\option\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%option}}`.
 */
class M200601235948CreateOptionTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = '{{%option}}';
        $sql = <<<SQL
CREATE TABLE {$table} (
  `id`    smallint(6) UNSIGNED NOT NULL,
  `group` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key`   varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `autoload`  tinyint(1) NOT NULL DEFAULT '0',
  `sort`      smallint(6) NOT NULL DEFAULT '0',
  `createdTime` int(11) NOT NULL,
  `updatedTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE {$table}
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `groupKey` (`group`,`key`),
  ADD KEY `group` (`group`),
  ADD KEY `key` (`key`),
  ADD KEY `sort` (`sort`);

ALTER TABLE {$table}
  MODIFY `id` smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT;
SQL;
        $this->getDb()->createCommand($sql)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%option}}');
    }
}
