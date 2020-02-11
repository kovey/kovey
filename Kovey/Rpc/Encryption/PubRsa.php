<?php
/**
 * @description RSA加解密
 *
 * @package Kovey\Rpc\Util
 *
 * @author kovey
 *
 * @time 2019-12-17 19:38:49
 *
 * @file kovey/Kovey\Rpc/Util/Rsa.php
 *
 */
namespace Kovey\Rpc\Encryption;

class PubRsa
{
    public static function encrypt(string $data, $key)
    {
        if (is_file($key)) {
            $key = 'file://' . $key;
        }

        if (!openssl_public_encrypt($data, $crypted, $key)) {
            return false;
        }

        return base64_encode($crypted);
    }

    public static function decrypt(string $encrypt, $key)
    {
        if (is_file($key)) {
            $key = 'file://' . $key;
        }

        if (!openssl_public_decrypt(base64_decode($encrypt), $decrypted, $key)) {
            return false;
        }

        return $decrypted;
    }
}
