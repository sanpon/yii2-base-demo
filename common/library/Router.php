<?php
/**
 * Rpc调用
 * @author pawN
 * @date 2019.08.09 16:26:27
 */

namespace common\library;

use Codeception\Util\HttpCode;
use common\cores\ApiController;

class Router
{
    //命名空间
    public $namespace;

    //分组
    public $platform;

    //API版本
    public $version;

    //模块
    public $module;

    //API服务名称
    public $service;

    //控制器
    public $controller;

    //API接口
    public $action;

    /**
     * API执行
     * @param array $params 用户参数
     * @return array
     * @date 2019.09.02 15:17:54
     * @throws \Exception
     */
    public function resolve(array $params): array
    {
        $service = ucfirst($this->service);

        $file = APP_PATH . DIRECTORY_SEPARATOR . "{$this->namespace}\\{$this->version}\\{$this->module}\\{$service}.php";
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);

        if (!file_exists($file)) {
            throw new \Exception($file . \Yii::t('yii', 'File not exists.'), HttpCode::NOT_FOUND);
        }

        include_once($file);
        $class = "{$this->namespace}\\{$this->module}\\{$service}";
        //此处需要使
        if (!class_exists($class)) {
            throw new \Exception($file . \Yii::t('yii', 'Class not exists.'), HttpCode::NOT_FOUND);
        }

        /**
         * @var ApiController $handler
         */
        $handler = new $class();
        $handler->router = [
            'platform' => $this->platform,
            'module' => $this->module,
            'service' => $service,
            'path' => strtolower(implode('/', [$this->platform, 'api', $this->module, $service, $this->action]))
        ];
        $beforeAction = $handler->beforeAction($this->action);
        if (!$beforeAction) {
            return [];
        }

        $result = $handler->runAction($handler, $params);

        return $handler->afterAction($this->action, $result);
    }

    /**
     * web调用
     * @param \stdClass $params
     * @return mixed|null
     * @date 2021.02.26 15:04:09
     */
    public function web(\stdClass $params)
    {
        $className = Context::camel($this->controller) . 'Controller';
        $action = 'action' . Context::camel($this->action);

        $class = "{$this->platform}\\controllers\\{$this->module}\\{$className}";

        $controller = (new $class(\Yii::$app->id, $params));
        if (method_exists($controller, 'beforeAction')) {
            $controller->beforeAction($this->action);
        }

        return $controller->$action();
    }

    /**
     * web内部调用
     * @param string $router
     * ```php
     * $router格式
     *  entry/default
     *  entry/default/index
     * ```
     * @param string $platform
     * @return mixed
     * @date 2021.02.26 15:16:26
     */
    public static function forward(string $router, string $platform = 'frontend')
    {
        $static = (new self());
        $static->platform = $platform;

        $router = explode('/', $router);
        $static->module = $router[0];
        $static->controller = $router[1];
        $static->action = isset($router[2]) ? $router[2] : 'index';

        $std = new \stdClass();
        $std->name = $static->module;
        $std->belong = $platform;

        return $static->web($std);
    }

    /**
     * 路由替换
     * @param string $router
     * @param string $platform
     * @return mixed
     * @date 2021.02.26 16:58:51
     */
    public static function replace(string $router, string $platform = 'frontend')
    {
        return static::forward($router, $platform);
    }
}
