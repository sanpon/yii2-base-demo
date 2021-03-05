<?php

namespace frontend\entry\ucenter;

use common\cores\FrontendService;
use common\services\CaptchaService;
use frontend\models\Oauthor2\UsersService;
use library\core\Redis;

class Permission extends FrontendService
{
    public function rules()
    {
        return [
            ['login' => [
                [['username', 'password','captcha'], 'required', 'message' => '{attribute}不能为空'],
            ]]
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'password' => '密码',
            'captcha' => '验证码'
        ];
    }

    /**
     * 登陆
     * @throws \Exception
     * @date 2020.07.13 16:27:30
     */
    public function actionLogin()
    {
        $captcha = $this->post('captcha', null);
        /**
         * @var \stdClass $valid
         */
        $valid = CaptchaService::validate($captcha);
        if (!$valid->status) {
            throw new \Exception('验证码错误');
        }

        $username = $this->post('username', null);
        $password = $this->post('password', null);
        return UsersService::login($username, $password, $valid);
    }

    /**
     * 退出系统
     * @throws \Exception
     * @date 2020.07.17 16:34:49
     */
    public function actionLogout()
    {
        $token = \Yii::$app->getRequest()->getHeaders()->get('authorization', null);
        $redis = Redis::startup();
        $redis->delete("user:{$token}");
        return ['message' => '退出登录'];
    }
}