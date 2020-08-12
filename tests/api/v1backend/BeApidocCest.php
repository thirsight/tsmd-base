<?php

/**
 * API 文档生成接口测试
 *
 * ```
 * $ cd ../yii2-app-advanced/api # (the dir with codeception.yml)
 * $ ../vendor/bin/codecept run api -g baseBeApidoc -d
 * $ ../vendor/bin/codecept run api ../vendor/thirsight/yii2-tsmd-base/tests/api/v1backend/BeApidocCest -d
 * $ ../vendor/bin/codecept run api ../vendor/thirsight/yii2-tsmd-base/tests/api/v1backend/BeApidocCest[:xxx] -d
 * ```
 */
class BeApidocCest
{
    public function _fixtures()
    {
        return [
            'users' => 'tsmd\base\tests\fixtures\UsersFixture',
        ];
    }

    /**
     * @group baseBeApidocGenerateApidoc
     */
    public function tryGenerateApidoc(ApiTester $I)
    {
        $url = $I->grabFixture('users')->wrapUrl('/apidoc/v1backend/apidoc/deploy-s3', 'be');
        $I->sendGET($url . '&isDeployS3=0');
        $I->seeResponseContains(Yii::$app->getModule('apidoc')->outputDir);
    }

    /**
     * @group baseBeApidoc
     */
    public function tryDeployS3(ApiTester $I)
    {
        $url = $I->grabFixture('users')->wrapUrl('/apidoc/v1backend/apidoc/deploy-s3', 'be');
        $I->sendGET($url . '&isDeployS3=1');
        $I->seeResponseContains('uploadTimer');
    }

    /**
     * @group baseBeApidocIndex
     */
    public function tryIndex(ApiTester $I)
    {
        $url = $I->grabFixture('users')->wrapUrl('/apidoc/v1backend/apidoc/index', 'be');
        $I->sendGET($url);
        $I->seeResponseContains('http');
    }
}
