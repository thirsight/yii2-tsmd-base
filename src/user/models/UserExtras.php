<?php

namespace tsmd\base\user\models;

use yii\base\Model;

/**
 * user 下的 extras 字段值模型，属性不可设置默认值，否则如未提交数据会覆盖已存在的值
 */
class UserExtras extends Model
{
    /**
     * @var string 头像
     */
    public $avatar;
    /**
     * @var int 是否使用过手机，1/0
     */
    public $isMobile;
    /**
     * @var int 是否使用过平板，1/0
     */
    public $isTablet;
    /**
     * @var int 是否使用过桌面电脑/PC，1/0
     */
    public $isDesktop;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['avatar'], 'string'],

            [['isMobile', 'isTablet', 'isDesktop'], 'number'],
            [['isMobile', 'isTablet', 'isDesktop'], 'in', 'range' => [0, 1]],
        ];
    }
}
