<?php

namespace tsmd\base\user\models;

use Yii;

/**
 * This is the model class for table "Usertp".
 *
 * @property int $tpid
 * @property int $tpUid
 * @property string $openid
 * @property string $source
 * @property array $info
 * @property int $createdTime
 * @property int $updatedTime
 */
class Usertp extends \tsmd\base\models\ArModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%usertp}}';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'tpid'   => 'tpid',
            'tpUid'  => Yii::t('base', 'User ID'),
            'openid' => Yii::t('base', 'OpenID'),
            'source' => Yii::t('base', 'Open Source'),
            'info'   => Yii::t('base', 'Open Info'),
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
            [['tpUid', 'openid', 'source'], 'required'],
            [['tpUid'], 'integer'],
            [['openid', 'source'], 'string'],

            [['info'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function saveInput()
    {
        parent::saveInput();

        if (is_array($this->info)) {
            $this->info = json_encode($this->info) ?: $this->info;
        }
    }

    /**
     * 查询后的格式化处理
     * @return $this
     */
    public function findFormat()
    {
        if (is_string($this->info)) {
            $this->info = $this->info ? json_decode($this->info, true) : [];
        }
        return $this;
    }

    /**
     * 插入或者更新 Usertp 记录
     *
     * @param integer $uid
     * @param string $source
     * @param string $sourceToken
     * @return Usertp
     */
    public static function saveUsertpBy($uid, $source, string $sourceToken)
    {
        try {
            $comp = Yii::$app->get($source);
            $comp->token = $sourceToken;
            $comp->requestUserinfo();
        } catch (\Exception $e) {
            $error = isset($comp->openid)
                ? Yii::t('base', 'Openid cannot be empty.')
                : Yii::t('base', "Invalid source ({$source}).");
            $usertp = new static();
            $usertp->addError('SourceInvalid', $error);
            return $usertp;
        }
        $usertp = Usertp::findOne(['openid' => $comp->openid])
            ?: new Usertp(['openid' => $comp->openid, 'source' => $source, 'tpUid' => $uid]);

        // openid 已綁定了其它用戶，不可更新
        if ($usertp->tpUid && $usertp->tpUid != $uid) {
            $usertp->adderror('OpenidDuplicate', "Openid `{$comp->openid}` has be taken by user `{$usertp->tpUid}`.");
            return $usertp;
        }
        $usertp->info = $comp->userinfo;
        $usertp->save();
        return $usertp;
    }
}
