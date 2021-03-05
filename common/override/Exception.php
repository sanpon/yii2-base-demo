<?php
/**
 *
 * @author pawN
 * @date 2019.10.02 15:18:21
 */

namespace common\override;

use Codeception\Util\HttpCode;
use yii\helpers\Json;
use yii\web\ErrorHandler;
use yii\web\Response;

class Exception extends ErrorHandler
{
    public $status;

    /**
     * 异常拦截处理
     * @param \Error|\Exception $exception
     * @date 2019.10.02 16:26:07
     * @author pawN
     */
    public function renderException($exception)
    {
        //正式环境记录系统异常信息
        if (YII_ENV_PROD) {
            \Yii::error(Json::encode([
                'exception' => $exception->getMessage(),
                'file' => $exception->getFile().'(Line: '.$exception->getLine().')',
                'code' => $exception->getCode(),
                'params' => [
                    'POST' => $_POST,
                    'GET' => $_GET,
                    'FILES' => $_FILES,
                    'SESSION' => $_SESSION,
                    'COOKIE' => $_COOKIE,
                    'SERVER' => $_SERVER
                ]
            ]), 'app_runtime');
        }

        //响应API异常
        if (defined('API_MODULE')) {
            $response = \Yii::$app->response;
            $code = $exception->getCode() ? $exception->getCode() : HttpCode::INTERNAL_SERVER_ERROR;
            $response->setStatusCode(200);
            $response->format = Response::FORMAT_JSON;
            $response->data = array_merge([
                'code' => $code,
                'status' => false,
                'message' => $exception->getMessage() ? $exception->getMessage() : '服务异常,请联系管理员处理',
            ], YII_DEBUG ? [
                'line' => $exception->getLine(),
                'file' => $exception->getFile()
            ] : []);
            $response->send();
            exit;
        }

        include \Yii::getAlias('@entry/views/error.php');
        exit;
    }
}
