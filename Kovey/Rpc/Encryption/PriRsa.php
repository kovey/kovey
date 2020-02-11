<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2019-12-17 19:57:26
 *
 * @file kovey/Kovey\Rpc/Util/PriRsa.php
 *
 */
namespace Kovey\Rpc\Encryption;

class PriRsa
{
    public static function encrypt(string $data, $key)
    {
        if (is_file($key)) {
            $key = 'file://' . $key;
        }

        if (!openssl_private_encrypt($data, $crypted, $key)) {
            return false;
        }

        return base64_encode($crypted);
    }

    public static function decrypt(string $encrypt, $key)
    {
        if (is_file($key)) {
            $key = 'file://' . $key;
        }

        if (!openssl_private_decrypt(base64_decode($encrypt), $decrypted, $key)) {
            return false;
        }

        return $decrypted;
    }
}
