<?php
/**
 * 公共服务入口
 */
namespace common\entry;

use common\cores\CommonService;
use common\services\CaptchaService;

class Captcha extends CommonService
{
    protected $needToken = false;

    /**
     * @return array
     * @throws \Exception
     * @date 2020.10.25 15:01:58
     */
    public function actionIndex()
    {
        //token 算法
        //var_dump(substr(md5('$2y$10$jQrficXrDigC/8kJ8idCLeJFctBOzY6sjm2SfsQnP7Pxe2j5YIt5O'), 4));die;
        $captcha = CaptchaService::getVerifyCode();

        return [
            'src' => $captcha['img'],
            '_t' => $captcha['session_id']
        ];
    }
}