<?php

/**
 * 前端用户登录、注册等接口测试
 *
 * ```
 * $ cd ../yii2-app-advanced/api # (the dir with codeception.yml)
 * $ ../vendor/bin/codecept run api -g baseFeUsermbr -d
 * $ ../vendor/bin/codecept run api ../vendor/thirsight/yii2-tsmd-base/tests/api/v1frontend/FeUsermbrCest -d
 * $ ../vendor/bin/codecept run api ../vendor/thirsight/yii2-tsmd-base/tests/api/v1frontend/FeUsermbrCest[:xxx] -d
 * ```
 */
class FeUsermbrCest
{
    public function _fixtures()
    {
        return [
            'users' => 'tsmd\base\tests\fixtures\UsersFixture',
        ];
    }

    /**
     * @group baseFeUsermbr
     */
    public function tryUpdate(ApiTester $I)
    {
        $udata = ['defaultWhsAddrid' => 124335];
        $url = $I->grabFixture('users')->wrapUrl('//user/v1frontend/usermbr/update', 'fe');
        $I->sendPOST($url, $udata);
        $I->seeResponseContainsJson($udata);
    }
}
