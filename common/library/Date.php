<?php
/**
 * 日期处理函数
 */

namespace common\library;

class Date
{
    /**
     * 时间戳格式化
     * @param string $format
     * @param int $timestamp
     * @return string
     * @date 2020.07.16 17:33:41
     */
    public static function format(int $timestamp = null, string $format = 'Y-m-d H:i:s'): string
    {
        if ($timestamp === null) {
            $timestamp = time();
        }
        return $timestamp ? date($format, $timestamp) : '';
    }
}