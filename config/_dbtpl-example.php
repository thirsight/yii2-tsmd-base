<?php

/**
 * 关于创建、授权数据库用户
 *
 * CREATE USER 'username'@'%' IDENTIFIED BY 'password';
 * SHOW GRANTS FOR 'username';
 * REVOKE ALL ON `tabename`.* FROM 'username'@'%';
 * GRANT SELECT, INSERT, UPDATE, DELETE, CREATE VIEW ON `tabename`.* TO 'username'@'%';
 */

return function($dbname, $user = 'rw') {
    $users = [
        'rw' => ['usernamerw', 'password'],
        'ro' => ['usernamero', 'password'],
    ];

    if (YII_ENV_PROD) {
        // 配置从服务器參數
        $slaveConfig = [
            'username' => $users['ro'][0],
            'password' => $users['ro'][1],
            'charset' => 'utf8mb4',
            'attributes' => [
                //PDO::ATTR_PERSISTENT => true,
                // use a smaller connection timeout
                //PDO::ATTR_TIMEOUT => 10,
            ],
            'on afterOpen' => function($event) {
                $event->sender->createCommand("SET time_zone = '+08:00'")->execute();
            },
        ];
        // 配置从服务器组
        $slaves = [
            ['dsn' => 'mysql:host=localhost;port=3306;dbname=' . $dbname],
        ];
    }

    return [
        'class' => 'yii\db\Connection',

        // 配置主服务器
        'dsn' => 'mysql:host=localhost;port=3306;dbname=' . $dbname,
        'username' => $users[$user][0],
        'password' => $users[$user][1],
        'charset' => 'utf8mb4',
        'on afterOpen' => function($event) {
            $event->sender->createCommand("SET time_zone = '+08:00'")->execute();
        },

        // 配置从服务器
        'slaveConfig' => $slaveConfig ?? [],
        // 配置从服务器组
        'slaves' => $slaves ?? [],
    ];
};
