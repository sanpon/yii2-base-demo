<?php
declare(strict_types=1);

function show($message, $exit = true)
{
    $debug = debug_backtrace(1);
    echo "<h4 style='color: #1b6d85'>{$debug[0]['file']}:{$debug[0]['line']}</h4>";
    echo '<pre style="color: green">';
    var_dump($message);
    echo '<pre>';
    $exit && die;
}

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
define('APP_PATH', dirname(__DIR__));

require(APP_PATH . '/vendor/autoload.php');
require(APP_PATH . '/vendor/yiisoft/yii2/Yii.php');
require(APP_PATH . '/common/config/bootstrap.php');

$config = require(APP_PATH . '/common/config/main.php');

// prod模式禁用debug和gii debug和gii的路由跟随路由规则 例如 路由规则设置为重写+.html 则访问为 domain/debug.html 和 domain/gii.html
if (!YII_ENV != 'prod') {
//    $config['bootstrap'][] = 'debug';
//    $config['modules']['debug'] = [
//        'class' => 'yii\debug\Module',
//    ];
//
//    $config['bootstrap'][] = 'gii';
//    $config['modules']['gii'] = [
//        'class' => 'yii\gii\Module',
//    ];
}
//(new \yii\web\Application($config))->run();
(new \common\override\Application($config))->run();
