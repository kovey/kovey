<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2019-11-14 21:16:58
 *
 * @file kovey/Kovey\Rpc/Util/Encryption.php
 *
 */
namespace Kovey\Rpc\Encryption;

class Encryption
{
    public static function encrypt(string $data, $key, $type = 'aes', $isPub = false)
    {
        if (strtolower($type) === 'aes') {
            return Aes::encrypt($data, $key);
        }

        if (strtolower($type) === 'rsa') {
            if ($isPub) {
                return PubRsa::encrypt($data, $key);
            }

            return PriRsa::encrypt($data, $key);
        }

        return false;
    }

    public static function decrypt(string $data, $key, $type = 'aes', $isPub = false)
    {
        if (strtolower($type) === 'aes') {
            return Aes::decrypt($data, $key);
        }

        if (strtolower($type) === 'rsa') {
            if ($isPub) {
                return PubRsa::decrypt($data, $key);
            }

            return PriRsa::decrypt($data, $key);
        }

        return false;
    }
}