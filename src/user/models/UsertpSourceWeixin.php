<?php

namespace tsmd\base\user\models;

use GuzzleHttp\Client;

/**
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
class UsertpSourceWeixin extends UsertpSource
{
    /**
     * @var string
     */
    public $token = '';

    /**
     * 通过微信 AccessToken 获取微信 用户公开资料，获取到的數據示例如下：
     *
     * ```php
     * [
     *     ...
     * ]
     * ```
     *
     * @return bool
     */
    public function requestUserinfo()
    {
        // todo
    }
}
