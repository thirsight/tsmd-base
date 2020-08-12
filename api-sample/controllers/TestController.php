<?php

namespace api\controllers;

use Yii;

/**
 * Test controller
 */
class TestController extends \yii\web\Controller
{
    /**
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/test/test`
     */
    public function actionTest()
    {
        echo time();
    }

    /**
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/test/phpinfo`
     */
    public function actionPhpinfo()
    {
        if (YII_ENV_DEV) {
            phpinfo();
        }
    }
}
