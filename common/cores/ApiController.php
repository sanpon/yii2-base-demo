<?php
/**
 *
 * @author pawN
 * @date 2019.10.02 14:47:40
 */

namespace common\cores;

use Codeception\Util\HttpCode;
use common\library\DynamicModelHelper;
use yii\base\Component;
use yii\helpers\ArrayHelper;

abstract class ApiController extends Component
{
    //是否需要token
    protected $needToken = true;

    //请求Action
    private $action;

    //请求参数
    protected $params;

    //用户ID
    protected $uid;

    //当前路由地址
    public $router;

    /**
     * @return array
     * @author pawN
     * @date 2019.09.30 16:47:18
     */
    protected function rules()
    {
        return [];
    }

    /**
     * @return array
     * @author pawN
     * @date 2019.10.02 12:26:16
     */
    protected function attributeLabels()
    {
        return [];
    }

    /**
     * 任务前置
     * @param $action
     * @return boolean
     * @throws \Exception
     * @date 2019.09.30 14:33:54
     * @author pawN
     */
    public function beforeAction($action)
    {
        $this->action = $action;
        return true;
    }

    /**
     * 执行任务
     * @param ApiController $service
     * @param array $params
     * @return array
     * @throws \Exception
     * @date 2019.09.30 14:33:35
     * @author pawN
     */
    abstract public function runAction($service, $params = []);

    /**
     * 流程结束操作
     * @param $action
     * @param array $result
     * @return array
     * @date 2020.06.12 17:42:29
     */
    public function afterAction($action, $result)
    {
        return $result;
    }

    /**
     * 解析API方法名称
     * @param array $params
     * @return string
     * @date 2020.06.12 17:23:06
     */
    protected function parseRequest($params = [])
    {
        $this->params = $params;

        if (!preg_match('/^(?:before|after).+/i', $this->action)) {
            return 'action' . str_replace('-', '', ucwords(strtolower($this->action), '-'));
        }

        $action = str_replace('-', '', ucwords($this->action, '-'));
        $this->action = preg_replace_callback('/^(before|after)(.+)/i', function ($v) {
            return strtolower($v[1]) . ucfirst($v[2]);
        }, $action);

        return $this->action;
    }

    /**
     * 获取get参数
     * @param $field
     * @param null $default
     * @return mixed
     * @author pawN
     * @throws \Exception
     * @date 2019.09.30 16:28:46
     */
    public function get($field, $default = null)
    {
        return ArrayHelper::getValue($this->params, $field, $default);
    }

    /**
     * 获取post参数
     * @param $field
     * @param null $default
     * @return mixed
     * @throws \Exception
     * @date 2020.06.12 15:37:33
     */
    public function post($field, $default = null)
    {
        return ArrayHelper::getValue($this->params, $field, $default);
    }

    /**
     * @param ApiController $service
     * @throws \Exception
     * @date 2019.10.02 11:40:31
     * @author pawN
     */
    public function validate($service)
    {
        $rules = $service->rules();
        $rules = isset($rules[$this->action]) ? $rules[$this->action] : [];

        /**
         * @var DynamicModelHelper $model
         */
        $model = DynamicModelHelper::validateData($this->params, $rules, $service);
        if ($model->hasErrors()) {
            throw new \Exception($model->getErrorSummary(), HttpCode::BAD_REQUEST);
        }
    }
}