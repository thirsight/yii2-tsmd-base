<?php

namespace tsmd\base\captcha\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%captcha}}`.
 */
class M200601235948CreateCaptchaTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = '{{%captcha}}';
        $sql = <<<SQL
CREATE TABLE {$table} (
    `capid`         INT(11) UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `capcode`       VARCHAR(8) NOT NULL,
    `target`        VARCHAR(192) NOT NULL,
    `type`          VARCHAR(32) NOT NULL,
    `uid`           INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `generateFreq`  SMALLINT(6) UNSIGNED NOT NULL DEFAULT 0,
    `generateTime`  INT(11) NOT NULL DEFAULT 0,
    `sendFreq`      SMALLINT(6) UNSIGNED NOT NULL DEFAULT 0,
    `sendTime`      INT(11) NOT NULL DEFAULT 0,
    `verifyFreq`    SMALLINT(6) UNSIGNED NOT NULL DEFAULT 0,
    `verifyTime`    INT(11) NOT NULL DEFAULT 0,
    `verified`      TINYINT(1) NOT NULL DEFAULT 0,
    `ip`            VARCHAR(40) NOT NULL,
    `createdTime`   INT(11) NOT NULL,
    `updatedTime`   INT(11) NOT NULL,
    UNIQUE KEY `targetType` (`target`, `type`),
    INDEX `uid` (`uid`),
    INDEX `updatedTime` (`updatedTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE {$table} AUTO_INCREMENT = 100001;
SQL;
        $this->getDb()->createCommand($sql)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%captcha}}');
    }
}
