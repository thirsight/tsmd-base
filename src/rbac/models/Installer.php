<?php

namespace tsmd\base\rbac\models;

use Yii;

/**
 * Install RBAC module action
 */
class Installer extends \tsmd\base\models\ModuleInstaller
{
    /**
     * @inheritdoc
     */
    public function initDocs()
    {
        // do nothing
    }

    /**
     * @return array|bool
     */
    public function initTable()
    {
        try {
            // @vendor/yiisoft/yii2/rbac/migrations/schema-mysql.sql
            $sql = <<<SQL
create table `auth_rule`
(
   `name`                 varchar(64) not null,
   `data`                 blob,
   `created_at`           integer,
   `updated_at`           integer,
    primary key (`name`)
) engine InnoDB;

create table `auth_item`
(
   `name`                 varchar(64) not null,
   `type`                 smallint not null,
   `description`          text,
   `rule_name`            varchar(64),
   `data`                 blob,
   `created_at`           integer,
   `updated_at`           integer,
   primary key (`name`),
   foreign key (`rule_name`) references `auth_rule` (`name`) on delete set null on update cascade,
   key `type` (`type`)
) engine InnoDB;

create table `auth_item_child`
(
   `parent`               varchar(64) not null,
   `child`                varchar(64) not null,
   primary key (`parent`, `child`),
   foreign key (`parent`) references `auth_item` (`name`) on delete cascade on update cascade,
   foreign key (`child`) references `auth_item` (`name`) on delete cascade on update cascade
) engine InnoDB;

create table `auth_assignment`
(
   `item_name`            varchar(64) not null,
   `user_id`              varchar(64) not null,
   `created_at`           integer,
   primary key (`item_name`, `user_id`),
   foreign key (`item_name`) references `auth_item` (`name`) on delete cascade on update cascade,
   key `auth_assignment_user_id_idx` (`user_id`)
) engine InnoDB;
SQL;
            return $this->getDb()->createCommand($sql)->execute();

        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function initOption()
    {
        // do nothing
    }

    /**
     * @inheritdoc
     */
    public function initRbac()
    {
        // do nothing
    }

    /**
     * @inheritdoc
     */
    public function initMenu()
    {
        // do nothing
    }
}
