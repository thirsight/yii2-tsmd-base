<?php

namespace tsmd\base\option\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%option}}`.
 */
class M200601235948CreateOptionTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = '{{%option}}';
        $sql = <<<SQL
CREATE TABLE {$table} (
  `optid`       mediumint(8) UNSIGNED NOT NULL,
  `optKey`      varchar(64) NOT NULL,
  `optGroup`    varchar(32) NOT NULL,
  `optValue`    varchar(192) NOT NULL,
  `optData`     text,
  `optSort`     smallint(6) NOT NULL DEFAULT '0',
  `createdTime` int(11) NOT NULL,
  `updatedTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE {$table}
  ADD PRIMARY KEY (`optid`),
  ADD UNIQUE KEY `optKey` (`optKey`),
  ADD KEY `optGroup` (`optGroup`),
  ADD KEY `optSort` (`optSort`);

ALTER TABLE {$table}
  MODIFY `optid` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE {$table} 
  AUTO_INCREMENT = 1000;
SQL;
        $this->getDb()->createCommand($sql)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%option}}');
    }
}
