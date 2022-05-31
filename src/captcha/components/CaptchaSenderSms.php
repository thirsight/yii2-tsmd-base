<?php

namespace tsmd\base\captcha\components;

use Yii;
use yii\base\BaseObject;
use tsmd\base\captcha\models\Captcha;

/**
 * 短信发送验证码
 */
class CaptchaSenderSms extends BaseObject implements CaptchaSenderInterface
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
        // 单个 IP 验证码有效期内最多发送 60 次
        $sendFreq = Captcha::find()
            ->where(['>', 'updatedTime', time() - Captcha::VERIFY_TIME_GAP])
            ->andWhere(['ip' => Yii::$app->request->getUserIP()])
            ->sum('sendFreq');
        if ($sendFreq > 60) {
            $captcha->addError('CaptchaCodeSendFreq60', Yii::t('base', 'The captcha code can only be sent 60 times.'));
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
        // todo
        return true;
    }
}
