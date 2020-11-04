<?php
/**
 *
 * @description 
 *
 * @package     
 *
 * @time        2019-12-25 23:56:13
 *
 * @file  /Users/kovey/Documents/php/kovey/bin/command/Util/Show.php
 *
 * @author      kovey
 */
namespace Util;

class Show
{
	public static function show($message)
	{
		echo "$message" . PHP_EOL;
	}

	public static function showFormat($format, ...$args)
	{
		self::show(sprintf($format, ...$args));
	}
}
