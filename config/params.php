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

    // https://unicode-table.com/en/blocks/
    // CJK Unified Ideographs Extension A
    // CJK Unified Ideographs
    // CJK Compatibility Ideographs
    // CJK Compatibility Ideographs Supplement 張芳慈
    'regUnicodeCJK' => '\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{F900}-\x{FAFF}\x{2F800}-\x{30000}',

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
