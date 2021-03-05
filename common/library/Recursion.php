<?php
/**
 * 递归处理类
 * @author pawn
 * @date 2017年12月15日01:06:39
 */

namespace common\library;

class Recursion
{
    /**
     * 查询子孙树
     * @param array $input 递归数据
     * @param string $pk 递归键名
     * @param int $pid 递归的起点
     * @param int $level 当前分类层级
     * @param string $key 递归父类键名
     * @return array
     * @date 2017年12月14日22:52:38
     * @author pawn
     */
    public static function sons(array $input, $pk = 'id', $pid = 0, $level = 1, $key = 'pid')
    {
        $output = [];
        foreach ($input as $val) {
            if ($val[$key] == $pid) {
                $val['level'] = $level;
                $output[] = $val;
                $output = array_merge($output, static::sons($input, $pk, $val[$pk], $level + 1, $key));
            }
        }

        return $output;
    }

    /**
     * 查询家谱树
     * @param string $pk 递归键名
     * @param array $input 递归的数据
     * @param int $id 递归的起点【谁的家谱树】
     * @param string $key 递归父类键名
     * @return array
     * @date 2017年12月14日23:07:51
     * @author pawn
     */
    public static function family($pk, array $input = [], $id = 0, $key = 'pid')
    {
        $family = [];
        foreach ($input as $val) {
            if ($val[$pk] == $id) {
                $family[] = $val;
                $family = array_merge(static::family($pk, $input, $val[$key], $key), $family);
            }
        }
        return $family;
    }

    /**
     * 获取子级节点
     * @param array $input 目标查询数据
     * @param int $id 子级节点的父级ID
     * @param string $key 父级键名
     * @return array
     * @date 2017年12月15日01:11:53
     * @author pawn
     */
    public static function children(array $input = [], $id = 0, $key = 'pid')
    {
        $sons = [];
        foreach ($input as $val) {
            if ($val[$key] == $id) {
                $sons[] = $val;
            }
        }
        return $sons;
    }

    /**
     * 无限级嵌套树
     * @param $data
     * @param int $level
     * @param int $id
     * @return array
     * @date 2018.10.25 10:59:16
     */
    public static function subtrees($data, $level = 1, $id = 0)
    {
        $ret = [];
        foreach ($data as $key => $val) {
            if ($val['level'] == $level && $val['pid'] == $id) {
                $data[$key]['children'] = static::subtrees($data, $level + 1, $val['id']);
                if (empty($data[$key]['children'])) {
                    unset($data[$key]['children']);
                }
                $ret[] = $data[$key];
            }
        }
        return $ret;
    }
}
