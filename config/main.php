<?php

/**
 * TSMD 模块配置文件
 *
 * 须将此配置文件导入到 /api/web/index.php, /api/config/test.php 文件的 $config 参数
 *
 * @link https://tsmd.thirsight.com/
 * @copyright Copyright (c) 2008 thirsight
 * @license https://tsmd.thirsight.com/license/
 */

$dbTpl = require '_dbtpl.php';
$baseConfig = [
    'id' => 'thirsight-api',
    'language' => 'zh-CN',
    'timeZone' => 'Asia/Shanghai',
    'params' => require 'params.php',

    // 设置路径别名，以便 Yii::autoload() 可自动加载 TSMD 自定的类
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',

        // 模块 yii2-tsmd-base
        '@tsmd/base' => __DIR__ . '/../src',
        '@tsmd/base/tests' => __DIR__ . '/../tests',
    ],

    // 设置 Yii 初始化时运行的组件
    'bootstrap' => [
        'log'
    ],

    // 设置控制器
    'controllerMap' => [
        'site' => 'tsmd\base\controllers\SiteController',
    ],

    // 设置 TSMD 模块
    'modules' => [
        'option' => [
            'class' => 'tsmd\base\option\Module',
        ],
        'user' => [
            'class' => 'tsmd\base\user\Module',
        ],
        'rbac' => [
            'class' => 'tsmd\base\rbac\Module',
        ],
        'captcha' => [
            'class' => 'tsmd\base\captcha\Module',
        ],
    ],

    // 组件设置
    'components' => [
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error'],
                ]
            ],
        ],
        // 数据库配置
        'db' => $dbTpl('tsmddb'),

        // 第三方登录：AppleID 登录
        'tpApple' => [
            'class' => 'tsmd\base\user\models\UsertpSourceApple',
        ],
        // 第三方登录：Facebook 登录
        'tpFacebook' => [
            'class' => 'tsmd\base\user\models\UsertpSourceFacebook',
        ],
        // 第三方登录：钉钉扫码登录
        'tpDingtalk' => [
            'class' => 'tsmd\base\user\models\UsertpSourceDingtalk',
            'appId' => '[[appId]]',
            'appSecret' => '[[appSecret]]',
            'redirectUri' => 'https://api.tsmd.thirsight.com/user/v1frontend/dingtalk/callback-login',
        ],

        // 验证码配置
        'captchaSenderSms' => [
            'class' => 'tsmd\base\captcha\components\CaptchaSenderSms',
        ],
        'captchaSenderEmail' => [
            'class' => 'tsmd\base\captcha\components\CaptchaSenderEmail',
        ],
        'captchaSenderImage' => [
            'class' => 'tsmd\base\captcha\components\CaptchaSenderImage',
        ],
        'recaptcha' => [
            'class' => 'tsmd\base\captcha\components\Recaptcha',
            'siteKey' => '[[siteKey]]',
            'secretKey' => '[[secretKey]]',
        ],

        // 设置核心组件参数 \yii\base\Application::coreComponents
        'i18n' => [
            'translations' => [
                'base' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@tsmd/base/messages',
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // 解决跨域请求预发送问题
                'OPTIONS <controller:.*>' => '/site/index',
            ],
        ],
        'security' => [
            'class' => 'yii\base\Security',
            'passwordHashCost' => 8,
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '[[cookieValidationKey]]',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'cache' => '\yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'tsmd\base\user\models\User',
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'htmlLayout' => false,
            'textLayout' => false,
            'viewPath' => '@tsmd/base/mail',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.live.com',
                'username' => '@enoreplyxample.com',
                'password' => '[[password]]',
                'port' => '587',
                'encryption' => 'tls',
            ],
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from' => 'noreply@example.com',
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
    ],
];

$configs = array_merge(getModuleConfigs('config', 'main'), [$baseConfig]);
return count($configs) == 1 ? reset($configs) : call_user_func_array(['yii\helpers\ArrayHelper', 'merge'], $configs);
