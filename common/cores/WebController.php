<?php
/**
 * 基础控制器
 * @author: pawN
 * @date 2018.12.01 12:52:03
 */

namespace common\cores;

use yii\base\Controller;
use yii\web\Application;

class WebController extends Controller
{
    public $layout = false;

    //加载模块页面
    public function render($view = '', $params = [])
    {
        /**
         * @var \stdClass $module
         */
        $module = $this->module;
        if ($module instanceof Application) {
            return parent::render("//{$view}", $params);
        }

        return parent::render("@{$module->belong}/views/{$module->name}/{$view}", $params);
    }
}