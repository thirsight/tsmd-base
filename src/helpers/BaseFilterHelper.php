<?php

namespace tsmd\base\helpers;

/**
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
class BaseFilterHelper
{
    /**
     * @param array $row
     */
    public static function rowFilter(array &$row, array &$fields)
    {
        $row = array_intersect_key($row, array_flip($fields));
        /*array_walk($row, function (&$val) {
            $val = empty($val) || is_numeric($val) ? strval($val) : $val;
        });*/
    }

    /**
     * @param array $rows
     */
    public static function rowsFilter(array &$rows, array &$fields)
    {
        array_walk($rows, function (&$row) use ($fields) {
            static::rowFilter($row, $fields);
        });
    }
}
