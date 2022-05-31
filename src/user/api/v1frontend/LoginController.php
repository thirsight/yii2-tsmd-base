<?php

namespace tsmd\base\user\api\v1frontend;

use Yii;
use tsmd\base\models\TsmdResult;
use tsmd\base\user\models\User;
use tsmd\base\user\models\UserFormLogin;
use tsmd\base\user\models\UserFormSignup;
use tsmd\base\user\models\UserFormResetPassword;

/**
 * 提供用户登录、获取用户初始数据等接口
 *
 * 登录成功会返回 `accessToken`，调用其它接口时须提交 `accessToken`
 *
 * **认证方式 Query string（所有接口通用）**
 *
 * 在 url 里增加 `?accessToken=xxxx`，xxx 须 urlencode
 *
 * **请求接口 Header（所有接口通用）**
 *
 * - `TSMD-DEVICE-UDID` 设备 ID
 * - `TSMD-DEVICE-TYPE` 设备型号，如：`iPhone 11 Pro` `SM-G9550`（即用户不可修改的数据）
 * - `TSMD-DEVICE-NAME` 设备名称，即用户可修改的数据
 *
 * **执行成功时返回的数据格式（所有接口通用）**
 *
 * `{"success":"SUCCESS"}` 或 `{"success":"..."}` 或相关的 JSON 数据
 *
 * **产生错误时返回的数据格式（所有接口通用）**
 *
 * 状态码为 2xx 返回的错误格式如下：
 *
 * ```json
 * {
 *   "error": {
 *     "username": "電郵、行動電話或密碼錯誤。"
 *   }
 * }
 * ```
 *
 * ```json
 * {
 *   "error": "Error data."
 * }
 * ```
 *
 * 状态码为 3xx 4xx 5xx 返回的错误格式如下：
 *
 * ```json
 * {
 *   "name": "Unauthorized",
 *   "message": "You are requesting with an invalid credential.",
 *   "code": 0,
 *   "status": 401,
 * }
 * ```
 */
class LoginController extends \tsmd\base\controllers\RestFrontendController
{
    /**
     * @var array 无须 accessToken 认证的接口
     */
    protected $authExcept = [
        'login',
        'send-captcha',
        'reset-password',
        'signup',
    ];

    /**
     * 手机号码、密码登入
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/login/login`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * loginType    | [[string]]  | Yes | mobile, email, username, tpApple, tpFacebook, tpDingtalk
     * loginName    | [[string]]  | Yes | 登录账号，如：手机号码、邮箱、用户名、Apple Token... 等
     * password     | [[string]]  | No  | 密码
     *
     * @return array
     */
    public function actionLogin()
    {
        $model = new UserFormLogin([User::ROLE_MEMBER]);
        $model->load($this->getBodyParams(), '');
        return !$model->login()
            ? TsmdResult::failed($model->firstErrors)
            : TsmdResult::responseModel(['accessToken' => $model->getUser()->generateAccessToken()]);
    }

    /**
     * 发送手机或邮箱验证码
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/login/send-captcha`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * mobile   | [[string]] | Yes/No | 手机号码或邮箱其中之一必填
     * email    | [[string]] | Yes/No | 邮箱或手机号码其中之一必填
     *
     * @return array
     */
    public function actionSendCaptcha()
    {
        $model = new UserFormSignup();
        $model->load($this->getBodyParams(), '');
        return $model->sendCaptcha($model->mobile ? 'mobile' : 'email')
            ? TsmdResult::response()
            : TsmdResult::failed($model->firstErrors);
    }

    /**
     * 重置密码，輸入新密码
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/login/reset-password`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * mobile   | [[string]] | Yes/No | 手机号码或邮箱其中之一必填
     * email    | [[string]] | Yes/No | 邮箱或手机号码其中之一必填
     * password | [[string]] | Yes | 密码（如果沒有提交密碼，生成一個隨機密碼）
     * capcode  | [[string]] | Yes | 手机验证码
     *
     * @return array
     */
    public function actionResetPassword()
    {
        $model = new UserFormResetPassword();
        $model->load($this->getBodyParams(), '');

        $property = $model->mobile ? 'mobile' : 'email';
        $attributes = [$property, 'password'];
        if ($model->verifyCaptcha($property) && $model->resetPassword($attributes)) {
            return TsmdResult::response();
        }
        return TsmdResult::failed($model->firstErrors);
    }

    /**
     * 簡訊註冊或登入，如果账号不存在，先註冊後登入，如果賬號已存在，則直接登入
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/login/signup`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * mobile   | [[string]] | Yes/No | 手机号码或邮箱其中之一必填
     * email    | [[string]] | Yes/No | 邮箱或手机号码其中之一必填
     * password | [[string]] | Yes | 密码（如果沒有提交密碼，生成一個隨機密碼）
     * capcode  | [[string]] | Yes | 手机验证码
     *
     * @return array
     */
    public function actionSignup()
    {
        $model = new UserFormSignup();
        $model->load($this->getBodyParams(), '');
        $model->alpha2 = 'TW';
        $model->role = User::ROLE_MEMBER;
        $model->status = User::STATUS_OK;

        if ($model->mobile) {
            $result = $model->verifyCaptcha('mobile') && $model->signup(['alpha2', 'mobile']);
        } elseif ($model->email) {
            $result = $model->verifyCaptcha('email') && $model->signup(['email']);
        } else {
            $result = $model->signup(['username']);
        }
        return $result
            ? TsmdResult::responseModel(['accessToken' => $model->getUser()->generateAccessToken()])
            : TsmdResult::failed($model->firstErrors);
    }
}
