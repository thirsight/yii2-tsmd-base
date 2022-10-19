<?php

namespace tsmd\base\user\api\v1backend;

use Yii;
use tsmd\base\models\TsmdResult;
use tsmd\base\user\models\User;
use tsmd\base\user\models\UserFormLogin;

/**
 * LoginController implements the login action for User model.
 */
class LoginController extends \tsmd\base\controllers\RestBackendController
{
    /**
     * @var array 无须认证的接口
     */
    protected $authExcept = [
        'login',
    ];

    /**
     * @var array 无须授权的接口
     */
    protected $acfExcept = [
        'login',
    ];

    /**
     * 登录
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1backend/login/login`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * loginType  | [[string]] | Yes | mobile, email, username, tpApple, tpFacebook, tpDingtalk
     * loginName  | [[string]] | Yes | 用户名
     * password   | [[string]] | No  | 密码
     *
     * @return array|UserFormLogin
     */
    public function actionLogin()
    {
        $params = $this->getBodyParams();
        $params['loginType'] = UserFormLogin::TYPE_USERNAME;
        $model = new UserFormLogin([User::ROLE_ADMIN]);
        $model->load($params, '');

        return !$model->login()
            ? TsmdResult::failed($model->firstErrors)
            : TsmdResult::responseModel(['accessToken' => $model->getUser()->generateAccessToken()]);
    }

    /**
     * @param $route
     * @return bool
     */
    private function checkUserRbac($route)
    {
        $routes[] = $route;
        do {
            $route = preg_replace('#^(.*)/[^/]+$#', '$1', $route);
            $routes[] = '/' . ltrim($route . '/*', '/');
        } while ($route);

        $user = Yii::$app->user;
        foreach ($routes as $route) {
            if ($user->can($route)) {
                return true;
            }
        }
        return false;
    }
}
