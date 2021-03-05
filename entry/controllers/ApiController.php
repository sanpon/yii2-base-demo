<?php
/**
 * API模块管理
 * @author: pawN
 * @date 2018.11.26 00:30:01
 */

namespace entry\controllers;

use common\library\Router;
use yii\helpers\ArrayHelper;
use yii\web\Response;

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Headers:did,Authorization');

class ApiController extends CommonController
{
    const VERSION = 'v1.0';
    const PLATFORM = 'frontend';

    /**
     * API请求
     * @return array
     * @throws \Exception
     * @author pawN
     * @date 2019.09.02 15:12:01
     */
    public function actionIndex()
    {
        //拦截非常规请求
        if (!in_array(strtolower($_SERVER['REQUEST_METHOD']), ['get', 'post'])) {
            exit(json_encode(['_t' => true]));
        }
        defined('API_MODULE') or define('API_MODULE', true);
        $request = \Yii::$app->request;
        $get = $request->get();

        $version = ArrayHelper::getValue($get, '__version', static::VERSION);
        $platform = ArrayHelper::getValue($get, '__platform', static::PLATFORM);
        $module = ArrayHelper::getValue($get, '__module', null);
        $service = ArrayHelper::getValue($get, '__service', null);
        $action = ArrayHelper::getValue($get, '__action', 'index');
        $namespace = "{$platform}\\entry";

        $mapping = ['__version', 'module', 'service', 'action', \Yii::$app->urlManager->routeParam];

        /**
         * 过滤get请求中的系统参数：module service action 路由
         */
        foreach ($mapping as $key) {
            ArrayHelper::remove($get, $key);
        }

        $actionParams = array_merge($get, $request->post());

        \Yii::$app->getResponse()->format = Response::FORMAT_JSON;

        $router = new Router();
        $router->namespace = $namespace;
        $router->platform = $platform;
        $router->version = $version;
        $router->module = $module;
        $router->service = $service;
        $router->action = $action;

        return $router->resolve($actionParams);
    }
}