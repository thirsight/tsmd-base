<?php

/**
 * 前端用户登录、注册等接口测试
 *
 * ```
 * $ cd ../yii2-app-advanced/api # (the dir with codeception.yml)
 * $ ../vendor/bin/codecept run api -g baseFeUser -d
 * $ ../vendor/bin/codecept run api ../vendor/thirsight/yii2-tsmd-base/tests/api/v1frontend/FeUserCest -d
 * $ ../vendor/bin/codecept run api ../vendor/thirsight/yii2-tsmd-base/tests/api/v1frontend/FeUserCest[:xxx] -d
 * ```
 */
class FeUserCest
{
    public function _fixtures()
    {
        return [
            'users' => 'tsmd\base\tests\fixtures\UsersFixture',
        ];
    }

    protected $data = [
        'site' => 'KK',
        //'cellphone' => '0999999901',
        //'password' => '123456',
        'cellphone' => '0988881022',
        'password' => '12345678',
        'captcha' => '999999',
        'fbAccessToken' => 'EAADwUK3W5L8BADDOy7wZBvQiglh24S0IsZB3jSZANxKoe7Neqv9UrQiob05mhWLxPe0ZCRZCLXUUEjxUNl0DZBANKLbk2CglO2VknIuXDZCIZAlACJrVsfoTek5yEdpIqE0G1n7jrOsOrNffZCAunoksAS8gpF53rqP4J4liRZAeRKaF0tAURhbG5gxWL5mNv3JwuWKmqGSkTqwBUr29iOj8tP',
        'appleIdentityToken' => 'eyJraWQiOiI4NkQ4OEtmIiwiYWxnIjoiUlMyNTYifQ.eyJpc3MiOiJodHRwczovL2FwcGxlaWQuYXBwbGUuY29tIiwiYXVkIjoiY29tLmp5Yi53YXJlaG91c2VjbGllbnQiLCJleHAiOjE1ODk4ODE3MzUsImlhdCI6MTU4OTg4MTEzNSwic3ViIjoiMDAxMDc1LjhmNjEwN2U4ZjM0MDQxNGZhOGNhZmMxN2IzNWM4NDc3LjA5MjUiLCJjX2hhc2giOiJ2YlFNdms3TW9oc0t6Tjh1V0F4RW13IiwiZW1haWwiOiJ0aGlyc2lnaHRAZ21haWwuY29tIiwiZW1haWxfdmVyaWZpZWQiOiJ0cnVlIiwiYXV0aF90aW1lIjoxNTg5ODgxMTM1LCJub25jZV9zdXBwb3J0ZWQiOnRydWV9.gC525RtEJE0yDxi0rO1copeZQLAf0o5GgZSZxETvCqNzTH4H4SLAwF2OLXuGFs9la1Y2LdvpNi1Yvn-8h-_9kGfhDT4z-eJkWfBZpE029n-uqEAhia6Q4CZ-XyxMBmJlX9QzBua4GtoydXM1Yt6ExFweuX-sPEWyrS0ElU_BJAuUpp_kUq8QvUcaeOvo_HAwZTDib_Wg7kmtwX0ucDt48PhsvvpHV_gY3v3SkvOUfdgYWW4aXNNOcnxQD4p6TPHNyQPF6Z-pNiJBdnWZz5tyfEcuTwgAcQnEg6T1GMFPC_-RUM9Hc8vyBdaNQUXU13LTbjGaonLrFqF9HlkHwD1Uaw',
    ];

    /**
     * @group baseFeUser
     */
    public function trySignupSendCaptcha(ApiTester $I)
    {
        $data = ['cellphone' => $this->data['cellphone'], 'gRecaptchaResponse' => 'xxx'];
        $I->sendPOST('/user/v1frontend/login/signup-send-captcha', $data);
        $I->seeResponseContains('SUCCESS');
    }

    /**
     * @group baseFeUser
     * @depends trySignupSendCaptcha
     */
    public function trySignup(ApiTester $I)
    {
        $I->sendPOST('/user/v1frontend/login/signup', $this->data);
        $I->seeResponseContains('accessToken');
    }

    /**
     * @group baseFeUser
     * @group baseFeUserLoginPwd
     */
    public function tryLoginPwd(ApiTester $I)
    {
        $cdata = [
//            'username' => $this->data['cellphone'],
//            'password' => $this->data['password'],
            'username' => $this->data['fbAccessToken'],
            'username' => $this->data['appleIdentityToken'],
            'password' => '',
        ];
        $I->sendPOST('/user/v1frontend/login/login-pwd', $cdata);
        $I->seeResponseContains('accessToken');
    }

    /**
     * @group baseFeUserResetPassword
     */
    public function trySendResetPasswordCaptcha(ApiTester $I)
    {
        $cdata = ['cellphone' => $this->data['cellphone']];
        $I->sendPOST('/user/v1frontend/login/send-reset-password-captcha', $cdata);
        $I->seeResponseContains('SUCCESS');
    }

    /**
     * @group baseFeUserResetPassword
     * @depends trySendResetPasswordCaptcha
     */
    public function tryResetPassword(ApiTester $I)
    {
        $cdata = [
            'cellphone' => $this->data['cellphone'],
            'captcha' => '999999',
            'newPassword' => 'abc456',
        ];
        $I->sendPOST('/user/v1frontend/login/reset-password', $cdata);
        $I->seeResponseContains('accessToken');
    }

    /**
     * @group baseFeUserUpdateFbid
     */
    public function tryUpdateFbid(ApiTester $I)
    {
        $url = $I->grabFixture('users')->wrapUrl('/user/v1frontend/user/update-fbid', 'fe');
        $I->sendPOST($url, ['fbAccessToken' => 'xxx']);
        $I->seeResponseContains('fbPicture');
    }

    /**
     * @group baseFeUserUpdateRealname
     */
    public function tryUpdateRealname(ApiTester $I)
    {
        $url = $I->grabFixture('users')->wrapUrl('/user/v1frontend/user/update-realname', 'fe');
        $I->sendPOST($url, ['newRealname' => '袁千惠']);
        $I->seeResponseContains('"tsmdResult":"SUCCESS"');
    }
}
