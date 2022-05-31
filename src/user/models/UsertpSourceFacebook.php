<?php

namespace tsmd\base\user\models;

use GuzzleHttp\Client;

/**
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
class UsertpSourceFacebook extends UsertpSource
{
    /**
     * @var string Facebook AccessToken, eg. EAADwUK3W5...
     */
    public $token = '';

    /**
     * 通过 Facebook AccessToken 获取 Facebook 用户公开资料，获取到的數據示例如下：
     *
     * ```php
     * [
     *     'id' => '105733271080753',
     *     'name' => 'Open Graph Test User',
     *     'email' => '',
     * ]
     * ```
     *
     * @return bool
     */
    public function requestUserinfo()
    {
        $resp = (new Client())->get('https://graph.facebook.com/me', [
            'timeout' => 3,
            'query' => [
                'access_token' => $this->token,
                'fields' => 'id,name,email',
            ],
        ]);
        $resp = json_decode($resp->getBody()->getContents(), true);

        $this->openid = $resp['id'];
        $this->userinfo = $resp;
        return true;
    }
}
