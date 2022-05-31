<?php

namespace tsmd\base\user\models;

use Yii;

/**
 * This is the model class for table "userdev".
 *
 * @property int $devid
 * @property int $devUid
 * @property string $udid
 * @property string $type
 * @property string $name
 * @property string $platform
 * @property string $browser
 * @property string $ip
 * @property string|null $rsapubkey
 * @property int $createdTime
 * @property int $updatedTime
 */
class Userdev extends \tsmd\base\models\ArModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%userdev}}';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'devid'       => 'devid',
            'devUid'      => Yii::t('base', 'User ID'),
            'udid'        => Yii::t('base', 'Device UDID'),
            'type'        => Yii::t('base', 'Device Type'),
            'name'        => Yii::t('base', 'Device Name'),
            'platform'    => Yii::t('base', 'Device Platform'),
            'browser'     => Yii::t('base', 'Device Browser'),
            'ip'          => Yii::t('base', 'Device IP'),
            'rsapubkey'   => Yii::t('base', 'Device RSA Pubkey'),
            'createdTime' => Yii::t('base', 'Created Time'),
            'updatedTime' => Yii::t('base', 'Updated Time'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['devUid', 'udid'], 'required'],
            ['devUid', 'integer'],
            ['udid', 'string', 'max' => 128],

            [['type', 'name'], 'string', 'max' => 128],
            [['rsapubkey'], 'string'],
        ];
    }

    /**
     * 插入或者更新 userdev 表记录
     *
     * @param integer $uid 用户 UID
     * @param array $data 提交需要验证的数据
     * 如：['udid' => '000000-00000-...', 'type' => 'iPhone 11 Pro', 'name' => 'My iPhone']
     * @return Userdev
     */
    public static function saveBy($uid, array $data = [])
    {
        $data = array_merge([
            'udid' => Yii::$app->request->headers->get('TSMD-DEVICE-UDID'),
            'type' => Yii::$app->request->headers->get('TSMD-DEVICE-TYPE'),
            'name' => Yii::$app->request->headers->get('TSMD-DEVICE-NAME'),
        ], array_filter($data));

        $device = self::find()->where(['udid' => $data['udid'], 'devUid' => $uid])->limit(1)->one()
            ?: new Userdev(['devUid' => $uid]);
        if ($device->load($data, '') && $device->validate()) {
            // 获取 platform, browser, ip 的值
            $browser = get_browser(null, true);

            $device->platform = $browser['platform'];
            $device->browser = $browser['browser'];
            $device->ip = Yii::$app->request->userIP;
            $device->save();

            // 更新用户使用过的设备
            $userext = [
                'uid' => $uid,
                'extras' => [
                    'isMobile' => intval($browser['ismobiledevice'] && !$browser['istablet']),
                    'isTablet' => intval($browser['istablet']),
                    'isDesktop' => intval(!$browser['ismobiledevice'] && !$browser['istablet']),
                ],
            ];
            User::saveBy($userext);

            return $device;
        }
        return $device;
    }
}
