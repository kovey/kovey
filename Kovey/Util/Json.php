<?php
/**
 *
 * @description 简单的JSON封装
 *
 * @package     Util
 *
 * @time        Tue Sep 24 08:52:33 2019
 *
 * @class       vendor/Kovey/Util/Json.php
 *
 * @author      kovey
 */
namespace Kovey\Util;

class Json
{
	public static function encode($data)
	{
		return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	public static function decode($data)
	{
		return json_decode($data, true);
	}
}
