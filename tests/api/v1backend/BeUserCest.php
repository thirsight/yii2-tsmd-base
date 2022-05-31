<?php

/**
 * 后端用户登录等接口测试
 *
 * ```
 * $ cd ../yii2-app-advanced/api # (the dir with codeception.yml)
 * $ ./codecept run api -g baseBeUser -d
 * $ ./codecept run api -c codeception-sandbox.yml -g baseBeUser -d
 * $ ./codecept run api ../vendor/thirsight/yii2-tsmd-base/tests/api/v1backend/BeUserLoginCest -d
 * $ ./codecept run api ../vendor/thirsight/yii2-tsmd-base/tests/api/v1backend/BeUserLoginCest[:xxx] -d
 * ```
 */
class BeUserCest
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
     * @group baseBeUserPrepare
     */
    public function tryPrepare(ApiTester $I)
    {
        $url = $I->grabFixture('users')->wrapUrl('/user/v1backend/user/prepare', 'be');
        $I->sendGET($url);
        $I->seeResponseContains('presetRoles');
    }

    /**
     * @group baseBeUserSearch
     */
    public function trySearch(ApiTester $I)
    {
        $data = [
            'mixed' => '',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/user/v1backend/user/search', 'be');
        $I->sendGET($url, $data);
        $I->seeResponseContains('SUCCESS');
    }

    /**
     * @group baseBeUserView
     */
    public function tryView(ApiTester $I)
    {
        $url = $I->grabFixture('users')->wrapUrl('/user/v1backend/user/view', 'be');
        $I->sendGET($url, ['uid' => 1000]);
        $I->seeResponseContains('SUCCESS');
    }

    /**
     * @group baseBeUserResetPassword
     */
    public function tryResetPassword(ApiTester $I)
    {
        $data = [
            'uid' => '100003',
            'password' => '',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/user/v1backend/user/reset-password', 'be');
        $I->sendPOST($url, $data);
        $I->seeResponseContains('password');
    }
}
