<?php

namespace tsmd\base\user\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class UserFormLogin extends Model
{
    const TYPE_MOBILE   = 'mobile';
    const TYPE_EMAIL    = 'email';
    const TYPE_USERNAME = 'username';

    const TYPE_PRESETS  = [self::TYPE_MOBILE, self::TYPE_EMAIL, self::TYPE_USERNAME];

    /**
     * @var string mobile, email, username, tpApple, tpFacebook, tpDingtalk, ...
     */
    public $loginType;
    /**
     * @var string
     */
    public $loginName;
    /**
     * @var string
     */
    public $password;
    /**
     * @var int 0|1
     */
    public $rememberMe = 1;

    /**
     * 允许登录的角色
     * @var integer[] [10, 90]
     */
    private $allowRoles;
    /**
     * 允许登录的状态
     * @var integer[] [200, 201]
     */
    private $allowStatuses;
    /**
     * @var User
     */
    private $user;

    /**
     * @param array $config
     */
    public function __construct(array $roles, array $statuses = [], $config = [])
    {
        $this->setAllowRoles($roles);
        $this->setAllowStatuses($statuses);
        parent::__construct($config);
    }

    /**
     * @param integer[] $allowRoles
     */
    public function setAllowRoles(array $allowRoles): void
    {
        $this->allowRoles = $allowRoles;
    }

    /**
     * @param integer[] $allowStatuses
     */
    public function setAllowStatuses(array $allowStatuses): void
    {
        $this->allowStatuses = $allowStatuses ?: [User::STATUS_OK];
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return void
     */
    public function setUser()
    {
        if ($this->user !== null) {
            return;
        }
        switch ($this->loginType) {
            case self::TYPE_MOBILE:
            case self::TYPE_EMAIL:
            case self::TYPE_USERNAME:
                $this->user = User::find()->where([$this->loginType => $this->loginName])->limit(1)->one();
                break;

            default:
                try {
                    $comp = Yii::$app->get($this->loginType);
                    $comp->token = $this->loginName;
                    $comp->requestUserinfo();
                } catch (\Exception $e) {
                    $this->addError('LoginTypeInvalid', Yii::t('base', "Invalid `loginType` ({$this->loginType})."));
                    break;
                }
                if (empty($comp->openid)) {
                    $this->addError('OpenidEmpty', Yii::t('base', "The OpenID cannot be empty."));
                    break;
                }
                $usertp = Usertp::findOne(['openid' => $comp->openid, 'source' => $this->loginType]);
                if (empty($usertp)) {
                    $this->addError('OpenidNotAssociated', Yii::t('base', 'The {source} has not been associated your account.', ['source' => $this->loginType]));
                    break;
                }
                $this->user = User::findOne(['uid' => $usertp->tpUid]);
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['loginName', 'trim'],
            ['loginName', 'required'],

            ['loginType', 'trim'],
            ['loginType', 'required'],
            ['loginType', function ($attribute, $params) {
                if (!in_array($this->loginType, self::TYPE_PRESETS) && !Yii::$app->get($this->loginType)) {
                    $this->addError('IncorrectLoginType', Yii::t('base', 'Incorrect login type.'));
                    return;
                }
                $this->setUser();
            }],

            ['password', 'trim'],
            ['password', 'required', 'when' => function ($model) {
                return in_array($this->loginType, self::TYPE_PRESETS);
            }],
            ['password', 'validatePassword'],

            ['rememberMe', 'default', 'value' => '1'],
            ['rememberMe', 'in', 'range' => [0, 1]],
        ];
    }

    /**
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if ($this->hasErrors()) return;

        if (empty($this->user)) {
            $this->addError('IncorrectUsernamePassword', Yii::t('base', 'Incorrect username or password.'));
            return;
        }
        if (!$this->user->validatePassword($this->password)) {
            $this->addError('IncorrectUsernamePassword', Yii::t('base', 'Incorrect username or password.'));
            return;
        }
    }

    /**
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if (!$this->validate()) {
            return false;
        }
        if (!in_array($this->user->role, $this->allowRoles)) {
            $this->addError('IncorrectRole', Yii::t('base', 'Incorrect role.'));
            return false;
        }
        if (!in_array($this->user->status, $this->allowStatuses)) {
            $this->addError('IncorrectStatus', Yii::t('base', 'Incorrect status.'));
            return false;
        }

        $duration = $this->rememberMe ? Yii::$app->params['userRememberMe'] : 0;
        $logined = Yii::$app->user->login($this->getUser(), $duration);
        if ($logined) {
            Userdev::saveBy($this->user->uid);
        }
        return $logined;
    }
}
