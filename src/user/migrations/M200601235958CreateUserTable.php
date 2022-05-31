<?php

namespace tsmd\base\user\migrations;

use Yii;
use yii\db\Migration;
use yii\rbac\Item;
use tsmd\base\user\models\User;

/**
 * Handles the creation of table `{{%user}}`.
 */
class M200601235958CreateUserTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = '{{%user}}';
        $sql = <<<SQL
CREATE TABLE {$table} (
    `uid`      int(11) UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `alpha2`   char(2) NOT NULL DEFAULT '',
    `mobile`   varchar(16),
    `email`    varchar(128),
    `username` varchar(64),
    `realname` varchar(128) NOT NULL DEFAULT '',
    `nickname` varchar(128) NOT NULL DEFAULT '',
    `role`     tinyint(2) NOT NULL DEFAULT 0,
    `status`   smallint(3) NOT NULL DEFAULT 0,
    `authKey`  varchar(64) NOT NULL,
    `passwordHash` varchar(192) NOT NULL,
    `extras`       text,
    `createdTime`  int(11) NOT NULL,
    `updatedTime`  int(11) NOT NULL,
    UNIQUE KEY `mobile` (`mobile`),
    UNIQUE KEY `email` (`email`),
    UNIQUE KEY `username` (`username`),
    INDEX `createdTime` (`createdTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE {$table} AUTO_INCREMENT = 100000;
SQL;
        $this->getDb()->createCommand($sql)->execute();

        $this->initUsers();
        $this->initRbac();
    }

    /**
     * 初始用户（管理员与普通用户）
     */
    private function initUsers()
    {
        $user = new User();
        $user->uid =  1;
        $user->mobile = '11234567890';
        $user->email = 'tsmd@example.com';
        $user->username = 'TsmdAdmin';
        $user->status = User::STATUS_OK;
        $user->role = User::ROLE_ADMIN;
        $user->setAuthKey();
        $user->setPassword('Tsmd123456');
        $user->insert(false);

        $user = new User();
        $user->uid =  1000;
        $user->mobile = '11234567891';
        $user->email = 'member@example.com';
        $user->username = 'TsmdMember';
        $user->status = User::STATUS_OK;
        $user->role = User::ROLE_MEMBER;
        $user->setAuthKey();
        $user->setPassword('Tsmd123456');
        $user->insert(false);
    }

    /**
     * 初始管理员权限
     */
    private function initRbac()
    {
        $item = new Item(['type' => Item::TYPE_PERMISSION, 'name' => '/*']);
        Yii::$app->authManager->remove($item);
        Yii::$app->authManager->add($item);
        Yii::$app->authManager->assign($item, 1);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
