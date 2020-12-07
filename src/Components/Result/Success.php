<?php
/**
 *
 * @description 接口成功时的返回值
 *
 * @package     Components\Result
 *
 * @time        Tue Sep 24 09:12:43 2019
 *
 * @author      kovey
 */
namespace Kovey\Components\Result;

class Success extends Result
{
    /**
     * @description 获取成功结果数据
     *
     * @param mixed $data
     *
     * @return Array
     */
    public static function getArray($data = array())
    {
        $res = new self(ErrorCode::SUCCESS, '成功', $data);
        return $res->toArray();
    }

    /**
     * @description 获取成功结果数据JSON
     *
     * @param mixed $data
     *
     * @return string
     */
    public static function getJson($data = array())
    {
        $res = new self(ErrorCode::SUCCESS, '成功', $data);
        return $res->toJson();
    }
}
