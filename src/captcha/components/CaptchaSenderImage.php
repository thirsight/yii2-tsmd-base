<?php

namespace tsmd\base\captcha\components;

use tsmd\base\captcha\models\Captcha;
use yii\captcha\CaptchaAction;

/**
 * 生成图形验证码
 */
class CaptchaSenderImage extends CaptchaAction implements CaptchaSenderInterface
{
    /**
     * @param Captcha $captcha
     * @return bool
     */
    public function canSend(Captcha $captcha): bool
    {
        return true;
    }

    /**
     * @param Captcha $captcha
     * @return bool
     */
    public function send(Captcha $captcha): string
    {
        return $this->renderImage($captcha->capcode);
    }
}
