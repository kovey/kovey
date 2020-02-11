<?php
/**
 *
 * @description 接口成功时的返回值
 *
 * @package     Components\Result
 *
 * @time        Tue Sep 24 09:12:43 2019
 *
 * @class       vendor/Kovey/Components/Result/Success.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Result;

class Success extends Result
{
    public static function getArray($data = array())
    {
        $res = new self(ErrorCode::SUCCESS, '成功', $data);
        return $res->toArray();
    }

    public static function getJson($data = array())
    {
        $res = new self(ErrorCode::SUCCESS, '成功', $data);
        return $res->toJson();
    }
}
