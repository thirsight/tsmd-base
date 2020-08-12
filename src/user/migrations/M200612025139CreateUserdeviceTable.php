<?php

namespace tsmd\base\user\migrations;

use yii\db\Migration;

/**
 * Class m200612_025139_userDevice
 */
class M200612025139CreateUserdeviceTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('userdevice',[
            'udUid' => $this->integer(11)->unsigned()->notNull(),
            'udUdid' => $this->string(64)->notNull(),
            'udType' => $this->string(64)->notNull()->defaultValue(''),
            'udName' => $this->string(64)->notNull()->defaultValue(''),
            'udPlatform' => $this->string(32)->notNull()->defaultValue(''),
            'udBrowser' => $this->string(32)->notNull()->defaultValue(''),
            'udIP' => $this->string(64)->notNull(),
            'udRSAPubkey' => $this->text()->null(),
            'createdTime' => $this->integer(11)->notNull(),
            'updatedTime' => $this->integer(11)->notNull(),
            'PRIMARY KEY(udUid, udUdid)'
        ]);
        $this->createIndex('udUid','userdevice','udUid');
        $this->createIndex('udUdid','userdevice','udUdid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('userdevice');
    }
}
