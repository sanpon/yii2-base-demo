<?php

namespace frontend\models\Oauthor2;

use Codeception\Util\HttpCode;
use common\models\Users;
use library\core\Redis;

class UsersService extends Users
{
    /**
     * 登录系统
     * @param $username
     * @param $password
     * @param $valid
     * @return array
     * @date 2020.07.18 10:42:02
     * @throws \Exception
     */
    public static function login($username, $password, $valid)
    {
        $model = static::findOne(['username' => $username]);

        if (empty($model) || !password_verify($password, $model->password)) {
            throw new \Exception('用户名或密码错误');
        }

        //重新生成验证码
        $valid->session->close();
        $valid->captcha->getCode();

        $loginTime = time();
        $loginTimestamp = $loginTime * 1000;
        $accessToken = sha1($model->token . $valid->session_id . $loginTimestamp);
        $redis = Redis::startup();
        $redis->set("user:{$accessToken}", [
            'id' => $model->id,
            'login_time' => $loginTime
        ], static::EXPIRE);

        return [
            'access_token' => $accessToken,
            'username' => $model->username
        ];
    }

    /**
     * 检查登录信息
     * @param string $token 登录授权码
     * @return int
     * @date 2020.07.14 14:28:40
     * @throws \Exception
     */
    public static function checkLogin($token)
    {
        $redis = Redis::startup();
        $info = $redis->get("user:{$token}");

        //一次会话不能超过24小时 连续超出24小时则认为是机器人访问 强制重新登陆
        if (!$info || time() - $info['login_time'] >= static::MAX_EXPIRE) {
            throw new \Exception('请先登录', HttpCode::UNAUTHORIZED);
        }

        //失效前2min重新刷新有效期
        if ($redis->ttl("user:{$token}") < static::REFRESH_EXPIRE) {
            //刷新用户token有效期
            $redis->set("user:{$token}", $info, Users::EXPIRE);
        }

        return $info['id'];
    }
}