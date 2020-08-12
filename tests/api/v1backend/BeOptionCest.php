<?php

/**
 * Option 后端接口测试
 *
 * ```
 * $ cd ../yii2-app-advanced/api # (the dir with codeception.yml)
 * $ ./codecept run api -g baseBeOption -d
 * $ ./codecept run api -c codeception-sandbox.yml -g baseBeOption -d
 * $ ./codecept run api ../vendor/thirsight/yii2-tsmd-base/tests/api/v1backend/BeOptionCest -d
 * $ ./codecept run api ../vendor/thirsight/yii2-tsmd-base/tests/api/v1backend/BeOptionCest[:xxx] -d
 * ```
 */
class BeOptionCest
{
    protected $id;

    public function _fixtures()
    {
        return [
            'users' => 'tsmd\base\tests\fixtures\UsersFixture',
        ];
    }

    /**
     * @group baseBeOption
     */
    public function tryInit(ApiTester $I)
    {
        $url = $I->grabFixture('users')->wrapUrl('/option/v1backend/option/init', 'be');
        $I->sendPOST($url, ['group' => 'site']);
        $I->seeResponseContains('SUCCESS');
    }

    /**
     * @group baseBeOption
     * @depends tryInit
     */
    public function tryIndex(ApiTester $I)
    {
        $url = $I->grabFixture('users')->wrapUrl('/option/v1backend/option/index', 'be');
        $I->sendGET($url);
        $I->seeResponseContains('SUCCESS');

        $resp = $I->grabResponse();
        $this->id = json_decode($resp, true)['list']['site'][0]['id'];
    }

    /**
     * @group baseBeOption
     * @depends tryIndex
     */
    public function tryUpdate(ApiTester $I)
    {
        $data = ['id' => $this->id, 'value' => 'test'];
        $url = $I->grabFixture('users')->wrapUrl('/option/v1backend/option/update', 'be');
        $I->sendPOST($url, $data);
        $I->seeResponseContains('SUCCESS');
    }

    /**
     * @group baseBeOption
     * @depends tryUpdate
     */
    public function tryDelete(ApiTester $I)
    {
        $url = $I->grabFixture('users')->wrapUrl('/option/v1backend/option/delete', 'be');
        $I->sendPOST($url, ['id' => $this->id]);
        $I->seeResponseContains('SUCCESS');
    }
}
