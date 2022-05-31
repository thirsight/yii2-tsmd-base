<?php

namespace tsmd\base\user\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%usertp}}`.
 */
class M210216000059CreateUsertpTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = '{{%usertp}}';
        $sql = <<<SQL
CREATE TABLE {$table} (
  `tpid`        int(11) UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `tpUid`       int(11) UNSIGNED NOT NULL,
  `openid`      varchar(128) NOT NULL,
  `source`      varchar(16) NOT NULL,
  `info`        text,
  `createdTime` int(11) NOT NULL,
  `updatedTime` int(11) NOT NULL,
  UNIQUE KEY `openid` (`openid`),
  INDEX `tpUid` (`tpUid`)
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
        $this->dropTable('usertp');
    }
}
