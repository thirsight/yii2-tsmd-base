<?php

/**
 * 前端用户登录、注册等接口测试
 *
 * ```
 * $ cd ../yii2-app-advanced/api # (the dir with codeception.yml)
 * $ ./codecept run api -g baseFeUserLogin -d
 * $ ./codecept run api ../vendor/thirsight/yii2-tsmd-base/tests/api/v1frontend/FeUserLoginCest -d
 * $ ./codecept run api ../vendor/thirsight/yii2-tsmd-base/tests/api/v1frontend/FeUserLoginCest[:xxx] -d
 * ```
 */
class FeUserLoginCest
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
     * @group baseFeUserLogin
     */
    public function tryLogin(ApiTester $I)
    {
        $data = [
            'loginType' => 'mobile',
            'loginName' => '11234567890',
            'password' => 'Tsmd123456',
        ];
        $I->sendPOST('/user/v1frontend/login/login', $data);
        $I->seeResponseContains('accessToken');
    }

    /**
     * @group baseFeUserLoginSendCaptcha
     * @group baseFeUserLoginResetPassword
     */
    public function trySendCaptcha(ApiTester $I)
    {
        $data = [
            'mobile' => '11234567890',
            'email' => 'tsmd@example.com',
        ];
        $I->sendPOST('/user/v1frontend/login/send-captcha', $data);
        $I->seeResponseContains('SUCCESS');
    }

    /**
     * @group baseFeUserLoginResetPassword
     */
    public function tryResetPassword(ApiTester $I)
    {
        $data = [
            'mobile' => '11234567890',
            'email' => 'tsmd@example.com',
            'password' => 'Tsmd123456',
            'capcode' => '999999',
        ];
        $I->sendPOST('/user/v1frontend/login/reset-password', $data);
        $I->seeResponseContains('SUCCESS');
    }

    /**
     * @group baseFeUserLoginSignup
     */
    public function trySendCaptchaSignup(ApiTester $I)
    {
        $I->sendPOST('/user/v1frontend/login/send-captcha', $this->getSignupData());
        $I->seeResponseContains('SUCCESS');
    }

    /**
     * @group baseFeUserLoginSignup
     * @group baseFeUserLoginSignupOne
     */
    public function trySignup(ApiTester $I)
    {
        $I->sendPOST('/user/v1frontend/login/signup', $this->getSignupData());
        $I->seeResponseContains('accessToken');
    }

    /**
     * @return array
     */
    private function getSignupData()
    {
        return [
            'mobile' => '11234567891',
            'email' => 'test@example.com',
            'username' => 'test',
            'password' => '123456',
            'capcode' => '999999',
        ];
    }
}
