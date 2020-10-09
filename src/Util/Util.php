<?php
/**
 *
 * @description 一些静态方法封装
 *
 * @package     Util
 *
 * @time        Tue Sep 24 08:51:42 2019
 *
 * @class       vendor/Kovey/Util/Util.php
 *
 * @author      kovey
 */
namespace Kovey\Util;

class Util
{
	/**
	 * @description 数字字母
	 *
	 * @var Array
	 */
	private static $char = array(
		'A', 'a', 'B', 'b', 'C', 'c', 'D', 'd', 'E', 'e', 'F', 'f', 'G', 'g', 'H', 'h', 'I', 'i', 'J', 'j',
		'K', 'k', 'L', 'l', 'M', 'm', 'N', 'n', 'O', 'o', 'P', 'p', 'Q', 'q', 'R', 'r', 'S', 's', 'T', 't',
		'U', 'u', 'V', 'v', 'W', 'w', 'X', 'x', 'Y', 'y', 'Z', 'z', '1', '2', '3', '4', '5', '6', '7', '8',
		'9', '0'
	);

	/**
	 * @description 数字
	 *
	 * @var Array
	 */
	private static $num = array(
		'1', '2', '3', '4', '5', '6', '7', '8', '9', '0'
	);

	/**
	 * @description path判断
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
    public static function isPath($data)
    {
        return (bool)preg_match('/^[a-zA-Z0-9]+$/', $data);
    }

	/**
	 * @description 获取微秒数
	 *
	 * @return float
	 */
	public static function getMicrotime()
	{
		return microtime(true);
	}

	/**
	 * @description 获取随机字符串
	 *
	 * @param int $size
	 *
	 * @return string
	 */
	public static function getRandom($size)
	{
		$rand = '';
		$index = count(self::$char) - 1;
		for ($i = 0; $i < $size; $i ++) {
			$rand .= self::$char[random_int(0, $index)];
		}

		return $rand;
	}

	/**
	 * @description 加密手机号
	 *
	 * @param string $mobile
	 *
	 * @return string
	 */
	public static function encryptMobile($mobile)
	{
		return substr_replace($mobile, '****', 3, 4);
	}

	/**
	 * @description 获取随机数字
	 *
	 * @param int $size
	 *
	 * @return string
	 */
	public static function getRandomNumber($size)
	{
		$rand = '';
		$index = count(self::$num) - 1;
		for ($i = 0; $i < $size; $i ++) {
			$rand .= self::$num[random_int(0, $index)];
		}

		return $rand;
	}

	/**
	 * @description 通过身份证获取年龄
	 *
	 * @param string $idCard
	 *
	 * @return int
	 */
	public static function getUserAge($idCard)
	{
		$year = substr($idCard, 6, 8);
		if ($year === false) {
			return 0;
		}

		return intval((time() - strtotime($year)) / (365 * 24 * 3600));
	}

	/**
	 * @description 通过身份证号获取生日
	 *
	 * @param string $idCard
	 *
	 * @return string
	 */
	public static function getBirthday($idCard)
	{
		$year = substr($idCard, 6, 4);
		if ($year === false) {
			return null;
		}
		$month = substr($idCard, 10, 2);
		if ($month === false) {
			return null;
		}
		$day = substr($idCard, 10, 2);
		if ($day === false) {
			return null;
		}

		return $year . '-' . $month . '-' . $day;
	}

	/**
	 * @description 转换身份证号
	 *
	 * @param string $idCard
	 *
	 * @return string
	 */
	public static function convertIdCard($idCard) 
	{
		if (15 == strlen($idCard)) {
			$W = array (7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2,1);
			$A = array ("1","0","X","9","8","7","6","5","4","3","2");
			$s = 0;
			$idCard18 = substr($idCard, 0, 6) . "19" . substr($idCard, 6);
			$idCard18_len = strlen($idCard18);
			for ($i = 0; $i < $idCard18_len; $i ++) {
				$s = $s + substr($idCard18, $i, 1) * $W[$i];
			}
			$idCard18 .= $A[$s % 11];
			return $idCard18;
		}

		return $idCard;
	}

	/**
	 * @description 判断是否是数字
	 *
	 * @param mixed $num
	 *
	 * @return bool
	 */
    public static function isNumber($num)
    {
        return ctype_digit(strval($num));
    }
}
