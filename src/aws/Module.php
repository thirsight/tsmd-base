<?php

namespace tsmd\base\aws;

class Module extends \yii\base\Module
{
    /**
     * @var string
     */
    public $controllerNamespace = 'tsmd\base\aws\api';

    /**
     * \tsmd\base\aws\api\S3Controller 控制器中的验证 Key
     *
     * @var string
     */
    public $s3ControllerSignKey;
}
