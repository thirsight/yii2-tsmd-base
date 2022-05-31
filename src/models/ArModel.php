<?php

namespace tsmd\base\models;

use Yii;

/**
 * This is the base model implements [[\yii\db\ActiveRecord]].
 *
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
abstract class ArModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->on(static::EVENT_BEFORE_VALIDATE, [$this, 'prefilter']);
        $this->on(static::EVENT_BEFORE_INSERT, [$this, 'saveInput']);
        $this->on(static::EVENT_BEFORE_UPDATE, [$this, 'saveInput']);
    }

    /**
     * beforeValidate 验证前的前置过滤器
     */
    protected function prefilter()
    {
        foreach ($this as $field => $value) {
            if (is_array($value)) {
                $this->{$field} = Yii::$app->formatter->asString($value);

            } elseif (is_string($value)) {
                $this->{$field} = Yii::$app->formatter->mergeBlank(strip_tags($value));
            }
        }
    }

    /**
     * afterValidate 验证后的后置过滤器
     */
    protected function postfilter()
    {
        // do something
    }

    /**
     * 添加、更新前的处理
     */
    protected function saveInput()
    {
        if ($this->hasAttribute('createdTime') && $this->isNewRecord) {
            $this->createdTime = $this->createdTime ?: time();
        }
        if ($this->hasAttribute('updatedTime')) {
            $this->updatedTime = time();
        }
    }

    /**
     * 查询后的处理
     */
    public function findFormat()
    {
        // do something
    }

    /**
     * @return string
     */
    public static function getDbName()
    {
        preg_match('#dbname=(\w+)#', static::getDb()->dsn, $m);
        return $m[1];
    }

    /**
     * @return string
     */
    public static function getRawTableName()
    {
        return static::getDb()->getSchema()->getRawTableName(static::tableName());
    }

    /**
     * @param $keyVal
     * @return string
     */
    public static function getTableUniqueKey($keyVal)
    {
        return strtolower(static::getDbName() . '.' . static::getRawTableName() . '.' . $keyVal);
    }

    /**
     * 创建一条记录
     * @param array $data
     * @param array $config
     * @return static
     */
    public static function createBy(array $data, array $config = [])
    {
        $model = new static($config);
        $model->load($data, '');
        $model->insert();
        return $model;
    }

    /**
     * 更新或创建一条记录
     * @param array $data
     * @param array $config
     * @return static
     */
    public static function saveBy(array $data, array $config = [])
    {
        $priKvs = [];
        foreach (static::primaryKey() as $k) {
            $priKvs[$k] = $data[$k] ?? null;
        }
        $model = static::find()->where($priKvs)->limit(1)->one() ?: new static($config);
        $model->load($data, '');
        $model->save();
        return $model;
    }
}
