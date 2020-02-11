<?php
/**
 *
 * @description 接口对外返回值结构
 *
 * @package     Components\Result
 *
 * @time        Tue Sep 24 09:11:06 2019
 *
 * @class       vendor/Kovey/Components/Result/Error.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Result;

class Error extends Result
{
    public static function getArray($code, $msg, $data = array())
    {
        $res = new self($code, $msg, $data);
        return $res->toArray();
    }

    public static function getJson($code, $msg, $data = array())
    {
        $res = new self($code, $msg, $data);
        return $res->toJson();
    }
}