<?php

namespace tsmd\base\user\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%userdev}}`.
 */
class M200612025139CreateUserdevTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = '{{%userdev}}';
        $sql = <<<SQL
CREATE TABLE {$table} (
  `devid`     int(11) UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `devUid`    int(11) UNSIGNED NOT NULL,
  `udid`      varchar(128) NOT NULL,
  `type`      varchar(128) NOT NULL DEFAULT '',
  `name`      varchar(128) NOT NULL DEFAULT '',
  `platform`  varchar(32) NOT NULL DEFAULT '',
  `browser`   varchar(32) NOT NULL DEFAULT '',
  `ip`        varchar(64) NOT NULL,
  `rsapubkey` text,
  `createdTime` int(11) NOT NULL,
  `updatedTime` int(11) NOT NULL,
  UNIQUE KEY `udidUid` (`udid`, `devUid`),
  INDEX `devUid` (`devUid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE {$table} AUTO_INCREMENT = 100000;
SQL;
        $this->getDb()->createCommand($sql)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('userdev');
    }
}
