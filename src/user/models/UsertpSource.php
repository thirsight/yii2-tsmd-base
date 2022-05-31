<?php
/**
 * @link https://tsmd.thirsight.com/
 * @copyright Copyright (c) 2020 Thirsight Software LLC
 * @license https://tsmd.thirsight.com/license/
 */

namespace tsmd\base\user\models;

use yii\base\BaseObject;

/**
 * 获取第三方用户信息的抽象类
 *
 * ```php
 * class UsertpSourceGoogle extends UsertpSource
 * {
 *     public function requestUserinfo()
 *     {
 *         $this->userinfo = ...;
 *         $this->openid = $this->userinfo['xxx'];
 *     }
 * }
 * ```
 *
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
abstract Class UsertpSource extends BaseObject
{
    /**
     * @var string 获取第三方用户信息所须的 token 或 code
     */
    public $token = '';
    /**
     * @var string 第三方用户 UID
     */
    public $openid = '';
    /**
     * @var array 第三方用户信息
     */
    public $userinfo = [];

    /**
     * 请求第三方用户信息
     *
     * @return bool
     */
    abstract public function requestUserinfo();
}
