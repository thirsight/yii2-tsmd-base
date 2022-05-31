<?php

namespace tsmd\base\user\api\v1backend;

use Yii;
use tsmd\base\models\TsmdResult;
use tsmd\base\user\models\User;
use tsmd\base\user\models\UserSearch;

/**
 * 提供用户管理相关接口
 *
 * Table Field | Description
 * ----------- | -----------
 * uid          | User ID
 * alpha2       | Country
 * cellphone    | Cellphone
 * email        | Email
 * authKey      | Auth Key
 * passwordHash | Password Hash
 * status       | Status
 * role         | Role
 * username     | Username
 * realname     | Real Name
 * nickname     | Nickname
 * gender       | Gender
 * slug         | Slug
 *
 * `status` value | Description
 * ---------------| -----------
 * 200            | OK
 * 201            | Inactive
 * 403            | Forbidden
 * 404            | Deleted
 * 423            | Locked
 *
 * `role` value | Description
 * -------------| -----------
 * 10           | Member
 * 90           | Admin
 */
class UserController extends \tsmd\base\controllers\RestBackendController
{
    /**
     * @var string[]
     */
    private $fields = [
        'uid', 'alpha2', 'mobile', 'email', 'username', 'realname', 'nickname',
        'role', 'status', 'extras', 'createdTime', 'updatedTime',
    ];

    /**
     * <kbd>API</kbd> <kbd>GET</kbd> `/user/v1backend/user/prepare`
     *
     * @return array
     */
    public function actionPrepare()
    {
        $roles = User::presetRoles();
        array_walk($roles, function(&$item, $key) {
            $item['value'] = $key;
        }, $roles);

        $statuses = User::presetStatuses();
        array_walk($statuses, function(&$item, $key) {
            $item['value'] = $key;
        }, $statuses);

        return TsmdResult::responseModel([
            'presetRoles' => array_values($roles),
            'presetStatuses' => array_values($statuses),
        ]);
    }

    /**
     * 用户列表查询
     *
     * <kbd>API</kbd> <kbd>GET</kbd> `/user/v1backend/user/search`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * mixed    | [[string]] | No | 可为手机号、邮箱、UID、用户名
     * uid      | [[string]] | No | UID
     * mobile   | [[string]] | No | 手机
     * email    | [[string]] | No | 邮箱
     * username | [[string]] | No | 用戶名
     * role     | [[string]] | No | 角色，参见表 "`role` value"
     * status   | [[string]] | No | 用户状态，参见表 "`status` value"
     * realname | [[string]] | No | 真实姓名
     * nickname | [[string]] | No | 昵称
     *
     * @return array
     */
    public function actionSearch()
    {
        list($rows, $count) = ($search = new UserSearch)->search($this->getQueryParams(), true);
        return $search->hasErrors()
            ? TsmdResult::failed($search->firstErrors)
            : TsmdResult::response($rows, ['count' => $count]);
    }

    /**
     * 用户查询
     *
     * <kbd>API</kbd> <kbd>GET</kbd> `/user/v1backend/user/view`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * uid      | [[string]] | No | UID
     *
     * @param integer $uid
     * @return array
     */
    public function actionView($uid)
    {
        $user = $this->findModel($uid);
        return TsmdResult::responseModel($user->toArray($this->fields));
    }

    /**
     * 重置密码，生成一个 8 位随机密码
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1backend/user/reset-password`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * uid      | [[string]] | Yes | UID
     * password | [[string]] | No  | 密码，未提交则自动生成一个密码
     *
     * @return array|User
     */
    public function actionResetPassword()
    {
        $password = $this->getBodyParams('password', Yii::$app->security->generateRandomString(16));

        $model = $this->findModel($this->getBodyParams('uid'));
        $model->setAuthKey();
        $model->setPassword((string) $password);
        $model->update(false, ['authKey', 'passwordHash']);

        return $model->hasErrors()
            ? TsmdResult::failed($model->firstErrors)
            : TsmdResult::response(['uid' => $model->uid, 'password' => $password]);
    }

    /**
     * @param integer $uid
     * @return User the loaded model
     * @throws \yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($uid)
    {
        if (is_numeric($uid) && ($model = User::findOne(['uid' => $uid])) !== null) {
            return $model;
        } else {
            throw new \yii\web\NotFoundHttpException('The requested `user` does not exist.');
        }
    }
}
