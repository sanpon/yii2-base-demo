<?php

namespace frontend\controllers\entry;

use common\components\redis\RedisClient;
use common\cores\FrontendController;
use common\library\Router;

class DefaultController extends FrontendController
{
    public function actionIndex()
    {
//        show(RedisClient::getRedis('myapp')->expire('clientSet', 50));
        show(RedisClient::getRedis('myapp')->ttl('clientSet'));
        show(\Yii::$app->getRedis()->set('msset', '这是通过msset的内容'));
        Router::forward('demo/default');
        Router::replace('entry/default', 'backend');
        return $this->render('default');
    }
}