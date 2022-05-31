<?php

/**
 * 前端用户登录、注册等接口测试
 *
 * ```
 * $ cd ../yii2-app-advanced/api # (the dir with codeception.yml)
 * $ ./codecept run api -g baseFeUser -d
 * $ ./codecept run api ../vendor/thirsight/yii2-tsmd-base/tests/api/v1frontend/FeUserCest -d
 * $ ./codecept run api ../vendor/thirsight/yii2-tsmd-base/tests/api/v1frontend/FeUserCest[:xxx] -d
 * ```
 */
class FeUserCest
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
     * @group baseFeUserProfile
     */
    public function tryProfile(ApiTester $I)
    {
        $url = $I->grabFixture('users')->wrapUrl('/user/v1frontend/user/profile', 'fe');
        $I->sendGET($url);
        $I->seeResponseContains('uid');
    }

    /**
     * @group baseFeUserRelateUsertp
     */
    public function tryRelateUsertp(ApiTester $I)
    {
        $data = [
            'usertpSource' => 'tpApple',
            'usertpToken' => '...',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/user/v1frontend/user/relate-usertp', 'fe');
        $I->sendPOST($url, $data);
        $I->seeResponseContains('SUCCESS');
    }

    /**
     * @group baseFeUserUpdateNames
     */
    public function tryUpdateNames(ApiTester $I)
    {
        $data = [
            'realname' => '真姓名',
            'nickname' => '小昵称',
            'avatar' => 'avatar',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/user/v1frontend/user/update-names', 'fe');
        $I->sendPOST($url, $data);
        $I->seeResponseContains('uid');
    }

    /**
     * @group baseFeUserUpdatePassword
     */
    public function tryUpdatePassword(ApiTester $I)
    {
        $data1 = [
            'password' => 'Tsmd123456',
            'newPassword' => '123456',
        ];
        $data2 = [
            'password' => '123456',
            'newPassword' => 'Tsmd123456',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/user/v1frontend/user/update-password', 'fe');
        $I->sendPOST($url, $data1);
        $I->seeResponseContains('SUCCESS');
    }

    /**
     * @group baseFeUserSendCaptcha
     * @group baseFeUserSendVerifyCaptcha
     */
    public function trySendCaptcha(ApiTester $I)
    {
        $data = [
            'email' => 'member2@example.com',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/user/v1frontend/user/send-captcha', 'fe');
        $I->sendPOST($url, $data);
        $I->seeResponseContains('SUCCESS');
    }

    /**
     * @group baseFeUserVerifyCaptcha
     * @group baseFeUserSendVerifyCaptcha
     */
    public function tryVerifyCaptcha(ApiTester $I)
    {
        $data = [
            'email' => 'member2@example.com',
            'capcode' => '999999',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/user/v1frontend/user/verify-captcha', 'fe');
        $I->sendPOST($url, $data);
        $I->seeResponseContains('SUCCESS');
    }

    /**
     * @group baseFeUserChangeMobile
     */
    public function tryChangeMobile(ApiTester $I)
    {
        $oldMobile = '11234567891';
        $newMobile = '11234567899';

        $url = $I->grabFixture('users')->wrapUrl('/user/v1frontend/user/send-captcha', 'fe');
        $I->sendPOST($url, ['mobile' => $oldMobile]);
        $I->seeResponseContains('SUCCESS');

        $url = $I->grabFixture('users')->wrapUrl('/user/v1frontend/user/send-captcha', 'fe');
        $I->sendPOST($url, ['mobile' => $newMobile]);
        $I->seeResponseContains('SUCCESS');

        $data = [
            'mobile' => $newMobile,
            'capcode' => '999999',
            'oldCapcode' => '999999',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/user/v1frontend/user/change-mobile', 'fe');
        $I->sendPOST($url, $data);
        $I->seeResponseContains($data['mobile']);
    }

    /**
     * @group baseFeUserChangeEmail
     */
    public function tryChangeEmail(ApiTester $I)
    {
        $oldEmail = 'member@example.com';
        $newEmail = 'member2@example.com';

        $url = $I->grabFixture('users')->wrapUrl('/user/v1frontend/user/send-captcha', 'fe');
        $I->sendPOST($url, ['email' => $oldEmail]);
        $I->seeResponseContains('SUCCESS');

        $url = $I->grabFixture('users')->wrapUrl('/user/v1frontend/user/send-captcha', 'fe');
        $I->sendPOST($url, ['email' => $newEmail]);
        $I->seeResponseContains('SUCCESS');

        $data = [
            'email' => $newEmail,
            'capcode' => '999999',
            'oldCapcode' => '999999',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/user/v1frontend/user/change-email', 'fe');
        $I->sendPOST($url, $data);
        $I->seeResponseContains($data['email']);
    }

    /**
     * @group baseFeUserChangeUsername
     */
    public function tryChangeUsername(ApiTester $I)
    {
        $data = [
            'username' => 'TsmdMember2',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/user/v1frontend/user/change-username', 'fe');
        $I->sendPOST($url, $data);
        $I->seeResponseContains($data['username']);
    }
}
