<?php

namespace tsmd\base\captcha\models;

/**
 * Install User module action
 */
class Installer extends \tsmd\base\models\ModuleInstaller
{
    /**
     * @inheritdoc
     */
    public function initDocs()
    {
        // do something
    }

    /**
     * @return array|bool
     */
    public function initTable()
    {
        try {
            $mainTable = Captcha::tableName();

            $sql = <<<SQL
CREATE TABLE IF NOT EXISTS {$mainTable} (
    `id`      BIGINT(20) UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `uid`     BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
    `target`  VARCHAR(128) NOT NULL, /* 必填，无须指定 DEFAULT */
    `type`    VARCHAR(128) NOT NULL, /* 必填 */
    `captcha` VARCHAR(10) NOT NULL,  /* 必填 */
    `generateCounter` SMALLINT(6) NOT NULL DEFAULT 0,
    `generateAt`      DATETIME NOT NULL DEFAULT 0,
    `sendCounter` SMALLINT(6) NOT NULL DEFAULT 0,
    `sendAt`      DATETIME NOT NULL DEFAULT 0,
    `validateCounter` SMALLINT(6) NOT NULL DEFAULT 0,
    `validateAt`      DATETIME NOT NULL DEFAULT 0,
    `ip` VARCHAR(40) NOT NULL, /* 必填 */
    `createdAt`    DATETIME NOT NULL, /* 必填 */
    `updatedAt`    DATETIME NOT NULL, /* 必填 */
    UNIQUE KEY `targetType` (`target`, `type`),
    INDEX `uid` (`uid`),
    INDEX `target` (`target`),
    INDEX `type` (`type`),
    INDEX `ip` (`ip`),
    INDEX `createdAt` (`createdAt`),
    INDEX `updatedAt` (`updatedAt`)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE {$mainTable} AUTO_INCREMENT = 100001;
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
        /*if ($this->hasErrors()) {
            return;
        }

        $types = [
            'signUp'   => 'Sign Up',
            'login'    => 'Login',
            'validate' => 'Validate',
            'resetPassword'   => 'Reset Password',
            'changeCellphone' => 'Change Cellphone',
            'changeEmail'     => 'Change Email',
            'changeUsername'  => 'Change Username',
        ];

        $i = 1;
        foreach ($types as $type => $name) {
            $opt = Option::createBy([
                'group'    => Captcha::OG_CAPTCHA_TYPE,
                'key'      => $type,
                'value'    => $name,
                'sort'     => $i++,
            ], '', ['scenario' => Option::SCENARIO_CREATE]);

            if ($opt->hasErrors()) {
                $this->_errors[] = $opt->firstErrors;
            }
        }*/
    }

    /**
     * @inheritdoc
     */
    public function initRbac()
    {
        // do something
    }

    /**
     * @inheritdoc
     */
    public function initMenu()
    {
        // do something
    }
}
