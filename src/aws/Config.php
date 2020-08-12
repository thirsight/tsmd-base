<?php

namespace tsmd\base\aws;

use yii\base\BaseObject;

/**
 * AWS common config class.
 *
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
class Config extends BaseObject
{
    /**
     * @var string eg: ap-northeast-2
     */
    public $region;

    /**
     * @var string eg: latest
     */
    public $version;

    /**
     * @var string eg: AKIAIR... (about 20 chars)
     */
    public $credentialKey;

    /**
     * @var string eg: U1mpM7... (about 40 chars)
     */
    public $credentialSecret;

    /**
     * @param array $extra
     * @return array
     */
    public function toArray($extra = [])
    {
        return \yii\helpers\ArrayHelper::merge([
            'region'   => $this->region,
            'version'  => $this->version,
            'credentials' => [
                'key'    => $this->credentialKey,
                'secret' => $this->credentialSecret,
            ]
        ], $extra);
    }
}
