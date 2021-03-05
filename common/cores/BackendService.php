<?php
/**
 * 基础模块
 * @author pawN
 * @date 2019.09.30 14:28:54
 */

namespace common\cores;

use backend\models\PermissionService;
use frontend\models\Oauthor2\UsersService;

class BackendService extends ApiController
{
    //限流开关
    protected $threshold;

    //登录的用户ID
    protected $uid;

    //商户ID
    protected $wid = 0;

    /**
     * 任务前置
     * @param $action
     * @return boolean
     * @throws \Exception
     * @date 2019.09.30 14:33:54
     * @author pawN
     */
    public function beforeAction($action)
    {
        if (!$this->needToken) {
            return parent::beforeAction($action);
        }

//        PermissionService::conserve($this->router);

        //登录检测
//        $headers = \Yii::$app->getRequest()->getHeaders();
//        $token = $headers->get('authorization', '');
//        $this->uid = UsersService::checkLogin($token);

        return parent::beforeAction($action);
    }

    /**
     * 执行任务
     * @param ApiController $service
     * @param array $params
     * @return array
     * @throws \Exception
     * @date 2019.09.30 14:33:35
     * @author pawN
     */
    public function runAction($service, $params = [])
    {
        $method = $this->parseRequest($params);

        //接口限流
        if ($this->threshold) {
            //待续...
        }

        //校验数据
        if (method_exists($service, 'rules')) {
            $this->validate($service);
        }

        return $service->$method();
    }
}