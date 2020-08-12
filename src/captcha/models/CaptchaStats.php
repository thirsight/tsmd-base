<?php

namespace tsmd\base\captcha\models;

use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * 验证码统计
 */
class CaptchaStats extends \tsmd\base\stats\BaseStats
{
    /**
     * @var string
     */
    public $type;

    /**
     * 最近一小时验证码类型统计
     *
     * @return array
     */
    public function groupLastHourType($type = null)
    {
        static $gp;
        if ($gp !== null) return $gp[$type] ?? null;

        $gp = (new Query)
            ->select('*')
            ->from('captcha_group_last_hour_type')
            ->andFilterWhere(['type' => $type])
            ->all(static::getDb());
        $gp = ArrayHelper::index($gp, 'type');
        return $gp[$type] ?? null;
    }
}
