<?php

namespace tsmd\base\captcha\api\v1tdd;

use Yii;

/**
 * 人机验证码示例
 */
class RecaptchaController extends \tsmd\base\controllers\RestTddController
{
    /**
     * <kbd>API</kbd> <kbd>GET</kbd> `/captcha/v1tdd/recaptcha/show-recaptcha`
     */
    public function actionShowRecaptcha()
    {
        if ($gRecaptchaResponse = Yii::$app->request->post('g-recaptcha-response')) {
            return Yii::$app->get('recaptcha')->validate($gRecaptchaResponse);
        }
        Yii::$app->response->format = 'html';

        $siteKey = Yii::$app->get('recaptcha')->siteKey;
        $html = <<<HTML
<html>
  <head>
    <title>reCAPTCHA demo: Simple page</title>
     <script src="//www.recaptcha.net/recaptcha/api.js" async defer></script>
     <script>
       function onSubmit(token) {
         document.getElementById("demo-form").submit();
       }
     </script>
  </head>
  <body>
    <form id='demo-form' action="?" method="POST">
      <button class="g-recaptcha" data-sitekey="{$siteKey}" data-callback='onSubmit'>Submit</button>
      <br/>
    </form>
  </body>
</html>
HTML;
        return $html;
    }
}
