<?php

namespace tsmd\base\models;

use Yii;
use yii\base\InvalidValueException;

/**
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
trait ExtrasTrait
{
    /**
     * 使用此 trait 需要对此方法进行重写
     * @param string $field
     * @return Extras
     * @throws InvalidValueException
     */
    public function getModelExtras(string $field)
    {
        throw new InvalidValueException('Error model of `Extras`.');
    }

    /**
     * @param string $attribute
     * @param $params
     */
    public function validateExtras($attribute, $params)
    {
        $extrasNew = $this->{$attribute};
        if (!is_array($extrasNew)) {
            $this->addError($attribute, "`{$attribute}` must be an array.");
            return;
        }
        $extrasNew = Yii::$app->formatter->asString($extrasNew);

        $modelExtras = $this->getModelExtras($attribute);
        $modelExtras->load($extrasNew, '');
        if (!$modelExtras->validate()) {
            $this->addErrors($modelExtras->firstErrors);
            return;
        }

        // 新舊值合併
        $extrasOld = json_decode($this->getOldAttribute($attribute) ?: '[]', true);
        $extrasNew = array_filter($modelExtras->toArray(), function ($val) {
            return $val !== null;
        });
        $this->{$attribute} = array_merge($extrasOld, $extrasNew);
    }
}
