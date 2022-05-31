<?php

namespace tsmd\base\user\models;

use Yii;
use yii\base\Model;

/**
 * User update form
 */
class UserFormUpdate extends Model
{
    use UserFormTrait;

    /**
     * @var string
     */
    public $realname;
    /**
     * @var string
     */
    public $nickname;
    /**
     * @var string
     */
    public $avatar;

    /**
     * @var string
     */
    public $newPassword;

    /**
     * @param User $user
     * @param array $config
     */
    public function __construct(User $user, $config = [])
    {
        parent::__construct($config);
        $this->setUser($user);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge($this->presetRules(), [
            [['usertpSource', 'usertpToken'], 'required'],

            [['realname', 'nickname', 'avatar'], 'trim'],
            [['realname', 'nickname', 'avatar'], 'required'],
            [['realname', 'nickname', 'avatar'], 'string'],
            [['realname', 'nickname'], 'match', 'pattern' => '#^[\x{3400}-\x{9FA5}]{2,6}$#u', 'message' => '「{value}」名字应为 2~6 个中文。'],

            ['newPassword', 'trim'],
            ['newPassword', 'required'],
            ['newPassword', 'string', 'min' => 6, 'max' => '32'],
        ]);
    }

    /**
     * 用户绑定第三方账号登录
     * @return Usertp|null
     */
    public function relateUsertp()
    {
        if (!$this->validate(['usertpSource', 'usertpToken'])) {
            return null;
        }
        $usertp = Usertp::saveUsertpBy($this->_user->uid, $this->usertpSource, $this->usertpToken);
        if ($usertp->hasErrors()) {
            $this->addErrors($usertp->firstErrors);
            return null;
        }
        Userdev::saveBy($this->_user->uid);
        return $usertp;
    }

    /**
     * 用户更新基本数据
     * @param string[] $attributes
     * @return bool|int
     */
    public function updateNames(array $attributes = ['realname', 'nickname', 'avatar'])
    {
        if (!$this->validate($attributes)) {
            return false;
        }
        $udata = [];
        if ($this->realname) $udata['realname'] = $this->realname;
        if ($this->nickname) $udata['nickname'] = $this->nickname;
        if ($this->avatar) $udata['extras'] = ['avatar' => $this->avatar];

        if ($this->_user->load($udata, '') && $this->_user->validate()) {
            $this->_user->update(false, ['realname', 'nickname', 'extras', 'updatedTime']);
            $this->_user->findFormat();
        }
        if ($this->_user->hasErrors()) {
            $this->addErrors($this->_user->firstErrors);
            return false;
        }
        return true;
    }

    /**
     * 用户修改密码
     * @return bool|int
     */
    public function updatePassword()
    {
        if (!$this->validate(['password', 'newPassword'])) {
            return false;
        }
        if ($this->password == $this->newPassword) {
            $this->addError('PasswordUnchanged', Yii::t('base', 'The password is unchanged.'));
            return false;
        }
        if (!$this->_user->validatePassword($this->password)) {
            $this->addError('PasswordIncorrect', Yii::t('base', 'The password is incorrect.'));
            return false;
        }
        $this->_user->setAuthKey();
        $this->_user->setPassword($this->newPassword);
        return $this->_user->update(false);
    }
}
