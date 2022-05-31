<?php
/**
 * 发送邮件验证码时加载此模板生成邮件内容
 * @see \tsmd\base\captcha\components\CaptchaSenderEmail
 * @var string $capcode
 */
echo <<<HTML
<h3>TSMD 验证码：{$capcode}</h3>
<p>验证码是 30 分钟内有效。</p>
HTML;
