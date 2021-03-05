<?php
/**
 * Web控制器
 * @author: pawN
 * @date 2018.11.30 00:25:10
 */

namespace entry\controllers;

use common\cores\WebController;
use common\library\Router;
use library\core\Context;
use yii\helpers\ArrayHelper;

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Headers:did,Authorization');

class DefaultController extends WebController
{
    public $layout = false;

    /**
     * 网站首页
     * @throws \Throwable
     * @date 2018.12.06 21:23:00
     * @author pawN
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $get = $request->get();

        $platform = ArrayHelper::getValue($get, '__platform', 'frontend');
        $module = ArrayHelper::getValue($get, '__module', 'entry');
        $controller = ArrayHelper::getValue($get, '__controller', 'Default');

        $std = new \stdClass();
        $platform = $platform == 'admin' ? 'backend' : $platform;
        $std->name = $module;
        $std->belong = $platform;

        $action = ArrayHelper::getValue($get, '__action', 'index');

        $router = new Router();
        $router->platform = $platform;
        $router->module = $module;
        $router->controller = $controller;
        $router->action = $action;
        return $router->web($std);
    }
}