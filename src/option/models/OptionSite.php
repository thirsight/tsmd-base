<?php

namespace tsmd\base\option\models;

/**
 * 站点预设配置
 */
class OptionSite extends \yii\base\BaseObject
{
    const OG_SITE = 'site';
    
    static $presetKeys = [
        'title',
        'keywords',
        'description',
        'maintenance',
    ];
}
