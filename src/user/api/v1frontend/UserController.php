<?php

namespace tsmd\base\user\api\v1frontend;

use tsmd\base\models\TsmdResult;
use tsmd\base\user\models\UserFormChange;
use tsmd\base\user\models\UserFormUpdate;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends \tsmd\base\controllers\RestFrontendController
{
    /**
     * @var string[]
     */
    private $fields = [
        'uid', 'alpha2', 'mobile', 'email', 'username', 'realname', 'nickname',
        'role', 'status', 'extras', 'createdTime', 'updatedTime',
    ];

    /**
     * 查看用户信息
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/user/v1frontend/user/profile`
     *
     * @return array
     */
    public function actionProfile()
    {
        return TsmdResult::response($this->user->toArray($this->fields));
    }

    /**
     * 用户绑定第三方账号登录
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/user/relate-usertp`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * usertpSource | [[string]] | Yes | 第三方账号登录组件 ID，如：tpApple, tpFacebook, tpDingtalk, ...
     * usertpToken  | [[string]] | Yes | 第三方账号登录 token
     *
     * @return array
     */
    public function actionRelateUsertp()
    {
        $model = new UserFormUpdate($this->user);
        $model->load($this->getBodyParams(), '');
        $model->relateUsertp();
        return $model->hasErrors() ? TsmdResult::failed($model->firstErrors) : TsmdResult::response();
    }

    /**
     * 用户更新基本数据
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/user/update-names`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * realname | [[string]] | Yes | 真实姓名
     * nickname | [[string]] | Yes | 昵称
     * avatar   | [[string]] | Yes | 头像
     *
     * @return array
     */
    public function actionUpdateNames()
    {
        $model = new UserFormUpdate($this->user);
        $model->load($this->getBodyParams(), '');
        $model->updateNames();
        return $model->hasErrors()
            ? TsmdResult::failed($model->firstErrors)
            : TsmdResult::response($model->getUser()->toArray(['uid', 'realname', 'nickname', 'extras']));
    }

    /**
     * 用户修改密码
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/user/update-password`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * password    | [[string]] | Yes | 旧密码
     * newPassword | [[string]] | Yes | 新密码
     *
     * @return array
     */
    public function actionUpdatePassword()
    {
        $model = new UserFormUpdate($this->user);
        $model->load($this->getBodyParams(), '');
        $model->updatePassword();
        return $model->hasErrors() ? TsmdResult::failed($model->firstErrors) : TsmdResult::response();
    }

    /**
     * 用户发送手机或邮箱验证码
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/user/send-captcha`
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
        $model = new UserFormChange($this->user);
        $model->load($this->getBodyParams(), '');
        return $model->sendCaptcha($model->mobile ? 'mobile' : 'email')
            ? TsmdResult::response()
            : TsmdResult::failed($model->firstErrors);
    }

    /**
     * 用户验证手机或邮箱验证码
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/user/verify-captcha`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * mobile   | [[string]] | Yes/No | 手机号码或邮箱其中之一必填
     * email    | [[string]] | Yes/No | 邮箱或手机号码其中之一必填
     * capcode  | [[string]] | Yes    | 验证码
     *
     * @return array
     */
    public function actionVerifyCaptcha()
    {
        $model = new UserFormChange($this->user);
        $model->load($this->getBodyParams(), '');
        return $model->verifyCaptcha($model->mobile ? 'mobile' : 'email')
            ? TsmdResult::response()
            : TsmdResult::failed($model->firstErrors);
    }

    /**
     * 用户修改手机号码
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/user/change-mobile`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * mobile     | [[string]] | Yes | 新的手机号码
     * capcode    | [[string]] | Yes | 新的手机号码验证码
     * oldCapcode | [[string]] | Yes | 旧的手机号码验证码
     *
     * @return array
     */
    public function actionChangeMobile()
    {
        $model = new UserFormChange($this->user);
        $model->load($this->getBodyParams(), '');
        $model->changeMobile();
        return $model->hasErrors()
            ? TsmdResult::failed($model->firstErrors)
            : TsmdResult::response($model->getUser()->toArray(['uid', 'mobile']));
    }

    /**
     * 用户修改邮箱
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/user/change-email`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * email      | [[string]] | Yes | 新的邮箱
     * capcode    | [[string]] | Yes | 新的邮箱验证码
     * oldCapcode | [[string]] | Yes | 旧的邮箱验证码
     *
     * @return array
     */
    public function actionChangeEmail()
    {
        $model = new UserFormChange($this->user);
        $model->load($this->getBodyParams(), '');
        $model->changeEmail();
        return $model->hasErrors()
            ? TsmdResult::failed($model->firstErrors)
            : TsmdResult::response($model->getUser()->toArray(['uid', 'email']));
    }

    /**
     * 用户修改用戶名
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/user/change-username`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * username | [[string]] | Yes | 新的用户名
     *
     * @return array
     */
    public function actionChangeUsername()
    {
        $model = new UserFormChange($this->user);
        $model->load($this->getBodyParams(), '');
        $model->changeUsername();
        return $model->hasErrors()
            ? TsmdResult::failed($model->firstErrors)
            : TsmdResult::response($model->getUser()->toArray(['uid', 'username']));
    }
}
