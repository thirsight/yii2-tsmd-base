<?php

namespace tsmd\base\captcha\models;

use Yii;
use yii\base\InvalidValueException;
use yii\base\Model;
use tsmd\base\captcha\components\CaptchaSenderInterface;

/**
 * 验证码生成器
 */
class CaptchaSender extends Model
{
    /**
     * 配置文件中注册组件的默认容器 ID
     */
    const DI_SENDER_SMS   = 'captchaSenderSms';
    const DI_SENDER_EMAIL = 'captchaSenderEmail';
    const DI_SENDER_IMAGE = 'captchaSenderImage';

    /**
     * @var CaptchaSenderInterface
     */
    private $sender;
    /**
     * @var string
     */
    private $target;
    /**
     * @var string
     */
    private $type;
    /**
     * @var int
     */
    private $uid;

    /**
     * @param string $diSender
     * @param array $config
     */
    public function __construct(string $diSender, $config = [])
    {
        parent::__construct($config);
        $this->setSender($diSender);
    }

    /**
     * @param string $diSender
     * @return $this
     * @throws \yii\base\InvalidConfigException
     */
    public function setSender(string $diSender)
    {
        $this->sender = Yii::$app->get($diSender);
        if (!$this->sender) {
            throw new InvalidValueException("Invalid sender `{$diSender}`.");
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target ?: '';
    }

    /**
     * @param string $target
     */
    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type ?: '';
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getUid(): int
    {
        return $this->uid ?: 0;
    }

    /**
     * @param int $uid
     */
    public function setUid(int $uid): void
    {
        $this->uid = $uid;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['target', 'required'],
            ['target', 'string', 'length' => [8, 192]],

            ['type', 'default', 'value' => Captcha::DEFAULT_TYPE],
            ['type', 'string', 'length' => [1, 16]],

            ['uid', 'default', 'value' => 0],
            ['uid', 'integer'],
        ];
    }

    /**
     * 生成并发送驗證碼
     * @return bool
     */
    public function send()
    {
        if (!$this->validate()) {
            return false;
        }
        $captcha = Captcha::find()
            ->where(['target' => $this->target, 'type' => $this->type])
            ->limit(1)
            ->one() ?: new Captcha(['target' => $this->target, 'type' => $this->type]);

        $captcha->target = $this->getTarget();
        $captcha->type = $this->getType();
        $captcha->uid = $this->getUid();
        $captcha->resetCapcode();
        $captcha->generateCapcode();

        if ($this->sender->canSend($captcha) && $this->sender->send($captcha)) {
            $captcha->sendFreq = $captcha->sendFreq + 1;
            $captcha->sendTime = time();
            $captcha->save(false);
            return true;
        }
        $this->addErrors($captcha->firstErrors);
        return false;
    }
}
