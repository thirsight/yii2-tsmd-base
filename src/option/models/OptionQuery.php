<?php

namespace tsmd\base\option\models;

use tsmd\base\models\TsmdQueryTrait;

/**
 * This is the Query class for [[Option]].
 */
class OptionQuery extends \yii\db\Query
{
    use TsmdQueryTrait;

    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->from(Option::tableName());
        $this->modelClass = Option::class;
    }

    /**
     * @return array
     */
    public function allWithFormat()
    {
        $rows = $this->all();
        array_walk($rows, function (&$r) {
            Option::formatBy($r);
        });
        return $rows;
    }
}
