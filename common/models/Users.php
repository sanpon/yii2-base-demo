<?php
/**
 * 后台用户
 */

namespace common\models;

use library\core\Context;
use library\core\Time;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * Class User
 * @package common\models
 * @property int $id
 * @property string $username
 * @property string $nickname
 * @property string $password
 * @property string $email
 * @property string $created_at
 * @property string $token 客户端固定token
 */
class Users extends Model
{
    //登录信息有效期
    const EXPIRE = 86400;

    //登录token最大有效时长
    const MAX_EXPIRE = 86400;

    //登录token刷新倒计时 token刷新前倒计时
    const REFRESH_EXPIRE = 120;

    //账号状态 1 正常 2 禁用
    const STATUS_NORMAL = 1;
    const STATUS_FORBIDDEN = 2; //禁用

    //更新用户信息
    const SCENARIO_UPDATE = 'update';

    //更新用户密码
    const SCENARIO_PWD = 'pwd';

    public static function tableName()
    {
        return '{{%users}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at']
                ]
            ]
        ];
    }

    public function rules()
    {
        return [
            [['username', 'email', 'password'], 'required', 'message' => '缺少{attribute}'],
            [['username', 'password', 'email'], 'filter', 'filter' => function ($attribute) {
                return trim($attribute);
            }],
            [['username', 'nickname'], 'string', 'max' => 30, 'message' => '{attribute}不能超过{max}'],
            ['nickname', 'default', 'value' => function () {
                return $this->username;
            }],
            ['username', 'unique', 'filter' => function ($query) {
                return $this->validateUser($query);
            }, 'message' => '{attribute}已经存在'],
            ['email', 'email', 'message' => '{attribute}格式不正确'],
            ['email', 'unique', 'filter' => function ($query) {
                return $this->validateEmail($query);
            }, 'message' => '{attribute}已经存在'],
            [['password', 'token'], 'string', 'min' => 6, 'max' => 255, 'message' => '{attribute}为{min}~{max}个字符'],
            [['password'], 'filter', 'filter' => function () {
                return password_hash($this->password, PASSWORD_DEFAULT);
            }],
            ['token', 'filter', 'filter' => function () {
                return substr(md5($this->password), 4);
            }],
            ['status', 'default', 'value' => function () {
                return self::STATUS_NORMAL;
            }]
        ];
    }

    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            self::SCENARIO_UPDATE => ['username', 'nickname', 'email', 'status'],
            self::SCENARIO_PWD => ['password', 'token']
        ]);

    }

    /**
     * 用户名是否存在
     * @param Query $query
     * @return boolean
     * @date 2020.10.09 15:35:24
     */
    public function validateUser($query)
    {
        if (!$this->id) {
            $number = $query->where(['username' => $this->username])->count();
            return $number < 1;
        }

        $userId = $query->select(['id'])->where(['username' => $this->username])->scalar();
        return $userId == $this->id;
    }

    /**
     * @param Query $query
     * @return bool
     * @date 2020.10.09 16:39:37
     */
    public function validateEmail($query)
    {
        if (!$this->id) {
            $number = $query->where(['email' => $this->email])->count();
            return $number < 1;
        }

        $userId = $query->select(['id'])->where(['email' => $this->email])->scalar();
        return $userId == $this->id;
    }
}
