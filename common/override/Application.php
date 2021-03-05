<?php
/**
 * 重写组件
 * @property \common\library\Redis $redis The user component. This property is read-only.
 */
namespace common\override;

class Application extends \yii\web\Application
{
    /**
     * @return object|null
     * @throws \Exception
     * @date 2021.03.05 10:46:04
     */
    public function getRedis()
    {
        return $this->get('redis');
    }

    public function coreComponents()
    {
        $components = parent::coreComponents();
        unset($components['assetManager']);
        return  $components;
    }
}