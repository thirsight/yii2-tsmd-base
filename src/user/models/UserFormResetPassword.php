<?php

namespace tsmd\base\user\models;

use Yii;
use yii\base\Model;

/**
 * User retrieve or modify password form
 */
class UserFormResetPassword extends Model
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
     * 通过手机、邮箱重置密码
     * @param string[] $attributes
     * @return bool|int
     */
    public function resetPassword(array $attributes = ['mobile', 'password'])
    {
        if (!$this->validate($attributes)) {
            return false;
        }
        $this->_user = $this->findUser();
        if (!$this->_user) {
            $this->addError('UserNotExists', Yii::t('base', 'The user does not exists.'));
            return false;
        }
        if ($this->_user->status !== User::STATUS_OK) {
            $this->addError('UserStatusIncorrect', Yii::t('base', 'The user status incorrect.'));
            return false;
        }
        $this->_user->setAuthKey();
        $this->_user->setPassword($this->password ?: Yii::$app->security->generateRandomString(16));
        if ($res = $this->_user->update(false)) {
            Userdev::saveBy($this->_user->uid);
        }
        return $res;
    }
}
