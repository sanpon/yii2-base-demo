<?php


namespace entry\controllers;

use Codeception\Util\HttpCode;
use yii\base\Controller;
use yii\helpers\ArrayHelper;
use yii\web\Response;

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Headers:did,Authorization');

class CommonController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * @throws \Exception
     * @date 2020.10.25 14:15:29
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

        $params = array_merge($get, $request->post());

        $controller = ArrayHelper::getValue($get, '__controller', null);
        $controller = ucfirst(strtolower($controller));
        $action = ArrayHelper::getValue($get, '__action', 'index');
        $namespace = "common\\entry\\{$controller}";

        /**
         * @var \common\cores\ApiController $controller
         */
        $controller = new $namespace();

        $beforeAction = $controller->beforeAction($action);
        if (!$beforeAction) {
            return [];
        }

        return  $controller->runAction($controller, $params);
    }

    /**
     * @param \yii\base\Action $action
     * @param mixed $result
     * @return array|mixed
     * @date 2020.10.25 15:19:27
     */
    public function afterAction($action, $result)
    {
        parent::afterAction($action, $result);

        $baseReturn = [
            'status' => true,
            'code' => HttpCode::OK,
        ];
        $result = $result ? $result : [];

        \Yii::$app->getResponse()->format = Response::FORMAT_JSON;

        return array_merge($baseReturn, $result);
    }
}