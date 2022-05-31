<?php

namespace tsmd\base\captcha\components;

use Yii;
use yii\base\BaseObject;
use tsmd\base\captcha\models\Captcha;

/**
 * 邮箱发送验证码
 */
class CaptchaSenderEmail extends BaseObject implements CaptchaSenderInterface
{
    /**
     * @param Captcha $captcha
     * @return bool
     */
    public function canSend(Captcha $captcha): bool
    {
        // 每分钟发送一次
        if (time() - $captcha->sendTime < 60) {
            $captcha->addError('CaptchaCodeSendTimeGap', Yii::t('base', 'The captcha code can only be sent once per minute.'));
            return false;
        }
        return true;
    }

    /**
     * @param Captcha $captcha
     * @return bool
     */
    public function send(Captcha $captcha): string
    {
        return Yii::$app->mailer
            ->compose('view-captcha', $captcha->toArray(['capcode']))
            ->setTo($captcha->target)
            ->setSubject('TSMD 验证码')
            ->send();
    }
}
