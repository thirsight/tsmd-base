<?php

namespace tsmd\base\yii;

/**
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
class YiiFormatterBehavior extends \yii\base\Behavior
{
    /**
     * 过滤掉字符串中所有的空格、换行
     *
     * @param $value
     * @return mixed
     */
    public function stripBlank($value)
    {
        return preg_replace('#[\s　]+#u', '', trim($value));
    }

    /**
     * 合并字符串中多余的空格、换行
     * UTF-8编码 ASCII (194,160) 空格異常，其編碼為 0xC2 0xA0
     * 替換方法 str_ireplace(chr(0xC2).chr(0xA0), ' ', $str)
     *
     * @param $value
     * @return mixed
     */
    public function mergeBlank($value)
    {
        $value = str_ireplace(chr(0xC2).chr(0xA0), ' ', $value);
        return preg_replace('#(\s)+#', '$1', trim($value, " \t\n\r\0\x0B"));
    }

    /**
     * @param $value
     * @return string
     */
    public function asNumber($value)
    {
        $value = preg_replace('#[^\d\.]#', '', $value);
        if (is_numeric($value) && preg_match('#\.\d*0+#', $value)) {
            $value = rtrim(rtrim($value, '0'), '.');
        }
        return $value;
    }


    /**
     * 手机、邮箱混淆字符
     *
     * @param $value
     * @return string
     */
    public function obfuscation($value)
    {
        if (is_numeric($value)) {
            return substr($value, 0, 2) . '****' . substr($value, -4);

        } elseif (strpos($value, '@')) {
            return substr($value, 0, 3) . '****' . substr($value, strpos($value, '@'));

        } elseif (preg_match('#(\d+-)(\d{6,})#', $value, $m)) {
            return $m[1] . substr($m[2], 0, 2) . '****' . substr($m[2], -3);
        }
        return $value;
    }
}
