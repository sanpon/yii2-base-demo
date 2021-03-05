<?php
/**
 * 图形验证码
 */

namespace common\library;

use yii\base\Controller;
use yii\captcha\CaptchaAction;
use yii\helpers\Json;

class Captcha extends CaptchaAction
{
    public $width = 100;
    public $height = 40;
    public $minLength = 5;
    public $maxLength = 5;
    public $backColor = 0xDDDDDD;
    public $offset = -1;

    /**
     * @var string $captcha 存储验证码的key
     */
    private $cookie = 'did';

    /**
     * @var int $expire 验证码有效期 单位 分钟
     */
    private $expire = 10;

    /**
     * Captcha constructor.
     * @param $id
     * @param $controller
     * @param array $config
     * @return mixed
     * @throws \Exception
     */
    public function __construct($id = 'captcha', $controller = '', $config = [])
    {
        /**
         * @var Controller $_t
         */
        $_t = CaptchaAction::class;
        parent::__construct($id, $_t, $config);
    }

    /**
     * 生成验证码
     * @param boolean $imageCode 是否图形验证码
     * @return array
     * @throws \Exception
     * @date 2020.07.07 17:22:02
     */
    public function getCode($imageCode = true)
    {
        $code = $this->generateVerifyCode();

        $img = $imageCode ? $this->renderImage($code) : '';

        $session = \Yii::$app->getSession();
        $session_id = \Yii::$app->getRequest()->getHeaders()->get($this->cookie, null);
        if ($session_id !== 'undefined' && $session_id) {
            $session->setId($session_id);
        }

        $session->open();
        $session_id = $session->getId();
        $data = [
            'code' => $code,
            'expire' => strtotime("+{$this->expire}min")
        ];

        $session->set($session_id, json_encode($data));
        $session->close();

        return [
            'code' => $code,
            'session_id' => $session_id,
            'img' => $imageCode ? 'data:image/png;base64,' . base64_encode($img) : ''
        ];
    }

    /**
     * 验证码校验
     * @param string $input 客户端输入的验证码
     * @param bool $caseSensitive
     * @return \stdClass
     * @throws \Exception
     * @date 2020.07.11 11:38:33
     */
    public function validate($input, $caseSensitive = false)
    {
        $session_id = \Yii::$app->getRequest()->getHeaders()->get($this->cookie);
        $session = \Yii::$app->getSession();
        $session->setId($session_id);
        $session->open();
        $captcha = $session->get($session_id);

        $std = new \stdClass();
        $std->status = false;
        $std->session = $session;
        $std->captcha = $this;
        if (empty($input) || empty($captcha)) {
            return $std;
        }

        $captcha = Json::decode($captcha);
        $valid = $caseSensitive ? $captcha['code'] === $input : strcasecmp($captcha['code'], $input) === 0;

        if ($valid && time() <= $captcha['expire']) {
            $std->status = true;
            $std->session_id = $session_id;
            return $std;
        }

        return $std;
    }
}