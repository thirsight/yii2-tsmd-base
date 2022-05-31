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
    /**
     * @var integer
     */
    protected $optid;

    /**
     * @return string[]
     */
    public function _fixtures()
    {
        return [
            'users' => 'tsmd\base\tests\fixtures\UsersFixture',
        ];
    }

    /**
     * @group baseBeOption
     * @group baseBeOptionInitSite
     */
    public function tryInitSite(ApiTester $I)
    {
        $url = $I->grabFixture('users')->wrapUrl('/option/v1backend/option/init-site', 'be');
        $I->sendPOST($url);
        $I->seeResponseContains('SUCCESS');
    }

    /**
     * @group baseBeOption
     * @group baseBeOptionSearch
     * @group baseBeOptionUpdate
     * @group baseBeOptionDelete
     */
    public function trySearch(ApiTester $I)
    {
        $url = $I->grabFixture('users')->wrapUrl('/option/v1backend/option/search', 'be');
        $I->sendGET($url);
        $I->seeResponseContains('SUCCESS');

        $resp = $I->grabResponse();
        $this->optid = json_decode($resp, true)['list'][0]['optid'] ?? 0;
    }

    /**
     * @group baseBeOption
     * @group baseBeOptionUpdate
     * @depends trySearch
     */
    public function tryUpdate(ApiTester $I)
    {
        $data = ['optid' => $this->optid, 'optValue' => 'test'];
        $url = $I->grabFixture('users')->wrapUrl('/option/v1backend/option/update', 'be');
        $I->sendPOST($url, $data);
        $I->seeResponseContains('SUCCESS');
    }

    /**
     * @group baseBeOptionDelete
     */
    public function tryDelete(ApiTester $I)
    {
        $url = $I->grabFixture('users')->wrapUrl('/option/v1backend/option/delete', 'be');
        $I->sendPOST($url, ['optid' => $this->optid]);
        $I->seeResponseContains('SUCCESS');
    }
}
