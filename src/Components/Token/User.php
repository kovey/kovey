<?php
/**
 * @description 用户token
 *
 * @package
 *
 * @author zhayai
 *
 * @time 2020-02-12 10:42:18
 *
 * @file jg-api/application/library/App/Token/User.php
 *
 */
namespace Kovey\Components\Token;

class User
{
    public static function encode(Array $ext, $key, $expire)
    {
        $jwt = new Jwt($key, $expire);
        return $jwt->encode($ext);
    }

    public static function decode($token, $key, $expire)
    {
        $jwt = new Jwt($key, $expire);
        return $jwt->decode($token);
    }
}
