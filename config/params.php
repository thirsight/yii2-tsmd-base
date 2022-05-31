<?php

/**
 * TSMD 模块配置文件
 *
 * @link https://tsmd.thirsight.com/
 * @copyright Copyright (c) 2008 thirsight
 * @license https://tsmd.thirsight.com/license/
 */

return [
    'adminEmail' => 'admin@thirsight.com',
    'supportEmail' => 'support@thirsight.com',
    'userRememberMe' => 86400 * 30,

    'tsmd\base\controllers\RestController' => [
        'corsOrigin' => [
            'http://localhost:80',
            'http://thirsight.com',
            'https://thirsight.com',
            'http://www.thirsight.com',
            'https://www.thirsight.com',
            'http://m.thirsight.com',
            'https://m.thirsight.com',
        ]
    ],
];
