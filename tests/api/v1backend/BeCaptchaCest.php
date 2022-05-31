<?php

/**
 * 后端验证码等接口测试
 *
 * ```
 * $ cd ../yii2-app-advanced/api # (the dir with codeception.yml)
 * $ ./codecept run api -g baseBeCaptcha -d
 * $ ./codecept run api ../vendor/thirsight/yii2-tsmd-base/tests/api/v1frontend/BeCaptchaCest -d
 * $ ./codecept run api ../vendor/thirsight/yii2-tsmd-base/tests/api/v1frontend/BeCaptchaCest[:xxx] -d
 * ```
 */
class BeCaptchaCest
{
    /**
     * @var integer
     */
    protected $capid;

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
     * @group baseBeCaptcha
     * @group baseBeCaptchaSearch
     * @group baseBeCaptchaDelete
     */
    public function trySearch(ApiTester $I)
    {
        $data = [
            'target' => '',
            'type' => '',
            'uid' => '',
            'ip' => '',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/captcha/v1backend/captcha/search', 'be');
        $I->sendGET($url, $data);
        $I->seeResponseContains('SUCCESS');

        $resp = $I->grabResponse();
        $this->capid = json_decode($resp, true)['list'][0]['capid'] ?? 0;
    }

    /**
     * @group baseBeCaptcha
     * @group baseBeCaptchaView
     */
    public function tryView(ApiTester $I)
    {
        $url = $I->grabFixture('users')->wrapUrl('/captcha/v1backend/captcha/view', 'be');
        $data = ['capid' => $this->capid];
        $I->sendGET($url, $data);
        $I->seeResponseContains($this->capid);
    }

    /**
     * @group baseBeCaptcha
     * @group baseBeCaptchaReset
     */
    public function tryReset(ApiTester $I)
    {
        $url = $I->grabFixture('users')->wrapUrl('/captcha/v1backend/captcha/reset', 'be');
        $data = ['capid' => $this->capid];
        $I->sendPOST($url, $data);
        $I->seeResponseContains('SUCCESS');
    }

    /**
     * @group baseBeCaptchaDelete
     */
    public function tryDelete(ApiTester $I)
    {
        $url = $I->grabFixture('users')->wrapUrl('/captcha/v1backend/captcha/delete', 'be');
        $data = ['capid' => $this->capid];
        $I->sendPOST($url, $data);
        $I->seeResponseContains('SUCCESS');
    }
}
