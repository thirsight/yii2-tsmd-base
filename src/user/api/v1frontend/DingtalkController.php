<?php

namespace tsmd\base\user\api\v1frontend;

use Yii;
use tsmd\base\user\models\User;
use tsmd\base\user\models\Usertp;

/**
 * 用戶賬戶綁定釘釘相关接口
 */
class DingtalkController extends \yii\web\Controller
{
    /**
     * @var string[]
     */
    protected $authExcept = ['generate-login-url', 'callback-login'];

    /**
     * 生成釘釘掃碼登錄 URL
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/user/v1frontend/dingtalk/generate-login-url`
     *
     * ```
     * {
     *     "tsmdResult": "SUCCESS",
     *     ...
     *     "model": {
     *         "url": "https://oapi.dingtalk.com/connect/qrconnect?appid=xxx&response_type=code&scope=snsapi_login&state=&redirect_uri=https%3A%2F%2Ftsmd.thirsight.com%2Fuser%2Fv1frontend%2Flogin%2Flogin"
     *     }
     * }
     * ```
     *
     * @return array
     */
    public function actionGenerateLoginUrl()
    {
        $url = Yii::$app->get('tpDingtalk')->generateQrconnectUrl();
        return $url;
    }

    /**
     * 用戶賬戶綁定釘釘，以便使用釘釘登錄
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/user/v1frontend/dingtalk/callback-login`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * code     | [[string]]  | Yes | eg. a741011b85a830a2b4b4b793b71f022d
     * state    | [[integer]] | Yes | 值為 `feLogin` 跳轉至 `https://tsmd.thirsight.com/#/login`
     *
     * 响应数据示例如下：
     *
     * ```
     * {
     *     "tsmdResult": "SUCCESS",
     *     ...
     *     "model": {
     *         "uid": "1"
     *     }
     * }
     * ```
     *
     * @return string|void
     */
    public function actionCallbackLogin($code = '', $state = '')
    {
        if (empty($code) || empty($state)) {
            return $this->showMessage('err', 'Error data.');
        }
        // 跳轉
        if (in_array($state, ['beLogin'])) {
            $urls = [
                'feLogin' => "https://tsmd.thirsight.com/#/login?code={$code}&state={$state}",
            ];
            $this->redirect($urls[$state]);
            return;
        }
        // 通过 code state 获取钉钉用户基本信息
        $resp = Yii::$app->get('tpDingtalk');
        $resp->token = $code;
        $resp->state = $state;
        $resp->requestUserinfo();
        if (empty($resp)) {
            return $this->showMessage('err', '無法獲取釘釘基本信息，請重新嘗試或與管理員聯繫。');
        }
        // 通过 state, unionid 找到对应用户
        $user = User::find()
            ->leftJoin(Usertp::getRawTableName(), 'tpUid=uid')
            ->where(['or', ['uid' => $resp->userinfo['state']], ['openid' => $resp->openid]])
            ->limit(1)
            ->one();
        if (empty($user)) {
            return $this->showMessage('err', 'Error state.');
        }
        $save = Usertp::saveUsertpBy($user->uid, 'tpDingtalk', $resp->openid);
        if ($save->hasErrors()) {
            return $this->showMessage('err', current($save->firstErrors));
        }
        return $this->showMessage('suc', "釘釘 `{$resp->userinfo['nick']}` 成功綁定賬號，請前往登錄頁使用釘釘掃碼登錄。");
    }

    /**
     * @param string $style
     * @param string $msg
     * @return string
     */
    protected function showMessage($style, $msg)
    {
        $bgs = ['err' => 'indianred', 'suc' => 'forestgreen'];
        return <<<HTML
<html>
<head>
    <title>TSMD 釘釘掃碼登錄</title>    
</head>
<body style="background-color: #; text-align: center">
    <img src="http://tsmd.thirsight.com/static/tsmd-logo.png" />
    <h2>TSMD 釘釘掃碼登錄</h2>
    <div style="background-color: {$bgs[$style]}; color: white; padding: 10px">
        <h3 style="text-align: center">{$msg}</h3>
    </div>
</body>
</html>
HTML;
    }
}
