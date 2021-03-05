<?php
$params = array_merge(
    require(APP_PATH . '/common/config/params.php'),
    require('@config/config/params.php')
);

return [
    'id' => 'console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'gii'],
    'controllerNamespace' => 'console\controllers',
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\console\controllers\FixtureController',
            'namespace' => 'common\fixtures',
          ],
    ],
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'user' => [
            'class' => 'common\models\MembersModel',
        ],
        'session' => [
            'class' => 'yii\web\Session',
        ],
    ],
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'params' => $params,
];
