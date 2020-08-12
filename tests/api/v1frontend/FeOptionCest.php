<?php

/**
 * Option 前端接口测试
 *
 * ```
 * $ cd ../yii2-app-advanced/api # (the dir with codeception.yml)
 * $ ./codecept run api -g baseFeOption -d
 * $ ./codecept run api -c codeception-sandbox.yml -g baseFeOption -d
 * $ ./codecept run api ../vendor/thirsight/yii2-tsmd-base/tests/api/v1frontend/FeOptionCest -d
 * $ ./codecept run api ../vendor/thirsight/yii2-tsmd-base/tests/api/v1frontend/FeOptionCest[:xxx] -d
 * ```
 */
class FeOptionCest
{
    public function _fixtures()
    {
        return [
            'users' => 'tsmd\base\tests\fixtures\UsersFixture',
        ];
    }

    /**
     * @group baseFeOption
     */
    public function trySite(ApiTester $I)
    {
        $I->sendGET('/option/v1frontend/option/site');
        $I->seeResponseContains('SUCCESS');
    }
}
