<?php
/**
 *
 * @author pawN
 * @date 2019.09.30 18:44:43
 */

namespace common\library;

use entry\controllers\ApiController;
use yii\base\DynamicModel;

class DynamicModelHelper extends DynamicModel
{
    /**
     * @var ApiController $service
     */
    private static $service;

    /**
     * 数据校验
     * @param array $data
     * @param array $rules
     * @param ApiController $service
     * @return DynamicModel
     * @throws \Exception
     * @author pawN
     * @date 2019.10.02 12:40:40
     */
    public static function validateData(array $data, $rules = [], $service = null): DynamicModel
    {
        static::$service = $service;

        return parent::validateData(static::generateAttributes($data, $rules), $rules);
    }


    /**
     * 生成属性
     * @param array $data
     * @param array $rules
     * @return array
     * @date 2019.10.02 12:10:31
     * @author pawN
     */
    public static function generateAttributes(array $data, array $rules): array
    {
        $attributes = [];

        foreach ($rules as $rule) {
            if (is_array($rule[0])) {
                $attributes = array_merge($rule[0]);
            } else {
                array_push($attributes, $rule[0]);
            }
        }

        $attributes = array_flip($attributes);
        foreach ($attributes as $key => $attribute) {
            $attributes[$key] = isset($data[$key]) ? $data[$key] : null;
        }

        return $attributes;
    }

    /**
     * 设置属性
     * @return array
     * @author pawN
     * @date 2019.10.02 12:20:02
     */
    public function attributeLabels(): array
    {
        return method_exists(static::$service, 'attributeLabels') ? static::$service->attributeLabels() : [];
    }

    /**
     * 获取模型错误消息
     * @param bool $showAllErrors
     * @return string
     * @author pawN
     * @date 2019.10.02 14:19:36
     */
    public function getErrorSummary($showAllErrors = true): string
    {
        return implode('、', parent::getErrorSummary($showAllErrors));
    }
}
