<?php

namespace apig;

/**
 * 接口测试
 *
 * ```
 * $ cd ../yii2-app-advanced/api # (the dir with codeception.yml)
 * $ ./codecept run api -g mygroup -d
 * ```
 */
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