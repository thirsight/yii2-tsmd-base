<?php

namespace tsmd\base\user\models;

use Yii;
use yii\base\Model;
use tsmd\base\captcha\models\Captcha;

/**
 * User change form
 */
class UserFormChange extends Model
{
    use UserFormTrait;

    const SCENARIO_CHANGE = 'change';

    /**
     * @var string
     */
    public $oldMobile;
    /**
     * @var string
     */
    public $oldEmail;
    /**
     * @var string
     */
    public $oldCapcode;

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
            ['username', 'match', 'pattern' => "#^{$this->_user->username}$#", 'not' => true, 'on' => self::SCENARIO_CHANGE,
                                  'message' => Yii::t('base', 'The username is unchanged.')],

            ['mobile', 'match', 'pattern' => "#^{$this->_user->mobile}$#", 'not' => true, 'on' => self::SCENARIO_CHANGE,
                                'message' => Yii::t('base', 'The mobile is unchanged.')],

            ['email', 'match', 'pattern' => "#^{$this->_user->email}$#", 'not' => true, 'on' => self::SCENARIO_CHANGE,
                               'message' => Yii::t('base', 'The email is unchanged.')],

            ['oldCapcode', 'string', 'min' => 4, 'max' => 6],
        ]);
    }

    /**
     * 更换用户名
     * @return bool|int
     */
    public function changeUsername()
    {
        $this->scenario = self::SCENARIO_CHANGE;
        if (!$this->validate(['username'])) {
            return false;
        }
        $userExists = $this->findUser();
        if ($userExists === false) {
            return false;
        } elseif ($userExists) {
            $this->addError('NewUsernameExists', Yii::t('app', 'The new username has already been taken.'));
            return false;
        }

        $this->_user->username = $this->username;
        if ($res = $this->_user->update(false, ['username', 'updatedTime'])) {
            Userdev::saveBy($this->_user->uid);
        }
        return $res;
    }

    /**
     * 更换手机号
     * @return bool|int
     */
    public function changeMobile()
    {
        $this->scenario = self::SCENARIO_CHANGE;
        if (!$this->validate(['mobile', 'capcode', 'oldCapcode'])) {
            return false;
        }
        // 验证旧手机验证码
        if ($this->_user->mobile) {
            $oldCaptcha = Captcha::findTarget($this->_user->mobile);
            if (!$oldCaptcha->verifyCapcode($this->oldCapcode)) {
                $this->addErrors($oldCaptcha->firstErrors);
                return false;
            }
        }
        // 验证新手机验证码
        $captcha = Captcha::findTarget($this->mobile);
        if (!$captcha->verifyCapcode($this->capcode)) {
            $this->addErrors($captcha->firstErrors);
            return false;
        }
        // 判断新手机用户是否存在
        $userExists = $this->findUser();
        if ($userExists === false) {
            return false;
        } elseif ($userExists) {
            $this->addError('NewMobileExists', Yii::t('app', 'The new mobile has already been taken.'));
            return false;
        }

        $this->_user->mobile = $this->mobile;
        if ($res = $this->_user->update(false, ['mobile', 'updatedTime'])) {
            Userdev::saveBy($this->_user->uid);
        }
        return $res;
    }

    /**
     * 更换邮箱
     * @return bool|int
     */
    public function changeEmail()
    {
        $this->scenario = self::SCENARIO_CHANGE;
        if (!$this->validate(['email', 'capcode', 'oldCapcode'])) {
            return false;
        }
        // 验证旧邮箱验证码
        if ($this->_user->email) {
            $oldCaptcha = Captcha::findTarget($this->_user->email);
            if (!$oldCaptcha->verifyCapcode($this->oldCapcode)) {
                $this->addErrors($oldCaptcha->firstErrors);
                return false;
            }
        }
        // 验证新邮箱验证码
        $captcha = Captcha::findTarget($this->email);
        if (!$captcha->verifyCapcode($this->capcode)) {
            $this->addErrors($captcha->firstErrors);
            return false;
        }
        // 判断邮箱对应用户是否存在
        $userExists = $this->findUser();
        if ($userExists === false) {
            return false;
        } elseif ($userExists) {
            $this->addError('NewEmailExists', Yii::t('app', 'The new email has already been taken.'));
            return false;
        }

        $this->_user->email = $this->email;
        if ($res = $this->_user->update(false, ['email', 'updatedTime'])) {
            Userdev::saveBy($this->_user->uid);
        }
        return $res;
    }
}
