<?php

namespace tsmd\base\user\models;

use Yii;
use yii\helpers\ArrayHelper;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use tsmd\base\models\ExtrasTrait;

/**
 * 用户模型
 *
 * @property integer $uid AUTO_INCREMENT 100000+
 * @property string $alpha2
 * @property string $mobile
 * @property string $email
 * @property string $username
 * @property string $realname
 * @property string $nickname
 * @property integer $role
 * @property integer $status
 * @property string $authKey
 * @property string $passwordHash
 * @property string|array $extras
 * @property string $createdTime
 * @property string $updatedTime
 *
 * @property string $password write-only password
 *
 * @property Userdev[] $userdevs
 * @property Usertp[] $usertps
 */
class User extends \tsmd\base\models\ArModel implements \yii\web\IdentityInterface
{
    use ExtrasTrait;

    /**
     * @param string $field
     * @return UserExtras
     */
    public function getModelExtras(string $field)
    {
        return new UserExtras();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid'          => Yii::t('base', 'User ID'),
            'alpha2'       => Yii::t('base', 'Country'),
            'mobile'       => Yii::t('base', 'Mobile'),
            'email'        => Yii::t('base', 'Email'),
            'username'     => Yii::t('base', 'Username'),
            'realname'     => Yii::t('base', 'Realname'),
            'nickname'     => Yii::t('base', 'Nickname'),
            'role'         => Yii::t('base', 'Role'),
            'status'       => Yii::t('base', 'Status'),
            'authKey'      => Yii::t('base', 'Auth Key'),
            'passwordHash' => Yii::t('base', 'Password Hash'),
            'extras'       => Yii::t('base', 'Extras'),
            'createdTime'  => Yii::t('base', 'Created Time'),
            'updatedTime'  => Yii::t('base', 'Updated Time'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['realname', 'nickname'], 'string', 'max' => 128],
            ['extras', 'validateExtras'],
        ];
    }

    const ROLE_MEMBER = 10;
    const ROLE_ADMIN  = 90;

    /**
     * @param null $key
     * @param null $default
     * @return array|mixed
     */
    public static function presetRoles($key = null, $default = null)
    {
        $data = [
            self::ROLE_MEMBER => ['name' => Yii::t('base', 'Member')],
            self::ROLE_ADMIN  => ['name' => Yii::t('base', 'Admin')],
        ];
        return $key === null ? $data : ArrayHelper::getValue($data, $key, $default);
    }

    // 借鉴 HTTP 状态码
    // 2xx 200 OK, 201 Created(Inactive)
    // 3xx
    // 4xx 403 Forbidden, 404 Not Found(Deleted), 423 Locked
    // 5xx
    const STATUS_OK        = 200;
    const STATUS_INACTIVE  = 201;
    const STATUS_FORBIDDEN = 403;
    const STATUS_DELETED   = 404;
    const STATUS_LOCKED    = 423;

    /**
     * @param null $key
     * @param null $default
     * @return array|mixed
     */
    public static function presetStatuses($key = null, $default = null)
    {
        $data = [
            self::STATUS_OK        => ['name' => Yii::t('base', 'OK')],
            self::STATUS_INACTIVE  => ['name' => Yii::t('base', 'Inactive')],
            self::STATUS_FORBIDDEN => ['name' => Yii::t('base', 'Forbidden')],
            self::STATUS_DELETED   => ['name' => Yii::t('base', 'Deleted')],
            self::STATUS_LOCKED    => ['name' => Yii::t('base', 'Locked')],
        ];
        return $key === null ? $data : ArrayHelper::getValue($data, $key, $default);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserdevs()
    {
        return $this->hasMany(Userdev::class, ['devUid' => 'uid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsertps()
    {
        return $this->hasMany(Usertp::class, ['tpUid' => 'uid']);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->uid;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * Generates "remember me" authentication key(43)
     */
    public function setAuthKey()
    {
        $this->authKey = time() . '-' . Yii::$app->security->generateRandomString();
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function setPassword($password)
    {
        if (empty($this->authKey)) {
            throw new \yii\base\InvalidConfigException('AuthKey must be set.');
        }
        $this->passwordHash = Yii::$app->security->generatePasswordHash($password . $this->authKey);
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password . $this->authKey, $this->passwordHash);
    }

    /**
     * 生成 JWT 格式 accessToken
     *
     * @return string
     */
    public function generateAccessToken()
    {
        $uid = Yii::$app->security->encryptByKey($this->uid, Yii::$app->request->cookieValidationKey);
        $uid = base64_encode($uid);
        $udid = Yii::$app->security->encryptByKey(Yii::$app->request->headers->get('TSMD-DEVICE-UDID', ''), Yii::$app->request->cookieValidationKey);
        $udid = base64_encode($udid);
        $payload = ['uid' => $uid, 'udid' => $udid, 'time' => time()];
        return JWT::encode($payload, Yii::$app->request->cookieValidationKey, 'HS512');
    }

    /**
     * @inheritdoc
     */
    public function saveInput()
    {
        parent::saveInput();

        if (is_array($this->extras)) {
            $this->extras = json_encode($this->extras) ?: $this->extras;
        }
        if ($this->mobile == '') {
            $this->mobile = null;
        }
        if ($this->email == '') {
            $this->email = null;
        }
        if ($this->username == '') {
            $this->username = null;
        }
    }

    /**
     * 查询后的格式化处理
     * @return $this
     */
    public function findFormat()
    {
        if (is_string($this->extras)) {
            $this->extras = $this->extras ? json_decode($this->extras, true) : [];
        }
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @param int|string $uid
     * @return self|null
     */
    public static function findIdentity($uid)
    {
        if (is_numeric($uid) && ($user = static::findOne(['uid' => $uid]))) {
            $user->findFormat();
            return $user;
        }
        return null;
    }

    /**
     * 通过加密的 accessToken 查找用户
     *
     * @see generateAccessToken
     * @param string $accessToken
     * @param null $type
     * @return self|null
     */
    public static function findIdentityByAccessToken($accessToken, $type = null)
    {
        // token 解密
        try {
            $dec = (array) JWT::decode($accessToken, new Key(Yii::$app->request->cookieValidationKey, 'HS512'));
            $uid = Yii::$app->security->decryptByKey(base64_decode($dec['uid']), Yii::$app->request->cookieValidationKey);
            $udid = Yii::$app->security->decryptByKey(base64_decode($dec['udid']), Yii::$app->request->cookieValidationKey);
            $time = $dec['time'];
        } catch (\Exception $e) {
            return null;
        }
        // token 是否过期
        if (time() - $time > Yii::$app->params['userRememberMe']
            && ($udid == '' || $udid != Yii::$app->request->headers->get('TSMD-DEVICE-UDID'))) {
            return null;
        }
        return static::findIdentity($uid);
    }

    /**
     * 格式化处理
     * @param array $row
     */
    public static function formatBy(array &$row)
    {
        if (is_string($row['extras'])) {
            $row['extras'] = $row['extras'] ? json_decode($row['extras'], true) : [];
        }
    }
}
