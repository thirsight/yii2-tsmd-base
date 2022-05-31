<?php

namespace tsmd\base\user\models;

include "../components/taobao-sdk/TopSdk.php";

/**
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
class UsertpSourceDingtalk extends UsertpSource
{
    /**
     * @var string 钉钉临时授权码，如：741a011b85a830a2b4b4b793b71f022d
     */
    public $token = '';

    /**
     * @var string 綁定的時候需要此參數，以便與用戶賬戶關聯 eg. 1000-009aff4e36c5f317d4c28bb607ee024d
     */
    public $state = '';

    /**
     * @var string
     */
    public $appId;
    /**
     * @var string
     */
    public $appSecret;

    /**
     * @var string 需和创建扫码登录应用授权时填写的回调域名一致，否则会提示无权限访问？
     */
    public $redirectUri;

    /**
     * 生成钉钉掃碼登錄 URL
     *
     * @return string
     */
    public function generateQrconnectUrl()
    {
        $url = 'https://oapi.dingtalk.com/connect/qrconnect';
        $params = [
            'appid' => $this->appId,
            'response_type' => 'code',
            'scope' => 'snsapi_login',
            'state' => $this->state ? $this->state . '-' . md5($this->state . $this->appSecret) : '',
            'redirect_uri' => $this->redirectUri,
        ];
        return $url . '?' . http_build_query($params);
    }

    /**
     * 通过临时授权码获取钉钉授权用户的个人基本信息，获取到的数据示例如下：
     *
     * ```
     * [
     *   'state' => '1000',
     *   'nick' => 'Haisen',
     *   'unionid' => '5CD2LWa1BanQJgCne31I1AiEiE',
     *   'dingId' => '$:LWCP_v1:$fNyaaiMFKBNpAI21Oi21ow==',
     *   'openid' => 'aZX6vucEHiSc5Bn4iSHOB3gAiEiE',
     *   'main_org_auth_high_level' => false,
     * ]
     * ```
     *
     * 其中 `unionid` 为 openid
     *
     * @return bool
     */
    public function requestUserinfo()
    {
        if ($this->state) {
            // 驗證 state 是否正確
            list($rawState, $md5) = explode('-', $this->state);
            if (md5($rawState . $this->appSecret) != $md5) {
                return false;
            }
        }

        $clt = new \DingTalkClient(\DingTalkConstant::$CALL_TYPE_OAPI, \DingTalkConstant::$METHOD_POST , \DingTalkConstant::$FORMAT_JSON);
        $req = new \OapiSnsGetuserinfoBycodeRequest;
        $req->setTmpAuthCode($this->code);
        $resp = $clt->executeWithAccessKey($req, "https://oapi.dingtalk.com/sns/getuserinfo_bycode", $this->appId, $this->appSecret);
        if (empty($resp->user_info)) {
            return false;
        }

        $resp = array_merge((array) $resp->user_info, ['state' => $rawState ?? '']);
        $this->openid = $resp['unionid'];
        $this->userinfo = $resp;
        return true;
    }
}
