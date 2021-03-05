<?php
/**
 * 验证码类
 */
namespace common\services;

use common\library\Captcha;
use yii\base\Component;

class CaptchaService extends Component
{
    /**
     * 获取验证码
     * @return array
     * @throws \Exception
     * @date 2020.07.12 10:34:02
     */
    public static function getVerifyCode()
    {
        $captcha = new Captcha();
        return $captcha->getCode();
    }

    /**
     * 验证验证码
     * @param $input
     * @param bool $caseSensitive
     * @throws \Exception
     * @return \stdClass
     * @date 2020.07.12 10:15:28
     */
    public static function validate($input, $caseSensitive = false)
    {
        $captcha = new Captcha();
        return $captcha->validate($input, $caseSensitive);
    }
}