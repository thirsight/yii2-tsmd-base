<?php

namespace tsmd\base\captcha\api\v1tdd;

use Yii;

/**
 * 验证码
 */
class CaptchaController extends \tsmd\base\controllers\RestTddController
{
    /**
     * <kbd>API</kbd> <kbd>GET</kbd> `/captcha/v1tdd/captcha/preview-email`
     */
    public function actionPreviewEmail()
    {
        Yii::$app->response->format = 'html';
        return Yii::$app->mailer->render('view-captcha', ['capcode' => '123456']);
    }
}
