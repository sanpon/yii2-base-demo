<?php

/**
 * Redis组件
 */

namespace common\components\redis;

use yii\base\Component;

class Redis extends Component
{
    /**
     * @var null|\Redis
     */
    private $redis = null;

    public $host = '127.0.0.1';
    public $port = 6379;
    public $password = '';
    public $database = 0;
    public $timeout = 3;

    public $enableSlaves = false;
    public $master = [];
    public $slaves = [];

    //同一前缀
    public $prefix = '';
    public $delimeter = ':';

    //主机连接
    private $connections = [];

    const JSON_ENCODE = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    //读写命令
    private $commands = [
        'write' => [
            'set', 'getset', 'append', 'setrange', 'increase', 'decrease', 'mset', 'hset', 'hdel', 'hincrease', 'hmset',
            'lpush', 'lpushx', 'lpop', 'rpop', 'rpoplpush', 'lrem', 'linsert', 'lset', 'ltrim', 'blpop', 'brpop',
            'brpoplpush', 'sadd', 'spop', 'srem', 'smove', 'sintersectstore', 'sunionstore', 'sdiffstore', 'zadd',
            'zincrby', 'expire'
        ],
        'read' => [
            'get', 'strlen', 'getrange', 'mget', 'hget', 'hexists', 'hlen', 'hstrlen', 'hmget', 'hkeys', 'hvals',
            'hgetall', 'llen', 'lindex', 'lrange', 'sismember', 'srandmember', 'scard', 'smembers', 'sintersect',
            'sunion', 'sdiff', 'zscore', 'zcard', 'zcount', 'zrange'
        ]
    ];

    /**
     * @throws \Exception
     * @date 2021.03.05 10:41:33
     */
    public function init()
    {
        if ($this->enableSlaves === false) {
            $index = $this->host . ':' . $this->port;
            $this->connections['masters'][$index] = $this->pconnect();
            return $this;
        }

        //主节点
        if (empty($this->master)) {
            $index = $this->host . $this->port;
            $this->connections['masters'][$index] = $this->pconnect();
        } else {
            foreach ($this->master as $master) {
                $this->host = $master['host'];
                $this->port = $master['port'];
                $this->password = isset($master['password']) ? $master['password'] : '';
                $this->database = isset($master['database']) ? $master['database'] : $this->database;
                $index = $master['host'] . ':' . $master['port'];
                $this->connections['masters'][$index] = $this->pconnect();
            }
        }

        //从节点
        foreach ($this->slaves as $slave) {
            $this->host = $slave['host'];
            $this->port = $slave['port'];
            $this->password = isset($slave['password']) ? $slave['password'] : '';
            $this->database = isset($slave['database']) ? $slave['database'] : $this->database;
            $index = $slave['host'] . ':' . $slave['port'];
            $this->connections['slaves'][$index] = $this->pconnect();
        }

        return $this;
    }

    /**
     * 连接redis
     * @throws \Exception
     * @date 2021.03.05 10:41:11
     */
    private function pconnect()
    {
        $redis = new \Redis();
        $persistentId = md5($this->host . $this->port);

        //处理连接失败
        try {
            $redis->pconnect($this->host, $this->port, $this->timeout, $persistentId);
        } catch (\Exception $e) {
            throw new \Exception(Yii::t('yii', 'Redis Connection refused'));
        }

        //处理密码授权失败
        try {
            $redis->ping();
        } catch (\Exception $e) {
            if (!$redis->auth($this->password)) {
                throw new \Exception($e->getMessage(), 403);
            }
        }

        $redis->select($this->database);

        return $redis;
    }

    /**
     * @return Redis
     * @date 2021.02.05 10:34:12
     */
    final public function __clone()
    {
        return $this;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     * @date 2021.03.05 11:18:57
     */
    public function __call($method, $arguments)
    {
        //是否开启主从
        if ($this->enableSlaves === false) {
            $this->redis = current($this->connections['masters']);
        } else {
            $this->redis = in_array($method, $this->commands['write'], true) ? $this->getMaster() : $this->getSlave();
        }

        $this->setPrefix();

        if (method_exists($this, $method)) {
            return $this->$method(...$arguments);
        }

        if (method_exists($this->redis, $method)) {
            return $this->redis->$method(...$arguments);
        }

        throw new \Exception("抱歉,未知的操作{$method}");
    }

    /**
     * 设置redis Key公共前缀
     * @date 2021.03.05 13:04:05
     */
    private function setPrefix()
    {
        if ($this->prefix) {
            $this->redis->setOption(\Redis::OPT_PREFIX, $this->prefix . $this->delimeter);
        }
    }

    /**
     * 获取主节点
     * @return mixed
     * @date 2021.03.05 12:08:49
     */
    public function getMaster()
    {
        $connections = array_values($this->connections['masters']);
        $index = mt_rand(0, count($connections) - 1);
        return $connections[$index];
    }

    public function getSlave()
    {
        $connections = array_values($this->connections['slaves']);
        $index = mt_rand(0, count($connections) - 1);
        return $connections[$index];
    }

    /**
     *************************** String类型操作 *****************************
     * Redis字符串类型操作集合
     **********************************************************************
     */

    /**
     * 设置字符串值
     * @param string $key
     * @param string|array $value
     * @param int $expire 过期时间
     * @param string $unit 时间单位 EX=>秒 PX=>毫秒
     * @param null|boolean $exists KEY添加方式 false key不存在时设置 true key存在时设置
     * @return boolean
     * @date 2021.01.18 18:22:50
     */
    private function set($key, $value, $expire = null, $unit = 'EX', $exists = null)
    {
        $value = is_array($value) ? json_encode($value, self::JSON_ENCODE) : $value;

        if (is_null($expire)) {
            return $this->redis->set($key, $value);
        }

        if (is_null($exists)) {
            return $this->redis->set($key, $value, [$unit => $expire]);
        }

        if ($exists === true) {
            return $this->redis->set($key, $value, ['XX', $unit => $value]);
        }

        return $this->redis->set($key, $value, ['NX', $unit => $value]);
    }

    /**
     * 获取字符串类型值
     * @param string $key
     * @return bool|mixed|string
     * @date 2021.01.19 01:04:45
     */
    private function get($key)
    {
        $context = $this->redis->get($key);
        $result = json_decode($context, true);
        return is_null($result) ? $context : $result;
    }

    /**
     * 设置字符串类型并返回设置之前的值
     * @param string $key
     * @param string $value
     * @return mixed|string
     * @date 2021.01.19 01:08:41
     */
    private function getset($key, $value)
    {
        return $this->redis->getSet($key, $value);
    }

    /**
     * 获取字符串key值的长度
     * @param string $key
     * @return int
     * @date 2021.01.19 01:10:36
     */
    private function strlen($key)
    {
        return $this->redis->strlen($key);
    }

    /**
     * 在字符串末尾追加内容
     * @param string $key
     * @param string $value
     * @return int 新值的长度
     * @date 2021.01.19 10:27:19
     */
    private function append($key, $value)
    {
        return $this->redis->append($key, $value);
    }

    /**
     * 字符串替换
     * @param string $key
     * @param int $offset
     * @param string $value
     * @return int 字符串修改后的长度
     * @date 2021.01.19 10:39:42
     */
    private function setrange($key, $offset, $value)
    {
        return $this->redis->setRange($key, $offset, $value);
    }

    /**
     * 字符串截取 默认获取整个字符串相当于get(key)
     * @param string $key
     * @param int $start
     * @param int $end
     * @return string
     * @date 2021.01.19 10:55:21
     */
    private function getrange($key, $start = 0, $end = -1)
    {
        return $this->redis->getRange($key, $start, $end);
    }

    /**
     * 整型自增
     * @param string $key
     * @param int|string|float $step 自增步长 默认自增1 负数表示自减
     * @return int|float
     * @date 2021.01.19 11:26:59
     */
    private function increase($key, $step = 1)
    {
        if ($step === 1) {
            return $this->redis->incr($key);
        }

        //整型
        if (strpos($step, '.') === false) {
            $step = (int)$step;
            return $this->redis->incrBy($key, $step);
        }

        //浮点型
        $step = (float)$step;
        return $this->redis->incrByFloat($key, $step);
    }

    /**
     * 整数
     * @param $key
     * @param int $step 自减步长 默认-1 负数表示自增
     * @return int
     * @date 2021.01.19 11:28:06
     */
    private function decrease($key, $step = -1)
    {
        if ($step === -1) {
            return $this->redis->decr($key);
        }
        $step = (int)$step;
        return $this->redis->decrBy($key, $step);
    }

    /**
     * 批量设置字符串键值
     * ```php
     * $array = [
     *  'name' => 'pawn',
     *  'age' => 20
     *  ...
     * ];
     * ```
     * @param array $array 多个键值对
     * @param boolean $overwrite 是否覆盖已经存在的key $overwrite == false 仅当提供的key都不存在时才会执行
     * @return bool
     * @date 2021.01.19 14:13:25
     */
    private function mset($array, $overwrite = true)
    {
        if ($overwrite) {
            return $this->redis->mset($array);
        }

        return $this->redis->msetnx($array);
    }

    /**
     * 批量获取字符串key
     * @param array $fields
     * @return array 结果关联数组 如果获取key失败对应值为false
     * @date 2021.01.19 14:20:41
     */
    private function mget($fields)
    {
        $result = $this->redis->mget($fields);
        $values = [];
        foreach ($fields as $key => $val) {
            $values[$val] = $result[$key];
        }
        return $values;
    }

    /**
     *************************** Hash类型操作 *****************************
     * Redis哈希类型操作集合
     * Hash结构类似关系型数据库二维结构
     * 一个hash缓存key可以看做是数据库名称
     * 因此hash的一个重要应用就是模拟关系型数据库
     *********************************************************************
     */

    /**
     * 往hash表添加一条记录
     * @param String $key 缓存Key
     * @param string|int $index 数据索引/数据key
     * @param string|array $value 数据值
     * @param boolean $overwrite 是否覆盖已存在的数据索引值/数据key值
     * @return bool|int
     * @date 2021.01.19 15:03:19
     */
    private function hset($key, $index, $value, $overwrite = true)
    {
        $value = is_array($value) ? json_encode($value, self::JSON_ENCODE) : $value;

        if ($overwrite) {
            return $this->redis->hSet($key, $index, $value);
        }

        return $this->redis->hSetNx($key, $index, $value);
    }

    /**
     * 获取hash表中的一条数据
     * @param string $key 缓存Key
     * @param string $index 数据索引
     * @return string|array|boolean
     * @date 2021.01.19 15:24:35
     */
    private function hget($key, $index)
    {
        $context = $this->redis->hGet($key, $index);
        if ($context === false) {
            return false;
        }

        $result = json_decode($context, true);
        return is_null($result) ? $context : $result;
    }

    /**
     * 数据索引是否在hash表中
     * @param string $key 缓存key
     * @param string|int $index 数据索引
     * @return bool
     * @date 2021.01.19 15:43:06
     */
    private function hexists($key, $index)
    {
        return $this->redis->hExists($key, $index);
    }

    /**
     * 从hash表记录中删除一条记录
     * @param string $key 缓存key
     * @param string|int $index hash索引key
     * @return bool|int
     * @date 2021.01.19 15:47:34
     */
    private function hdel($key, $index)
    {
        return $this->redis->hDel($key, $index);
    }

    /**
     * 统计hash表中记录数量
     * @param $key
     * @return bool|int
     * @date 2021.01.19 15:51:48
     */
    private function hlen($key)
    {
        return $this->redis->hLen($key);
    }

    /**
     * 计算hash索引值长度
     * @param string $key
     * @param int|string $index
     * @return int
     * @date 2021.01.20 18:23:33
     */
    private function hstrlen($key, $index)
    {
        return $this->redis->hStrLen($key, $index);
    }

    /**
     * hash索引值自增/自减算数运算
     * @param string $key
     * @param string|int $index
     * @param int|float $step 自增步长
     * @return int|float
     * @date 2021.01.21 10:48:38
     */
    private function hincrease($key, $index, $step = 1)
    {
        if (is_integer($step)) {
            return $this->redis->hIncrBy($key, $index, $step);
        }

        return $this->redis->hIncrByFloat($key, $index, $step);
    }

    /**
     * 批量设置hash键值对
     * @param string $key
     * @param array $data 需要存储的数据
     * @param string|null $index hash索引
     * @return bool
     * @date 2021.01.21 11:18:20
     */
    private function hmset($key, $data, $index = null)
    {
        $values = [];
        foreach ($data as $k => $v) {
            $field = $index ? $v[$index] : $k;
            $values[$field] = is_array($v) ? json_encode($v, self::JSON_ENCODE) : $v;
        }

        return $this->redis->hMSet($key, $values);
    }

    /**
     * 批量获取hash索引值
     * @param string $key
     * @param array $indexes
     * @return array
     * @date 2021.01.21 14:39:33
     */
    private function hmget($key, $indexes)
    {
        $data = $this->redis->hMGet($key, $indexes);
        foreach ($data as $k => $val) {
            $result = json_decode($val, true);
            $data[$k] = $result == null ? $val : $result;
        }
        return $data;
    }

    /**
     * 获取一个hash表中的所有索引
     * @param string $key
     * @return array
     * @date 2021.01.21 14:44:36
     */
    private function hkeys($key)
    {
        return $this->redis->hKeys($key);
    }

    /**
     * 获取一个hash表中的所有值
     * @param string $key
     * @return array
     * @date 2021.01.21 14:50:56
     */
    private function hvals($key)
    {
        $data = $this->redis->hVals($key);
        foreach ($data as $k => $v) {
            $result = json_decode($v, true);
            $data[$k] = is_null($result) ? $v : $result;
        }

        return $data;
    }

    /**
     * 获取一个完整的hash表
     * @param string $key
     * @return array
     * @date 2021.01.21 14:56:43
     */
    private function hgetall($key)
    {
        $data = $this->redis->hGetAll($key);
        foreach ($data as $k => $v) {
            $result = json_decode($v, true);
            $data[$k] = is_null($result) ? $v : $result;
        }
        return $data;
    }

    /**
     *************************** List类型操作 *****************************
     * Redis列表类型操作集合
     **********************************************************************
     */

    /**
     * 自动创建列表并向列表头增加内容
     * ```php
     * 简单类型
     * lpush('userinfo', 121)
     * lpush('userinfo', 'niubao')
     * lpush('userinfo', true)
     *
     * 一维关联数组 数组元素作为整体添加且以第一个元素
     * lpush('userinfo', ['name'=>'阿木木', 'age'=>18])
     *
     * 一维索引数组 逐个插入数组元素
     * lpush('userinfo', ['阿木木', '提莫', '小法师', '露露', '波比'])
     *
     * 二维数组
     * lpush('userinfo', [['id'=>1, 'title'=>'小米'],['id'=>2, 'title'=>'华为']])
     * ```
     * @param string $key 列表名称
     * @param mixed $values
     * @return bool|int 列表长度
     * @date 2021.01.21 16:28:07
     */
    private function lpush($key, $values)
    {
        //简单类型
        if (!is_array($values)) {
            return $this->redis->lPush($key, $values);
        }

        //一维关联数组
        if (array_keys($values) !== range(0, count($values) - 1)) {
            return $this->redis->lPush($key, json_encode($values, self::JSON_ENCODE));
        }

        //一维索引数组
        if (!is_array(current($values))) {
            return $this->redis->lPush($key, ...$values);
        }

        $data = [];
        foreach ($values as $k => $v) {
            $data[$k] = is_array($v) ? json_encode($v, self::JSON_ENCODE) : $v;
        }

        return $this->redis->lPush($key, ...$data);
    }

    /**
     * 向已存在的列表头部追加内容
     * @param string $key 列表名称
     * @param int|string|array $value
     * @return bool|int 列表长度
     * @date 2021.01.22 10:15:27
     */
    private function lpushx($key, $value)
    {
        $value = is_array($value) ? json_encode($value, self::JSON_ENCODE) : $value;
        return $this->redis->lPushx($key, $value);
    }

    /**
     * 创建列表或向已存在列表尾部追加一条或多条内容
     * ```php
     * 简单类型
     * rpush('userinfo', 121)
     * rpush('userinfo', 'niubao')
     * rpush('userinfo', true)
     *
     * 一维关联数组 数组元素作为整体添加且以第一个元素
     * rpush('userinfo', ['name'=>'阿木木', 'age'=>18])
     *
     * 一维索引数组 逐个插入数组元素
     * rpush('userinfo', ['阿木木', '提莫', '小法师', '露露', '波比'])
     *
     * 二维数组
     * rpush('userinfo', [['id'=>1, 'title'=>'小米'],['id'=>2, 'title'=>'华为']])
     * ```
     * @param string $key 列表名称
     * @param mixed $values
     * @return bool|int 列表长度
     * @date 2021.01.21 16:28:07
     */
    private function rpush($key, $values)
    {
        //简单类型
        if (!is_array($values)) {
            return $this->redis->rPush($key, $values);
        }

        //一维关联数组
        if (array_keys($values) !== range(0, count($values) - 1)) {
            return $this->redis->rPush($key, json_encode($values, self::JSON_ENCODE));
        }

        //一维索引数组
        if (!is_array(current($values))) {
            return $this->redis->rPush($key, ...$values);
        }

        $data = [];
        foreach ($values as $k => $v) {
            $data[$k] = is_array($v) ? json_encode($v, self::JSON_ENCODE) : $v;
        }

        return $this->redis->rPush($key, ...$data);
    }

    /**
     * 向存在的列表尾部追加内容
     * @param string $key
     * @param mixed|string $value
     * @return bool|int
     * @date 2021.01.22 14:46:33
     */
    private function rpushx($key, $value)
    {
        $value = is_array($value) ? json_encode($value, self::JSON_ENCODE) : $value;
        return $this->redis->rPushx($key, $value);
    }

    /**
     * 删除列表头部元素并返回
     * @param string $key
     * @param boolean $filterable 是否对结果json_decode处理
     * @return mixed|array
     * @date 2021.01.22 16:19:45
     */
    private function lpop($key, $filterable = false)
    {
        $context = $this->redis->lPop($key);

        return $filterable ? json_decode($context, true) : $context;
    }

    /**
     * 删除列表尾部元素并返回
     * @param string $key
     * @param boolean $filterable 是否对返回json_decode数据处理
     * @return bool|mixed
     * @date 2021.01.22 16:27:30
     */
    private function rpop($key, $filterable = false)
    {
        $context = $this->redis->rPop($key);

        return $filterable ? json_decode($context, true) : $context;
    }

    /**
     * 将source列表的尾部元素移动至destination列表头并返回被移动的元素
     * @param string $source 来源列表
     * @param string $destination 目标列表
     * @param boolean 是否json_decode被Pop的元素
     * @return bool|mixed|string
     * @date 2021.01.22 16:41:55
     */
    private function rpoplpush($source, $destination, $filterable = false)
    {
        $context = $this->redis->rpoplpush($source, $destination);

        return $filterable ? json_decode($context, true) : $context;
    }

    /**
     * 搜索并删除列表中的元素
     * @param string $key
     * @param string|array $value 待删除的值
     * @param int $count 删除数量 0=>删除所有 >0从表头开始搜索 <0从表尾开始搜索
     * @return bool|int 若$key为list则返回删除个数 未找到返回0 若$key为非list类型 则返回false
     * @date 2021.01.22 16:58:01
     */
    private function lrem($key, $value, $count = 0)
    {
        $value = is_array($value) ? json_encode($value, self::JSON_ENCODE) : $value;
        return $this->redis->lRem($key, $value, $count);
    }

    /**
     * 列表长度
     * @param string $key
     * @return bool|int 若$key为list类型 返回列表长度 $key不存在返回0 $key为非list返回false
     * @date 2021.01.22 17:19:31
     */
    private function llen($key)
    {
        return $this->redis->lLen($key);
    }

    /**
     * 获取列表中的元素
     * @param $key
     * @param int $index 列表中元素的索引 从0开始 负数表示从列表尾部开始计数
     * @param boolean $filterable
     * @return bool|mixed
     * @date 2021.01.22 17:32:09
     */
    private function lindex($key, $index, $filterable = false)
    {
        $context = $this->redis->lIndex($key, $index);

        return $filterable ? json_decode($context, true) : $context;
    }

    /**
     * 向列表中插入元素
     * 注意从列表头开始查找到的第一个位置插入
     * @param $key
     * @param mixed $pivot 插入点
     * @param mixed $value 插入值
     * @param string $type 插入方式 默认前置插入 \Redis::BEFORE 前置  \Redis::AFTER后置
     * @return int
     * @date 2021.01.22 17:51:03
     */
    private function linsert($key, $pivot, $value, $type = \Redis::BEFORE)
    {
        $pivot = is_array($pivot) ? json_encode($pivot, self::JSON_ENCODE) : $pivot;
        $value = is_array($value) ? json_encode($value, self::JSON_ENCODE) : $value;
        return $this->redis->lInsert($key, $type, $pivot, $value);
    }

    /**
     * 更新列表索引对应值
     * @param string $key
     * @param int $index 待更新位置的索引
     * @param mixed $value 更新值
     * @return bool
     * @date 2021.01.22 18:21:03
     */
    private function lset($key, $index, $value)
    {
        $value = is_array($value) ? json_encode($value, self::JSON_ENCODE) : $value;
        return $this->redis->lSet($key, $index, $value);
    }

    /**
     * 获取列表中的数据
     * @param string $key
     * @param int $start 开始位置
     * @param int $end 结束位置
     * @param boolean $filterable 是否对结果列表中的元素json_decode处理
     * @return array
     * @date 2021.01.22 18:31:36
     */
    private function lrange($key, $start = 0, $end = -1, $filterable = false)
    {
        $context = $this->redis->lRange($key, $start, $end);
        if ($filterable === false) {
            return $context;
        }

        foreach ($context as $k => $v) {
            $context[$k] = json_decode($v, true);
        }
        return $context;
    }

    /**
     * 删除不在范围内的列表数据
     * @param string $key
     * @param int $start 开始位置
     * @param int $end 结束位置
     * @return bool
     * @date 2021.01.23 12:26:55
     */
    private function ltrim($key, $start, $end)
    {
        return $this->redis->lTrim($key, $start, $end);
    }

    /**
     * 阻塞式Lpop 找到以第一个不为空的元素执行并返回
     * 一个key时 为空时直接阻塞
     * 多个key时 只有全部key都为空时进入阻塞 否则返回第一个不为空的key的头部元素
     * @param string|array $keys
     * @param int $timeout 阻塞时长,单位:秒 [0 无限等待 >0 阻塞时长]
     * @return array
     * @date 2021.01.25 14:19:34
     */
    private function blpop($keys, $timeout = 0)
    {
        return $this->redis->blPop($keys, $timeout);
    }

    /**
     * 阻塞式RPOP
     * @param string|array $keys
     * @param int $timeout
     * @return array
     * @date 2021.01.25 14:34:43
     */
    private function brpop($keys, $timeout = 0)
    {
        return $this->redis->brPop($keys, $timeout);
    }

    /**
     * 阻塞式RPOPLPUSH
     * @param string $source
     * @param string $destination
     * @param int $timeout
     * @return bool|mixed|string
     * @date 2021.01.25 14:39:56
     */
    private function brpoplpush($source, $destination, $timeout = 0)
    {
        return $this->redis->brpoplpush($source, $destination, $timeout);
    }

    /**
     *************************** 无序集合类型 *****************************
     * Redis无序集合类型操作
     **********************************************************************
     */

    /**
     * 集合添加元素 若元素已存在则忽略
     * @param string $key
     * @param mixed $elements 若为boolean则被转换为0|1存储
     * @return bool|int 有效新增元素数量
     * @date 2021.01.25 14:47:10
     */
    private function sadd($key, $elements)
    {
        if (is_array($elements)) {
            return $this->redis->sAdd($key, ...$elements);
        }

        return $this->redis->sAdd($key, $elements);
    }

    /**
     * 元素是否在集合中
     * @param string $key
     * @param string $element
     * @return bool
     * @date 2021.01.25 15:00:48
     */
    private function sismember($key, $element)
    {
        return $this->redis->sIsMember($key, $element);
    }

    /**
     * 从集合中随机删除若干元素并返回
     * @param string $key
     * @param int $count 返回元素个数
     * @return array|bool key非集合类型返回false
     * @date 2021.01.25 15:02:02
     */
    private function spop($key, $count = 1)
    {
        return $this->redis->sPop($key, $count);
    }

    /**
     * 从集合中随机返回若干元素
     * @param string $key
     * @param int $count 返回元素个数 正数最大值等于真实元素个数, 负数返回元素负数绝对值个元素 若元素真实个数不足 则会重复获取元素
     * @return array|bool key非集合类型返回false
     * @date 2021.01.25 15:21:10
     */
    private function srandmember($key, $count = 1)
    {
        return $this->redis->sRandMember($key, $count);
    }

    /**
     * 从集合中删除指定的元素
     * @param string $key
     * @param string|array $elements
     * @return int|boolean 被有效成功移除的元素的数量 key非集合类型返回false
     * @date 2021.01.25 15:36:23
     */
    private function srem($key, $elements)
    {
        if (is_array($elements)) {
            return $this->redis->sRem($key, ...$elements);
        }

        return $this->redis->sRem($key, $elements);
    }

    /**
     * 将集合元素移动到另一个集合中 原子操作
     * @param String $source
     * @param string $destination
     * @param string $element
     * @return bool
     * @date 2021.01.25 15:48:52
     */
    private function smove($source, $destination, $element)
    {
        return $this->redis->sMove($source, $destination, $element);
    }

    /**
     * 统计集合中的元素个数
     * @param string $key
     * @return int
     * @date 2021.01.25 15:55:43
     */
    private function scard($key)
    {
        return $this->redis->sCard($key);
    }

    /**
     * 获取集合中的元素
     * @param string $key
     * @return array
     * @date 2021.01.25 15:58:54
     */
    private function smembers($key)
    {
        return $this->redis->sMembers($key);
    }

    /**
     * 多个集合的交集
     * @param string $set1
     * @param string|array $set2
     * @return array
     * @date 2021.01.25 16:06:23
     */
    private function sintersect($set1, $set2)
    {
        if (is_array($set2)) {
            return $this->redis->sInter($set1, ...$set2);
        }

        return $this->redis->sInter($set1, $set2);
    }

    /**
     * 另存集合的交集
     * @param string $destination 存储位置
     * @param string $source1 集合1
     * @param string|array $source2 集合2~集合n
     * @return bool|int 交集元素个数
     * @date 2021.01.25 16:14:29
     */
    private function sintersectstore($destination, $source1, $source2)
    {
        if (is_array($source2)) {
            return $this->redis->sInterStore($destination, $source1, ...$source2);
        }

        return $this->redis->sInterStore($destination, $source1, $source2);
    }

    /**
     * 计算集合并集
     * @param string $set1
     * @param string|array $set2
     * @return array
     * @date 2021.01.25 16:21:36
     */
    private function sunion($set1, $set2)
    {
        if (is_array($set2)) {
            return $this->redis->sUnion($set1, ...$set2);
        }

        return $this->redis->sUnion($set1, $set2);
    }

    /**
     * 另存集合并集
     * @param string $destination
     * @param string $source1
     * @param string|array $source2
     * @return int 集合中的元素
     * @date 2021.01.25 16:25:49
     */
    private function sunionstore($destination, $source1, $source2)
    {
        if (is_array($source2)) {
            return $this->redis->sUnionStore($destination, $source1, ...$source2);
        }

        return $this->redis->sUnionStore($destination, $source1, $source2);
    }

    /**
     * 计算集合差集 计算在$set1中不在$set2中的元素
     * @param string $set1
     * @param string|array $set2
     * @return array
     * @date 2021.01.25 16:31:52
     */
    private function sdiff($set1, $set2)
    {
        if (is_array($set2)) {
            return $this->redis->sDiff($set1, ...$set2);
        }

        return $this->redis->sDiff($set1, $set2);
    }

    /**
     * 另存计算集合差集 计算在$set1中不在$set2中的元素
     * @param string $destination 另存key
     * @param string $set1
     * @param string|array $set2
     * @return bool|int
     * @date 2021.01.25 16:36:08
     */
    private function sdiffstore($destination, $set1, $set2)
    {
        if (is_array($set2)) {
            return $this->redis->sDiffStore($destination, $set1, ...$set2);
        }

        return $this->redis->sDiffStore($destination, $set1, $set2);
    }

    /**
     *************************** 有序集合类型 *****************************
     * Redis有序集合类型操作
     **********************************************************************
     */

    /**
     * 有序集合添加元素
     * ```php
     * $redis->zadd('players', ['pawn', 'deft', 'chovy', 'faker', 'mata', ['name' => 'kkoma', 'job' => 'coach']]))
     * ```
     * @param $key
     * @param array $data 添加的元素数组
     * @param array $options 额外参数配置
     * @return int 添加的元素个数
     * @date 2021.01.25 17:40:26
     * @see https://redis.io/commands/zadd
     */
    private function zadd($key, $data = [], $options = ['NX'])
    {
        $array = [];
        foreach ($data as $k => $v) {
            $array[] = $k;
            $array[] = is_array($v) ? json_encode($v, self::JSON_ENCODE) : $v;
        }

        return $this->redis->zAdd($key, $options, ...$array);
    }

    /**
     * 获取值在有序集合中的顺序
     * @param $key
     * @param $value
     * @return bool|float|int
     * @date 2021.02.08 17:37:22
     */
    private function zscore($key, $value)
    {
        return $this->redis->zScore($key, $value);
    }

    /**
     * 增减元素的顺序
     * @param string $key
     * @param string $member
     * @param int|float $value 增减步长
     * @return float 新顺序
     * @date 2021.02.08 17:51:02
     */
    private function zincrby($key, $member, $value)
    {
        return $this->redis->zIncrBy($key, $value, $member);
    }

    /**
     * 统计集合中的元素数量
     * @param string $key
     * @return int
     * @date 2021.02.08 18:17:51
     */
    private function zcard($key)
    {
        return $this->redis->zCard($key);
    }

    /**
     * 获取指定区间范围内元素个数
     * @param string $key
     * @param int $start 元素起始基数
     * @param int $end 元素结束基数
     * @return int
     * @date 2021.02.08 18:29:17
     */
    private function zcount($key, $start = 0, $end = -1)
    {
        return $this->redis->zCount($key, $start, $end);
    }

    /**
     * 获取指定区间范围内元素
     * @param string $key
     * @param int $start 开始索引
     * @param int $stop 结束索引
     * @param int $sortBy 排序方式 SORT_DESC 降序 SORT_ASC 升序
     * @param boolean $with_scores 是否返回完成完整元素
     * @return array
     * @date 2021.02.08 18:34:57
     */
    private function zrange($key, $start = 0, $stop = -1, $with_scores = false, $sortBy = SORT_ASC)
    {
        if ($sortBy == SORT_ASC) {
            $body = $this->redis->zRange($key, $start, $stop, $with_scores);
        } else {
            $body = $this->redis->zRevRange($key, $start, $stop, $with_scores);
        }

        if ($with_scores) {
            return array_combine(array_values($body), array_keys($body));
        }

        return $body;
    }
}
