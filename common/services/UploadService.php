<?php
/**
 * 上传组件
 */

namespace common\services;

use yii\base\Component;

class UploadService extends Component
{
    //默认上传KEY
    const DEFAULT_NAME = 'file';

    //基础文件类型
    private static $commonMimes = [
        'image/jpeg' => '.jpg',
        'image/jpg' => '.jpg',
        'image/png' => '.png',
        'image/gif' => '.gif'
    ];

    //模块独立支持的格式
    private static $mimes = [
        'avatar' => []
    ];

    /**
     * 头像上传
     * @param $uid
     * @param $module
     * @param $name
     * @throws \Exception
     * @return array
     * @date 2020.07.31 15:38:50
     */
    public static function avatar($uid, $module, $name = self::DEFAULT_NAME)
    {
        $file = $_FILES[$name];
        $r = static::validFile($file, $module);
        $symbol = str_pad($uid, 9, 0, STR_PAD_LEFT);
        $symbol = preg_replace('/(\d{1,3})(?=(?:\d{2})+$)/', "$0/", $symbol);
        $fileName = "/ucenter/avatar/{$symbol}_origin{$r->suffix}";
        $path = \Yii::getAlias('@images').$fileName;
        if (!(dirname($path))) {
            mkdir(dirname($path), 0733, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $path)) {
            throw new \Exception('上传失败');
        }

        return ['message' => '上传成功'];

    }

    /**
     * 校验文件是否合法
     * @param $file
     * @param string $module
     * @return \stdClass
     * @date 2020.07.31 15:30:56
     * @throws \Exception
     */
    private static function validFile($file, $module)
    {
        if (empty($file) || !is_uploaded_file($file['tmp_name'])) {
            throw new \Exception('抱歉,没有上传文件');
        }

        $errors = [
            UPLOAD_ERR_INI_SIZE => '上传文件超出限制1',
            UPLOAD_ERR_FORM_SIZE => '上传文件超出限制2',
            UPLOAD_ERR_PARTIAL => '上传数据不完整',
            UPLOAD_ERR_NO_FILE => '未找到上传文件',
            UPLOAD_ERR_NO_TMP_DIR => '上传失败1',
            UPLOAD_ERR_CANT_WRITE => '上传失败2'
        ];
        if (isset($errors[$file['error']])) {
            throw new \Exception($errors[$file['error']]);
        }

        if (!isset(static::$mimes[$module])) {
            throw new \Exception('抱歉,未支持的模块');
        }

        $mimes = isset(static::$mimes[$module]['mime']) ? static::$mimes[$module]['mime'] : [];

        $mimes = array_merge(static::$commonMimes, $mimes);
        if (!isset($mimes[$file['type']])) {
            throw new \Exception('抱歉,未支持的文件类型');
        }

        $std = new \stdClass();
        $std->suffix = $mimes[$file['type']];

        return $std;
    }
}