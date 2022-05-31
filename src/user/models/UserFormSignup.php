<?php

namespace tsmd\base\user\models;

use Yii;
use yii\base\Model;

/**
 * User Signup form
 */
class UserFormSignup extends Model
{
    use UserFormTrait;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return $this->presetRules();
    }

    /**
     * @param string[] $attributes
     * @return bool
     */
    public function signup(array $attributes = ['alpha2', 'mobile', 'email', 'username'])
    {
        $attributes = array_merge($attributes, ['role', 'status', 'password', 'usertpSource', 'usertpToken']);
        if (!$this->validate($attributes)) {
            return false;
        }
        if (empty($this->mobile) && empty($this->email) && empty($this->username)) {
            $this->addError('MobileEmailUsernameEmpty', Yii::t('base', '`mobile`, `email` or `username` value cannot be empty.'));
        }
        $userExists = $this->findUser();
        if ($userExists === false) {
            return false;
        } elseif ($userExists) {
            $this->_user = $userExists;
            return true;
        }

        $user = new User();
        $user->alpha2   = $this->alpha2;
        $user->mobile   = $this->mobile ?: null;
        $user->email    = $this->email ?: null;
        $user->username = $this->username ?: null;
        $user->role     = $this->role;
        $user->status   = $this->status;
        $user->setAuthKey();
        $user->setPassword($this->password ?: Yii::$app->security->generateRandomString(16));
        if ($res = $user->insert(false)) {
            $this->_user = $user;
            $this->saveUserInfoRelated();
        }
        return $res;
    }

    /**
     * 保存用户相关信息
     */
    public function saveUserInfoRelated()
    {
        // 记录用户使用的设备
        Userdev::saveBy($this->_user->uid);

        // 绑定用户第三方账号登录
        if ($this->usertpSource && $this->usertpToken) {
            Usertp::saveUsertpBy($this->_user->uid, $this->usertpSource, $this->usertpToken);
        }
    }
}
