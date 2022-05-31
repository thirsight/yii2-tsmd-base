<?php

namespace tsmd\base\captcha\components;

use tsmd\base\captcha\models\Captcha;

/**
 * 验证码发送接口
 */
interface CaptchaSenderInterface
{
    /**
     * @param Captcha $captcha
     * @return bool
     */
    public function canSend(Captcha $captcha): bool;

    /**
     * @param Captcha $captcha
     * @return string
     */
    public function send(Captcha $captcha): string;
}
