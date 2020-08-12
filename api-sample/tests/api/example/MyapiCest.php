<?php

class MyapiCest
{
    /**
     * @group mygroup
     * @param ApiTester $I
     */
    public function tryApi(ApiTester $I)
    {
        $I->amOnPage('/site/index');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }
}