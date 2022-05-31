<?php

namespace tsmd\base\user\models;

use GuzzleHttp\Client;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;

/**
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
class UsertpSourceApple extends UsertpSource
{
    /**
     * @var string Apple identityToken JWT 格式數據，eg. eyJraWQiOi....eyJpc3MiOi....HA3Ur6UIpE...
     */
    public $token = '';

    /**
     * 通过 Apple identityToken 获取 Apple 用户资料，获取到的數據示例如下：
     *
     * ```php
     * [
     *    'iss' => 'https://appleid.apple.com',
     *    'aud' => 'com.example',
     *    'exp' => 1589881735,
     *    'iat' => 1589881135,
     *    'sub' => '001075.8f6107e8f340414fa8cafc1712345678.0925',
     *    'c_hash' => 'vbQMvk7MohsKzN8uabcdef',
     *    'email' => 'thirsight@gmail.com',
     *    'email_verified' => 'true',
     *    'auth_time' => 1589881135,
     *    'nonce_supported' => true,
     * ]
     * ```
     *
     * 其中 `sub` 為 apple user (openid)
     *
     * @return bool
     */
    public function requestUserinfo()
    {
        $keys = (new Client)->get('https://appleid.apple.com/auth/keys')->getBody();
        $keys = JWK::parseKeySet(json_decode($keys, true));
        $resp = (array) JWT::decode($this->token, $keys);

        $this->openid = $resp['sub'];
        $this->userinfo = $resp;
        return true;
    }
}
