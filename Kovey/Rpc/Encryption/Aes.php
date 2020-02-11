<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2019-12-17 20:37:36
 *
 * @file kovey/Kovey\Rpc/Util/Aes.php
 *
 */
namespace Kovey\Rpc\Encryption;

class Aes
{
    public static function encrypt(string $data, $key)
    {
        if (strlen($key) != 43) {
            return false;
        }

        $key = base64_decode($key . "=");
        $iv = substr($key, 0, 16);
        return openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
    }

    public static function decrypt($encrypt, $key)
    {
        if (strlen($key) != 43) {
            return false;
        }

        $key = base64_decode($key . "=");
        $iv = substr($key, 0, 16);
        $clear = openssl_decrypt($encrypt, 'AES-256-CBC', $key, 0, $iv);

        return $clear;
    }
}
