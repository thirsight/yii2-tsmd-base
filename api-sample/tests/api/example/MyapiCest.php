<?php

/**
 * 接口测试
 *
 * ```
 * $ cd ../yii2-app-advanced/api # (the dir with codeception.yml)
 * $ ./codecept run api -g mygroup -d
 * ```
 */
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