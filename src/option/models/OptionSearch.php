<?php

namespace tsmd\base\option\models;

use yii\base\Model;

/**
 * OptionSearch represents the model behind the search form about `tsmd\base\option\models\Option`.
 */
class OptionSearch extends Model
{
    /**
     * @var integer
     */
    public $optid;
    /**
     * @var string
     */
    public $optKey;
    /**
     * @var string
     */
    public $optGroup;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['optid', 'integer'],
            ['optKey', 'string', 'max' => 64],
            ['optGroup', 'string', 'max' => 32],
        ];
    }

    /**
     * @param array $params
     * @param bool $withCount
     * @return array
     */
    public function search(array $params, $withCount = false)
    {
        $this->load($params, '');
        if (!$this->validate()) {
            return [[], 0];
        }
        $query = new OptionQuery();
        $query->andWhereIn('optid', $this->optid);
        $query->andWhereIn('optKey', $this->optKey);
        $query->andWhereIn('optGroup', $this->optGroup);

        if ($withCount) {
            $count = $query->count();
        }
        $rows = $query->addPaging()->orderBy('optid DESC')->allWithFormat();
        return [$rows, $count ?? 0];
    }
}
