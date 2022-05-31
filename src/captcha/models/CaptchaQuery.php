<?php

namespace tsmd\base\captcha\models;

use tsmd\base\models\TsmdQueryTrait;

/**
 * This is the Query class for [[Captcha]].
 */
class CaptchaQuery extends \yii\db\Query
{
    use TsmdQueryTrait;

    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->from(Captcha::tableName());
        $this->modelClass = Captcha::class;
    }
}
