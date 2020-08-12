<?php

namespace tsmd\base\user\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%usermbr}}`.
 */
class M200601235959CreateUsermbrTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = '{{%usermbr}}';
        $sql = <<<SQL
CREATE TABLE {$table} (
  `mbrUid`      INT(11) UNSIGNED PRIMARY KEY NOT NULL,
  `isMobile`    TINYINT(1) NOT NULL DEFAULT 0,
  `isTablet`    TINYINT(1) NOT NULL DEFAULT 0,
  `isDesktop`   TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $this->getDb()->createCommand($sql)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%usermbr}}');
    }
}
