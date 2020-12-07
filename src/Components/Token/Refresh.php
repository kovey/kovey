<?php
/**
 * @description 刷新token
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-02-12 10:49:18
 *
 */
namespace Kovey\Components\Token;

class Refresh
{
    public static function encode(Array $ext, $key, $expired)
    {
        $jwt = new Jwt($key, $expired);
        return $jwt->encode($ext);
    }

    public static function decode($token, $key, $expired)
    {
        $jwt = new Jwt($key, $expired);
        return $jwt->decode($token);
    }
}
