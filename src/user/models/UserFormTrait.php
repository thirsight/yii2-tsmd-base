<?php

namespace tsmd\base\user\models;

use Yii;
use tsmd\base\helpers\CountryHelper;
use tsmd\base\captcha\models\Captcha;
use tsmd\base\captcha\models\CaptchaSender;

/**
 * 用户表单通用代码块
 */
trait UserFormTrait
{
    /**
     * @var string 国家、地区代号
     */
    public $alpha2;
    /**
     * @var string 手机
     */
    public $mobile;
    /**
     * @var string 邮箱
     */
    public $email;
    /**
     * @var string 用户名
     */
    public $username;
    /**
     * @var integer 角色
     */
    public $role;
    /**
     * @var integer 状态
     */
    public $status;
    /**
     * @var string 验证码
     */
    public $capcode;
    /**
     * @var string 密码
     */
    public $password;

    /**
     * @var string 第三方账号登录组件 ID，如：tpApple, tpFacebook, tpDingtalk, ...
     */
    public $usertpSource;
    /**
     * @var string 第三方账号登录 token
     */
    public $usertpToken;

    /**
     * @var User
     */
    private $_user;

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->_user = $user;
    }

    /**
     * @return array
     */
    public function presetRules()
    {
        return [
            [['alpha2', 'mobile', 'email', 'username', 'role', 'status', 'capcode', 'password'], 'trim'],
            [['alpha2', 'mobile', 'email', 'username', 'role', 'status', 'capcode', 'password'], 'required'],

            ['alpha2', 'in', 'range' => array_keys(CountryHelper::$countries)],
            ['mobile', 'match', 'pattern' => '#\d{10,16}#'],

            ['email', 'email'],
            ['email', 'string', 'max' => 128],

            ['username', 'match', 'pattern' => '#\w{4,64}#'],

            ['role', 'in', 'range' => array_keys(User::presetRoles())],
            ['status', 'in', 'range' => array_keys(User::presetStatuses())],

            ['capcode', 'string', 'min' => 4, 'max' => 6],
            ['password', 'string', 'min' => 6, 'max' => 32],

            [['usertpSource', 'usertpToken'], 'string'],
        ];
    }

    /**
     * @return User|null|bool
     */
    public function findUser()
    {
        $orWhere = ['or'];
        if ($this->mobile) $orWhere[] = ['mobile' => $this->mobile];
        if ($this->email) $orWhere[] = ['email' => $this->email];
        if ($this->username) $orWhere[] = ['username' => $this->username];

        if (isset($orWhere[1])) {
            $users = User::find()->where($orWhere)->limit(count($orWhere) - 1)->all();
            if (count($users) > 1) {
                $this->addError('UserConflict', Yii::t('base', '`mobile`, `email` or `username` value conflict.'));
                return false;

            } elseif ($users) {
                return $users[0];
            }
        }
        return null;
    }

    /**
     * 发送手机验、邮箱证码
     * @param string $property eg: mobile, email
     * @param string $type
     * @return bool
     */
    public function sendCaptcha(string $property, string $type = Captcha::DEFAULT_TYPE)
    {
        if (!in_array($property, ['mobile', 'email'])) {
            $this->addError('UserFormPropertyNotExists', "Property `{$property}` does not exists.");
            return false;
        }
        $diSender = [
            'mobile' => CaptchaSender::DI_SENDER_SMS,
            'email' => CaptchaSender::DI_SENDER_EMAIL,
        ][$property];

        if ($this->hasErrors() || !$this->validate($property)) {
            return false;
        }
        $data = [
            'target' => $this->{$property},
            'type' => $type,
            'uid' => $this->_user->uid ?? 0,
        ];
        $sender = new CaptchaSender($diSender);
        if ($sender->load($data, '') && $sender->send()) {
            return true;
        }
        $this->addErrors($sender->firstErrors);
        return false;
    }

    /**
     * 验证手机、邮箱验证码
     * @param string $property eg: mobile, email
     * @param bool $verifiedReset
     * @param string $type
     * @return bool
     */
    public function verifyCaptcha(string $property, bool $verifiedReset = true, string $type = Captcha::DEFAULT_TYPE)
    {
        if (!in_array($property, ['mobile', 'email'])) {
            $this->addError('UserFormPropertyNotExists', "Property `{$property}` does not exists.");
            return false;
        }
        $attributes[] = $property;
        $attributes[] = 'capcode';

        if ($this->hasErrors() || !$this->validate($attributes)) {
            return false;
        }
        $captcha = Captcha::findTarget($this->{$property}, $type);
        if ($captcha->verifyCapcode($this->capcode, $verifiedReset)) {
            return true;
        }
        $this->addErrors($captcha->firstErrors);
        return false;
    }
}
