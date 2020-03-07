<?php
/**
 * @description RSA 私钥解密
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
	/**
	 * @description 加密
	 *
	 * @param string $data
	 *
	 * @param string $key
	 *
	 * @return string | bool
	 */
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

	/**
	 * @description 解密
	 *
	 * @param string $encrypt
	 *
	 * @param string $key
	 *
	 * @return string | bool
	 */
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
