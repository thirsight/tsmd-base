<?php
/**
 * @link http://tsmd.thirsight.com/
 * @copyright Copyright (c) 2019 Chaosen Zhang
 * @license http://tsmd.thirsight.com/license/
 */

namespace tsmd\base\captcha\migrations;

use yii\db\Migration;

/**
 * Add view of `captcha_group_last_hour_type`
 *
 * @see https://github.com/thirsight/tsmd-captcha
 *
 * @author Thirsight <thirsight@gmail.com>
 * @since 1.0.1
 */
class m190516_1310_captcha_group_last_hour_type extends Migration
{
    public $db = 'db';

    public $compact = true;

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        // 最近一小时验证码类型统计
        $sql = <<<SQL
CREATE VIEW captcha_group_last_hour_type AS
SELECT `type`, COUNT(*) AS `typeCounter`, SUM(generateCounter) AS `generateCounter`, SUM(sendCounter) AS `sendCounter`
FROM `captcha` WHERE `updatedAt` >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY `type`;
SQL;

            $this->getDb()->createCommand($sql)
                ->execute();

        try {
        } catch (\Exception $e) {
            // todo
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->getDb()->createCommand()
            ->dropView('captcha_group_last_hour_type')
            ->execute();
    }
}
