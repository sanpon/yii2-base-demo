<?php
/**
 * Redis客户端
 */

namespace common\components\redis;


class RedisClient
{
    /**
     * @var Redis
     */
    private $redis;

    /**
     * @param string $prefix
     * @return RedisClient
     * @date 2021.03.05 12:34:09
     */
    public static function getRedis($prefix = '')
    {
        $instance = new static();
        $instance->redis = \Yii::$app->getRedis();
        $instance->redis->prefix = $prefix;
        return $instance;
    }

    public function __call($method, $arguments)
    {
        return $this->redis->$method(...$arguments);
    }
}