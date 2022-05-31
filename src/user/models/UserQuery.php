<?php

namespace tsmd\base\user\models;

use tsmd\base\models\TsmdQueryTrait;

/**
 * This is the Query class for [[Option]].
 */
class UserQuery extends \yii\db\Query
{
    use TsmdQueryTrait;

    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->from(User::tableName());
        $this->modelClass = User::class;
    }

    /**
     * @param string $mixed
     * @return $this
     */
    public function andWhereMixed($mixed)
    {
        if (stripos($mixed, '*') !== false) {
            $mixed = str_ireplace('*', '%', $mixed);
            return $this->andWhere(['or',
                ['like', 'mobile', $mixed, false],
                ['like', 'email', $mixed, false],
                ['like', 'username', $mixed, false],
            ]);

        } elseif (stripos($mixed, 'UID') !== false) {
            $uid = trim(str_ireplace('UID', '', $mixed));
            return $this->andWhereIn('uid', $uid);

        } elseif (stripos($mixed, 'MOBILE') !== false) {
            $mobile = trim(str_ireplace('MOBILE', '', $mixed));
            return $this->andWhereIn('mobile', $mobile);

        } elseif (stripos($mixed, '@') !== false) {
            return $this->andWhereIn('email', $mixed);

        } elseif (is_numeric($mixed)) {
            return $this->andWhere(['uid' => $mixed]);

        } elseif ($mixed) {
            return $this->andWhereIn('username', $mixed);
        }
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function andWhereRealname($name)
    {
        if (stripos($name, '*') !== false) {
            $name = str_ireplace('*', '%', $name);
            return $this->andWhere(['like', 'realname', $name, false]);

        } elseif ($name) {
            return $this->andWhereIn('realname', $name);
        }
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function andWhereNickname($name)
    {
        if (stripos($name, '*') !== false) {
            $name = str_ireplace('*', '%', $name);
            return $this->andWhere(['like', 'nickname', $name, false]);

        } elseif ($name) {
            return $this->andWhereIn('nickname', $name);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function allWithFormat()
    {
        $rows = $this->all();
        array_walk($rows, function (&$r) {
            User::formatBy($r);
        });
        return $rows;
    }
}
