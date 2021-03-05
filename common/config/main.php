<?php
$database = include(APP_PATH . '/config/db.php');
$mail = include(APP_PATH . '/config/mail.php');
$redis = include(APP_PATH . '/config/redis.php');
$params = require(APP_PATH . '/common/config/params.php');
$module_id = 'entry';
return [
    'id' => $module_id,
    'basePath' => APP_PATH,
    'runtimePath' => APP_PATH . "/runtime/{$module_id}",
    'bootstrap' => ['log'],
    'controllerNamespace' => 'entry\controllers',
    'defaultRoute' => 'default',
    'vendorPath' => APP_PATH . '/vendor',
    'language' => 'zh_CN',
    'components' => [
        'request' => [
            'csrfParam' => 'token',
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'rio7fGP853sxb-bAWGFsGEN3UxRStnbQ',
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => '_sin',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'suffix' => '.html',
            'rules' => [
                //公共服务模块
                'services/<__controller:\w+(?:-\w+)?>' => 'common/index',
                'services/<__controller:\w+(?:-\w+)?>/<__action:\w+(?:\w+)?>' => 'common/index',

                //api部分路由
                '<__platform:\w+>/api/<__module:\w+>/<__service:\w+(?:-\w+)?>' => 'api/index',
                '<__platform:\w+>/api/<__module:\w+>/<__service:\w+(?:-\w+)?>/<__action:\w+(?:-\w+)*>' => 'api/index',
                '<__platform:\w+>/api/<__version:v\d+(\.\d+)?>/<__module:\w+>/<__service:\w+(?:-\w+)?>' => 'api/index',
                '<__platform:\w+>/api/<__version:v\d+(\.\d+)?>/<__module:\w+>/<__service:\w+(?:-\w+)?>/<__action:\w+(?:-\w+)*>' => 'api/index',
                'api/<__module:\w+>/<__service:\w+(?:-\w+)?>' => 'api/index',
                'api/<__module:\w+>/<__service:\w+(?:-\w+)?>/<__action:\w+(?:-\w+)*>' => 'api/index',
                'api/<__version:(v\d+(\.\d+)?)?>/<__module:\w+>/<__service:\w+(?:-\w+)?>' => 'api/index',
                'api/<__version:(v\d+(\.\d+)?)?>/<__module:\w+>/<__service:\w+(?:-\w+)?>/<__action:\w+(?:-\w+)*>' => 'api/index',

                //web部分路由
                '<__platform:admin>' => 'default/index',
                '<__platform:backend>/<__module:\w+>/<__controller:\w+(?:-\w+)?>' => 'default/index',
                '<__platform:backend>/<__module:\w+>/<__controller:\w+(?:-\w+)?>/<__action:\w+(?:-\w+)?>' => 'default/index',
                '<__module:\w+>/<__controller:\w+(?:-\w+)?>' => 'default/index',
                '<__module:\w+>/<__controller:\w+(?:-\w+)?>/<__action:\w+(?:\w+)?>' => 'default/index',
            ],
        ],
        'db' => $database,
        //此处配置会影响url缓存信息
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logFile' => "@runtime/logs/app.log",
                    'logVars' => [] //系统级错误的额外参数意义不是很大 仅记录业务层的的请求环境参数 避免产生垃圾数据 增大日志
                ],
            ],
        ],
        'redis' => $redis,
        //接管系统异常实现自定义
        'errorHandler' => [
            'errorAction' => 'base/error',
            'class' => 'common\override\Exception'
        ],
        //语言配置 该处配置作用于语言转换 例如：yii::t('app', 'goods list')
        'i18n' => [
            'translations' => [
//                'app*' => [
//                    'class' => 'yii\i18n\PhpMessageSource',
//                    'basePath' => '@common/language',
//                    'fileMap' => [
//                        'goods' => 'goods.php'
//                    ],
//                ],
                'yii*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/language',
                    'fileMap' => [
                        'yii' => 'yii.php'
                    ],
                ]
            ],
        ],
        'mailer' => $mail
    ],
    'params' => $params
];
