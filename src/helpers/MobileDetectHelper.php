<?php

namespace tsmd\base\helpers;

/**
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */

class MobileDetectHelper
{
    /**
     * @var MobileDetect
     */
    static $_mobileDetect;

    /**
     * @return MobileDetect
     */
    protected static function getMobileDetect()
    {
        if (!static::$_mobileDetect) {
            static::$_mobileDetect = new MobileDetect();
        }
        return static::$_mobileDetect;
    }

    /**
     * @param null $userAgent
     * @return string
     */
    public static function getDevice($userAgent = null)
    {
        if (static::getMobileDetect()->isMobile($userAgent)) {
            return 'mobile';

        } elseif (static::getMobileDetect()->isTablet($userAgent)) {
            return 'tablet';

        } else {
            return 'desktop';
        }
    }

    /**
     * @param null $userAgent
     * @return string
     */
    public static function getOs($userAgent = null)
    {
        foreach (MobileDetect::getOperatingSystems() as $os => $regex) {
            if (empty($regex)) {
                continue;
            }

            if (static::getMobileDetect()->match($regex, $userAgent)) {
                return $os;
            }
        }
        return '';
    }

    /**
     * @param null $userAgent
     * @return int|string
     */
    public static function getBrowser($userAgent = null)
    {
        echo $userAgent,'<br><br>';
        foreach (MobileDetect::getBrowsers() as $browser => $regex) {
            echo $regex, '<br>';
            if (empty($regex)) {
                continue;
            }

            if (static::getMobileDetect()->match($regex, $userAgent)) {
                return $browser;
            }
        }
        return '';
    }
}
