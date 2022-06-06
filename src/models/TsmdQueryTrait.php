<?php

namespace tsmd\base\models;

use Yii;

/**
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
trait TsmdQueryTrait
{
    /**
     * @var \yii\db\ActiveRecord
     */
    public $modelClass;

    /**
     * @param null $db
     * @return \yii\db\Command
     */
    public function createCommand($db = null)
    {
        return parent::createCommand($db ?: $this->modelClass::getDb());
    }

    /**
     * {@inheritdoc}
     * @return array|bool
     */
    public function one($db = null)
    {
        $this->limit(1);
        return parent::one($db);
    }

    /**
     * @param null $db
     * @return array
     */
    public function allWithCount($db = null)
    {
        $count = $this->count();
        $rows = parent::all($db);
        return [$rows, $count];
    }

    /**
     * @param array|string $inclFields
     * @param array|string $exclFields
     * @return $this
     */
    public function addSelectFields($inclFields, $exclFields)
    {
        if (empty($inclFields) && empty($exclFields)) {
            return $this;
        }
        $inclFields = $inclFields && is_string($inclFields) ? explode(',', $inclFields) : (array) $inclFields;
        $exclFields = $exclFields && is_string($exclFields) ? explode(',', $exclFields) : (array) $exclFields;

        $fields = array_diff($inclFields ?: $this->modelClass::instance()->attributes(), $exclFields);
        array_walk($fields, function (&$field) {
            $field = "{$this->from[0]}.{$field}";
        });
        $this->addSelect($fields);
        return $this;
    }

    /**
     * @param string $field
     * @param string|array $value
     * @return $this
     */
    public function andWhereIn($field, $value)
    {
        $this->andWhere($this->getWhereIn($field, $value));
        return $this;
    }

    /**
     * @param string $field
     * @param string|array $value
     * @return $this
     */
    public function orWhereIn($field, $value)
    {
        $this->orWhere($this->getWhereIn($field, $value));
        return $this;
    }

    /**
     * @param string $field
     * @param string|array $value
     * @return array
     */
    public function getWhereIn($field, $value)
    {
        if ($value) {
            $value = is_string($value) ? explode(',', $value) : $value;
            $value = array_unique($value);
            return ['in', $field, $value];
        }
        return [];
    }

    /**
     * @param int|null $maxOffset
     * @param int|null $maxLimit
     * @return $this
     */
    public function addPaging($maxOffset = null, $maxLimit = null)
    {
        $pageOffset = Yii::$app->request->getPageOffset();
        $pageSize = Yii::$app->request->getPageSize();
        return $this
            ->offset(is_numeric($maxOffset) ? min($maxOffset, $pageOffset) : $pageOffset)
            ->limit(is_numeric($maxLimit) ? min($maxLimit, $pageSize) : $pageSize);
    }
}
