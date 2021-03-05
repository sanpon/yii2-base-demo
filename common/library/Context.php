<?php
/**
 * 字符串处理类
 * @author pawn
 * @date 2017年12月17日20:07:30
 */

namespace common\library;

class Context
{
    /**
     * 截取字符串
     * @param string $string
     * @param int $length 截取长度
     * @param string $suffix 超长字符占位符
     * @param int $start 截取起始位置
     * @param string $encoding 截取的编码方式
     * @return string
     * @date 2017年12月17日20:16:41
     * @author pawn
     */
    public static function substring(string $string, int $length, string $suffix = '...', int $start = 0, string $encoding = 'utf-8'): string
    {
        return static::length($string) > $length ? mb_strcut($string, $start, $length, $encoding) . $suffix : $string;
    }

    /**
     * 计算字符串的长度
     * @param string $string 带计算的字符串
     * @param string $encoding 默认 utf-8
     * @return int
     * @date 2017年12月17日20:12:28
     * @author pawn
     */
    public static function length(string $string, string $encoding = 'utf-8'): int
    {
        return mb_strlen($string, $encoding);
    }

    /**
     * 将字符串转为驼峰
     * @param string $string
     * @return string
     * @date 2021.02.26 14:17:29
     */
    public static function camel(string $string): string
    {
        return preg_replace_callback('%-([a-z0-9_])%i', function ($matches) {
            return ucfirst($matches[1]);
        }, ucfirst($string));
    }

    /**
     * 手机号/邮箱星号替换
     * @param string $string
     * @return string
     * @date 2020.07.29 15:27:25
     */
    public static function mosaic(string $string): string
    {
        if (preg_match('/^\d{11}$/', $string)) {
            return preg_replace('/^(\d{3})\d{4}(\d{4})$/', '$1****$2', $string);
        }

        if (preg_match('/^[\da-z][\da-z.]*@[\da-z]+(?:\.[\da-z]+)+$/i', $string)) {
            return preg_replace('/^([\da-z]{1,2}).*(@[\da-z]+(?:\.[\da-z]+)+)$/', '$1****$2', $string);
        }

        return $string;
    }
}
