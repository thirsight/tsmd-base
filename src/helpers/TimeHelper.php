<?php

namespace tsmd\base\helpers;

/**
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
class TimeHelper
{
    /**
     * UTC 时间戳与当地时间戳之差
     * @return int
     */
    public static function diff()
    {
        $local = time();
        $utc = strtotime(gmdate('Y-m-d H:i:s', $local));
        return $local - $utc;
    }

    /**
     * 当地时间戳转换为 UTC 时间戳
     * @param null $local
     * @return int
     */
    public static function toUtc($local = null)
    {
        if (!is_numeric($local)) {
            $local = strtotime($local) !== false ? strtotime($local)  : null;
        }
        return $local !== null ? $local - self::diff() : 0;
    }

    /**
     * UTC 时间戳转换为当地时间戳
     * @param $utc
     * @return int
     */
    public static function toLocal($utc)
    {
        if (!is_numeric($utc)) {
            $utc = strtotime($utc) !== false ? strtotime($utc) : null;
        }
        return $utc !== null? $utc + self::diff() : 0;
    }
}
