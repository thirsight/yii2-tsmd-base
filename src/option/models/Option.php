<?php

namespace tsmd\base\option\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%option}}".
 *
 * @property integer $optid
 * @property string $optKey
 * @property string $optGroup
 * @property string $optValue
 * @property array $optData
 * @property integer $optSort
 * @property string $createdTime
 * @property string $updatedTime
 */
class Option extends \tsmd\base\models\ArModel
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->on(static::EVENT_AFTER_FIND, [$this, 'findFormat']);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%option}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'optid'       => Yii::t('base', 'Option ID'),
            'optKey'      => Yii::t('base', 'Option Key'),
            'optGroup'    => Yii::t('base', 'Option Group'),
            'optValue'    => Yii::t('base', 'Option Value'),
            'optData'     => Yii::t('base', 'Option Data'),
            'optSort'     => Yii::t('base', 'Option Sort'),
            'createdTime' => Yii::t('base', 'Created Time'),
            'updatedTime' => Yii::t('base', 'Updated Time'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['optKey', 'required'],
            ['optKey', 'string', 'max' => 64],

            ['optGroup', 'required'],
            ['optGroup', 'string', 'max' => 32],

            ['optValue', 'default', 'value' => ''],
            ['optValue', 'string'],

            ['optData', 'default', 'value' => []],
            ['optData', function ($attribute, $params) {
                if (!is_array($this->optData)) {
                    $this->addError('OptionDataNotArray', 'Option data must be an array.');
                }
            }],

            ['optSort', 'default', 'value' => 0],
            ['optSort', 'integer'],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function saveInput()
    {
        if (is_array($this->optData)) {
            $this->optData = json_encode($this->optData) ?: $this->optData;
        }
        parent::saveInput();
    }

    /**
     * 查询后的格式化处理
     * @return $this
     */
    public function findFormat()
    {
        if (is_string($this->optData)) {
            $this->optData = $this->optData ? json_decode($this->optData, true) : [];
        }
        return $this;
    }

    /**
     * 格式化处理
     * @param array $row
     */
    public static function formatBy(array &$row)
    {
        if (is_string($row['optData'])) {
            $row['optData'] = $row['optData'] ? json_decode($row['optData'], true) : [];
        }
    }

    /**
     * 獲取 option 值
     * @param string $key
     * @return array|string
     */
    public static function getValueDataBy(string $key)
    {
        $row = static::find()->where(['optKey' => $key])->limit(1)->asArray()->cache(3600)->one();
        if ($row) {
            return $row['optData'] ? json_decode($row['optData'], true) : $row['optValue'];
        }
        return null;
    }

    /**
     * 初始化 Option
     * @param string $optGroup
     * @return array
     */
    public static function initBy(string $group, array $keys)
    {
        $opts = static::find()
            ->where(['optKey' => $keys])
            ->all();
        $opts = ArrayHelper::index($opts, 'optKey');

        $resp = ['options' => [], 'errors' => []];
        foreach ($keys as $i => $key) {
            $opt = $opts[$key] ?? new Option();
            if ($opt->isNewRecord) {
                $opt->optGroup = $group;
                $opt->optKey   = $key;
                $opt->optValue = '';
                $opt->optData  = [];
                $opt->optSort  = ++$i;
                $opt->insert(false);
            } else {
                $opt->optSort = ++$i;
                $opt->update(false, ['optSort', 'updatedTime']);
            }
            if ($opt->hasErrors()) {
                $resp['errors'][] = $opt->firstErrors;
            } else {
                $resp['options'][] = $opt->toArray();
            }
        }
        return [$resp['options'], $resp['errors']];
    }
}
