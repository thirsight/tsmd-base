<?php

namespace tsmd\base\stats;

use Yii;

/**
 * 统计基础类
 *
 * @property string $dateStart
 * @property string $dateEnd
 * @property string $dateInterval
 * @property string $currDate
 * @property string $prevDate
 * @property string $nextDate
 *
 * @property string $timeStart
 * @property string $timeEnd
 *
 * @property string $monthStart
 * @property string $monthEnd
 * @property string $prevMonth
 * @property string $prevMonthEnd
 * @property string $nextMonth
 * @property string $nextMonthEnd
 */
class BaseStats extends \yii\base\BaseObject
{
    // 起始日期
    public $dateStart;
    // 结束日期
    public $dateEnd;
    // 当日
    public $currDate;
    // 上日
    public $prevDate;
    // 次日
    public $nextDate;

    // 起始、结果日期天数
    public $dateInterval;

    // 起始时间戳
    public $timeStart;
    // 结束时间戳
    public $timeEnd;

    // 当月初
    public $monthStart;
    // 当月末
    public $monthEnd;
    // 上月
    public $prevMonth;
    // 上月末
    public $prevMonthEnd;
    // 下月
    public $nextMonth;
    // 下月末
    public $nextMonthEnd;

    /**
     * @var bool
     */
    public $isSerialize = true;

    /**
     * @var bool 是否重新统计写入 StatsDaily
     */
    public $isReStats = false;

    /**
     * BaseStats constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        // 设置日期
        $this->dateStart = strtotime($this->dateStart) ? date('Y-m-d 00:00:00', strtotime($this->dateStart)) : date('Y-m-d 00:00:00', strtotime('Today'));
        $this->dateEnd   = strtotime($this->dateEnd) ? date('Y-m-d 23:59:59', strtotime($this->dateEnd)) : date('Y-m-d 23:59:59', strtotime('Today'));
        $this->currDate  = $this->dateStart;
        $this->prevDate  = date('Y-m-d 00:00:00', strtotime($this->dateStart . ' -1 Day'));
        $this->nextDate  = date('Y-m-d 00:00:00', strtotime($this->dateEnd . ' +1 Day'));

        // 起始、结束日期天数
        $this->dateInterval = bcdiv(strtotime($this->dateEnd) - strtotime($this->dateStart), 86400) + 1;

        // 设置时间戳
        $this->timeStart = strtotime($this->dateStart);
        $this->timeEnd   = strtotime($this->dateEnd . ' +1 Day');

        // 设置月初月末
        $this->monthStart = date('Y-m-01 00:00:00', strtotime($this->dateStart));
        $this->monthEnd   = date('Y-m-d 23:59:59', strtotime($this->monthStart . ' +1 Month -1 Day'));
        $this->prevMonth    = date('Y-m-01 00:00:00', strtotime($this->monthStart . ' -1 Month'));
        $this->prevMonthEnd = date('Y-m-d 23:59:59', strtotime($this->monthStart . ' -1 Day'));
        $this->nextMonth    = date('Y-m-01 00:00:00', strtotime($this->monthStart . ' +1 Month'));
        $this->nextMonthEnd = date('Y-m-d 23:59:59', strtotime($this->nextMonth . ' +1 Month -1 Day'));
    }

    /**
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->getDb();
    }

    /**
     * @param null|string $format eg. Y-m-d
     * @return array
     */
    public function getIntervalDays($format = null)
    {
        $days = [];
        for ($i = 0; $i < $this->dateInterval; $i++) {
            $days[] = $format
                ? date($format, strtotime("{$this->dateStart} + {$i} Day"))
                : strtotime("{$this->dateStart} + {$i} Day") * 1000;
        }
        return $days;
    }

    /**
     * 获取已存储至 StatsDaily 的统计结果（键值对格式）
     *
     * @param $sdTag
     * @param $sdDateField
     * @return array
     */
    public function getStatsDailyKvs($sdTag, $sdDateField)
    {
        // 2019.06.28 暫時取消統計結果儲存
        return [];

        // 未强制重新统计，获取已存储的统计结果
        if (!$this->isReStats) {
            $result = StatsDaily::batchGetKvBy($sdTag, $sdDateField, $this->dateStart, $this->dateEnd);

            // 日期天数不一致，已存储的统计结果设为空，以便进行重新统计
            //$result = count($result) != $this->dateInterval ? [] : $result;

            return $result;
        }
        return [];
    }

    /**
     * 生成用于 StatsDaily 的 tag
     *
     * @param string $sql the SQL statement to be executed, not raw SQL.
     * @return string
     */
    public function generateSdTagBy($sql)
    {
        return md5($sql);
    }

    /**
     * @param mixed $result
     * @param array $extra
     * @return array
     */
    public function serializeReturn($result, array $extra = [])
    {
        return array_merge([
            'dateStart' => $this->dateStart,
            'currDate'  => $this->currDate,
            'prevDate'  => $this->prevDate,
            'nextDate'  => $this->nextDate,
            'month'     => $this->monthStart,
            'prevMonth' => $this->prevMonth,
            'nextMonth' => $this->nextMonth,
            'result'    => $result,
        ], $extra);
    }

    /**
     * 單位轉換：千克轉為克
     *
     * @param $kg
     * @return integer
     */
    public static function kg2g($kg)
    {
        return is_numeric($kg) ? round($kg * 1000) : 0;
    }

    /**
     * 單位轉換：克轉為千克
     *
     * @param $g
     * @return float
     */
    public static function g2kg($g)
    {
        return is_numeric($g) ? bcdiv($g, 1000, 3) : 0;
    }
}
