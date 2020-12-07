<?php
/**
 * @description 数据加解密
 *
 * @package
 *
 * @author kovey
 *
 * @time 2019-11-14 21:16:58
 *
 */
namespace Kovey\Rpc\Encryption;

class Encryption
{
    /**
     * @description 加密
     *
     * @param string $data
     *
     * @param string $key
     *
     * @param string $type
     *
     * @param bool isPub
     *
     * @return string | bool
     */
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

    /**
     * @description 解密
     *
     * @param string $data
     *
     * @param string $key
     *
     * @param string $type
     *
     * @param bool isPub
     *
     * @return string | bool
     */
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
