<?php

namespace tsmd\base\user\models;

use yii\base\Model;

/**
 * UsersSearch represents the model behind the search form about `User`.
 */
class UserSearch extends Model
{
    /**
     * @var string
     */
    public $mixed;

    /**
     * @var int
     */
    public $uid;
    /**
     * @var string
     */
    public $mobile;
    /**
     * @var string
     */
    public $email;
    /**
     * @var string
     */
    public $username;
    /**
     * @var int
     */
    public $role;
    /**
     * @var int
     */
    public $status;

    /**
     * @var string
     */
    public $realname;
    /**
     * @var string
     */
    public $nickname;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['mixed', 'string'],

            ['uid', 'integer'],
            ['mobile', 'string'],
            ['email', 'email'],
            ['username', 'string'],

            ['role', 'in', 'range' => array_keys(User::presetRoles())],
            ['status', 'in', 'range' => array_keys(User::presetStatuses())],

            ['realname', 'string'],
            ['nickname', 'string'],
        ];
    }

    /**
     * @param array $params
     * @param bool $withCount
     * @return array
     */
    public function search(array $params, bool $withCount)
    {
        $this->load($params, '');
        if (!$this->validate()) {
            return [[], 0];
        }
        ($query = new UserQuery)
            ->select('uid, alpha2, mobile, email, username, realname, nickname, role, status, extras')
            ->addSelect('createdTime, updatedTime')
            ->andWhereIn('uid', $this->uid)
            ->andWhereIn('mobile', $this->mobile)
            ->andWhereIn('email', $this->email)
            ->andWhereIn('username', $this->username)
            ->andWhereMixed($this->mixed)
            ->andWhereIn('role', $this->role)
            ->andWhereIn('status', $this->status)
            ->andWhereRealname($this->realname)
            ->andWhereNickname($this->nickname);

        $count = $withCount ? $query->count() : 0;
        $rows  = $query->addPaging()->orderBy('uid DESC')->allWithFormat();
        return [$rows, $count];
    }
}
