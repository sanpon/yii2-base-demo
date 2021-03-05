<?php
/**
 * 基础模型用于前后端共享
 * @author pawn
 * @date
 */

namespace common\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class Model extends ActiveRecord
{
    /**
     * @param bool $showAllErrors
     * @return string
     * @author pawN
     * @date 2019.10.02 14:37:49
     */
    public function getErrorSummary($showAllErrors = true)
    {
        return implode('、', parent::getErrorSummary($showAllErrors));
    }

    /**
     * 分页计算器
     * @param ActiveQuery|Query $query
     * @param \Closure|null $callback
     * @return array
     * @throws \Exception
     * @date 2020.06.12 14:42:03
     */
    public static function pagination($query, \Closure $callback = null)
    {
        $data = $_REQUEST;

        //偏移量
        $offset = (int)ArrayHelper::getValue($data, 'offset', 0);

        //单页数据量
        $pageSize = (int)ArrayHelper::getValue($data, 'size', 20);

        $countQuery = clone $query;
        $total = $countQuery->count();
        $query->offset($offset * $pageSize)->limit($pageSize);

        $list = $query instanceof ActiveQuery ? $query->asArray()->all() : $query->all();

        if ($list && $callback) {
            $list = $callback($list);
        }

        return [
            'list' => $list,
            'total' => (int)$total,
            'size' => (int)$pageSize,
            'next_page' => ($offset + 1) < ceil($total / $pageSize)
        ];
    }
}
