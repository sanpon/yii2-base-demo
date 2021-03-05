<?php
/**
 * 后台Demo API
 */

namespace backend\entry\entry;

use common\cores\BackendService;

class Demo extends BackendService
{
    /**
     * 参数校验规则
     * @return array
     * @date 2021.02.25 18:34:52
     */
    protected function rules()
    {
        return [
            'index' => [
                [['name', 'age'], 'required', 'message' => \Yii::t('yii', '{attribute} is required')],
                ['name', 'string', 'max' => 5, 'tooLong' => \Yii::t('yii', 'the {attribute} maxlength is {max}')],
                ['age', 'integer']
            ]
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => \Yii::t('yii', 'name'),
            'age' => \Yii::t('yii', 'age')
        ];
    }

    /**
     * 正常消息演示
     * @return array
     * @date 2021.02.25 18:23:22
     */
    public function actionIndex()
    {
        return [
            'message' => \Yii::t('yii', 'Hello Yii2')
        ];
    }

    /**
     * 错误消息演示
     * @throws \Exception
     * @date 2021.02.25 18:23:08
     */
    public function actionError()
    {
        throw new \Exception(\Yii::t('yii', 'Yii2 error message'));
    }
}
