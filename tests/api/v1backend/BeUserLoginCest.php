<?php

/**
 * 后端用户登录等接口测试
 *
 * ```
 * $ cd ../yii2-app-advanced/api # (the dir with codeception.yml)
 * $ ./codecept run api -g baseBeUserLogin -d
 * $ ./codecept run api -c codeception-sandbox.yml -g baseBeUserLogin -d
 * $ ./codecept run api ../vendor/thirsight/yii2-tsmd-base/tests/api/v1backend/BeUserLoginCest -d
 * $ ./codecept run api ../vendor/thirsight/yii2-tsmd-base/tests/api/v1backend/BeUserLoginCest[:xxx] -d
 * ```
 */
class BeUserLoginCest
{
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
     * @group baseBeUserLogin
     */
    public function tryLogin(ApiTester $I)
    {
        $data = [
            'loginName' => 'TsmdAdmin',
            'password' => 'Tsmd123456',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/user/v1backend/login/login', 'be');
        $I->sendPOST($url, $data);
        $I->seeResponseContains('accessToken');
    }
}
