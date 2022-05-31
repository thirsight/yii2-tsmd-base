<?php

namespace tsmd\base\captcha\models;

use Yii;

/**
 * This is the model class for table "captcha".
 *
 * @property int $capid
 * @property string $capcode
 * @property string $target eg. mobile: 1234567890, email: thirsight@gmail.com, ...
 * @property string $type eg. verify, login, signup, ...
 * @property int $uid
 * @property int $generateFreq
 * @property int $generateTime
 * @property int $sendFreq
 * @property int $sendTime
 * @property int $verifyFreq
 * @property int $verifyTime
 * @property int $verified
 * @property string $ip
 * @property int $createdTime
 * @property int $updatedTime
 */
class Captcha extends \tsmd\base\models\ArModel
{
    /**
     * 默认验证类型
     */
    const DEFAULT_TYPE = 'verify';
    /**
     * 验证码验证次数上限
     */
    const VERIFY_FREQ_MAX = 5;
    /**
     * 验证码有效时间，秒
     */
    const VERIFY_TIME_GAP = 1800;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%captcha}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'capid'        => 'capid',
            'capcode'      => Yii::t('base', 'Captcha Code'),
            'target'       => Yii::t('base', 'Captcha Target'),
            'type'         => Yii::t('base', 'Captcha Type'),
            'uid'          => Yii::t('base', 'User ID'),
            'generateFreq' => Yii::t('base', 'Generate Freq'),
            'generateTime' => Yii::t('base', 'Generate Time'),
            'sendFreq'     => Yii::t('base', 'Send Freq'),
            'sendTime'     => Yii::t('base', 'Send Time'),
            'verifyFreq'   => Yii::t('base', 'Verify Freq'),
            'verifyTime'   => Yii::t('base', 'Verify Time'),
            'verified'     => Yii::t('base', 'Verified'),
            'ip'           => Yii::t('base', 'IP'),
            'createdTime'  => Yii::t('base', 'Created Time'),
            'updatedTime'  => Yii::t('base', 'Updated Time'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function saveInput()
    {
        parent::saveInput();
        $this->ip = Yii::$app->request->getUserIP();
    }

    /**
     * 验证码是否超时过期
     * @return bool
     */
    public function hasExpired()
    {
        return time() - $this->generateTime > self::VERIFY_TIME_GAP;
    }

    /**
     * 验证成功重置验证码
     * @see verifyCapcode
     * @param bool $isForce
     * @return $this
     */
    public function resetCapcode(bool $isForce = false)
    {
        if ($this->verified || $isForce) {
            $this->capcode = '';
            $this->generateFreq = 0;
            $this->generateTime = 0;
            $this->sendFreq = 0;
            $this->sendTime = 0;
            $this->verifyFreq = 0;
            $this->verifyTime = 0;
            $this->verified = 0;
        }
        return $this;
    }

    /**
     * 生成验证码
     * @return $this
     */
    public function generateCapcode()
    {
        if (empty($this->capcode) || $this->hasExpired()) {
            $this->capcode = YII_ENV_DEV ? '999999' : mt_rand(100000, 999999);
            $this->generateFreq = $this->generateFreq + 1;
            $this->generateTime = time();
        }
        return $this;
    }

    /**
     * @param string $input 用户提交的验证码
     * @param bool $verifiedReset 验证成功是否重置验证数据
     * @return boolean
     */
    public function verifyCapcode(string $input, bool $verifiedReset = true)
    {
        if (empty($this->capcode)) {
            $this->addError('CaptchaCodeEmpty', Yii::t('base', 'The captcha code is empty.'));
            return false;
        }
        if ($this->hasExpired()) {
            $this->addError('CaptchaCodeVerifyTimeGap', Yii::t('base', 'The captcha code has expired.'));
            return false;
        }
        if ($this->verifyFreq >= self::VERIFY_FREQ_MAX) {
            $this->addError('CaptchaCodeVerifyFreqMax', Yii::t('base', 'The captcha code verify times limit 5.'));
            return false;
        }
        if ($this->capcode !== $input) {
            $this->verifyFreq = $this->verifyFreq + 1;
            $this->verifyTime = time();
            $this->update(false, ['verifyFreq', 'verifyTime', 'updatedTime']);

            $this->addError('CaptchaCodeIncorrect', Yii::t('base', 'The captcha code incorrect.'));
            return false;
        }
        if (!$this->verified) {
            $this->verified = 1;
            if ($verifiedReset) {
                $this->resetCapcode();
            }
            $this->update(false);
        }
        return true;
    }

    /**
     * @param string $target
     * @param string $type
     * @return Captcha|\yii\db\ActiveRecord|null
     */
    public static function findTarget(string $target, string $type = Captcha::DEFAULT_TYPE)
    {
        return Captcha::find()
            ->where(['target' => $target, 'type' => $type])
            ->limit(1)
            ->one() ?: new Captcha();
    }
}
