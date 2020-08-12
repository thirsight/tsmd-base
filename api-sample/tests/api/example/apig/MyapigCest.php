<?php

namespace apig;

class MyapigCest
{
    /**
     * @group mygroup
     * @param \ApiTester $I
     */
    public function tryApig(\ApiTester $I)
    {
        $I->amOnPage('/site/index');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }
}