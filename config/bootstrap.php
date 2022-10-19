<?php

// Request
\Yii::$container->set('yii\web\Request', [
    'class' => 'tsmd\base\yii\YiiRequest',
    'parsers' => [
        'application/json' => 'yii\web\JsonParser',
        'text/plain' => 'yii\web\JsonParser',
    ],
    'acceptableContentTypes' => ['*/*' => []],
]);

// Formatter
\Yii::$container->set('yii\i18n\Formatter', [
    'nullDisplay' => '',
    'defaultTimeZone' => 'Asia/Shanghai',
    'dateFormat' => "php:Y-m-d",
    'timeFormat' => "php:H:i:s",
    'datetimeFormat' => "php:Y-m-d H:i:s",

    'as common' => ['class' => 'tsmd\base\yii\YiiFormatterBehavior'],
]);

/**
 * 获取 TSMD 模块的配置参数
 * @param string $basedir
 * @param string $basename
 * @return array[]
 */
function getModuleConfigs(string $basedir, string $basename) {
    $vendorPaths = [
        dirname(dirname(__DIR__)),
    ];
    if (defined('EXTRA_VENDOR_PATHS')) {
        $vendorPaths = array_merge($vendorPaths, EXTRA_VENDOR_PATHS);
    }

    static $modulePaths;
    if ($modulePaths === null) {
        foreach ($vendorPaths as $vendorPath) {
            foreach (scandir($vendorPath) as $name) {
                if (stripos($name, '.') === 0) {
                    continue;
                }
                $modulePaths[] = $vendorPath . DIRECTORY_SEPARATOR . $name;
            }
        }
    }
    $configs = [];
    foreach ($modulePaths as $modulePath) {
        $configFile = $modulePath . DIRECTORY_SEPARATOR . $basedir . DIRECTORY_SEPARATOR . $basename . '.php';
        if (is_file($configFile) && stripos($configFile, 'thirsight/yii2-tsmd-base') === false) {
            $configs[] = require $configFile;
        }
    }
    return $configs;
}
