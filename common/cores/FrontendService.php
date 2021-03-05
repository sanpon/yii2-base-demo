<?php
/**
 *
 * @author pawN
 * @date 2019.10.02 14:55:14
 */

namespace common\cores;


class FrontendService extends ApiController
{
    //限流开关
    private $threshold;

    /**
     * @param $action
     * @return bool
     * @throws \Exception
     * @date 2020.06.12 17:15:23
     */
    public function beforeAction($action)
    {
        //登陆信息校验
        return parent::beforeAction($action);
    }

    /**
     * 运行
     * @param ApiController $service
     * @param array $params
     * @return array
     * @throws \Exception
     * @date 2020.06.12 17:16:26
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