<?php
/**
 *
 * @description 
 *
 * @package     
 *
 * @time        2019-11-24 11:37:31
 *
 * @file  /Users/kovey/Documents/php/meman/user/application/library/Util/Backup.php
 *
 * @author      kovey
 */
namespace Kovey\Util;

use Kovey\Util\Json;

class Backup
{
	private static $dir;

	public static function setDir($dir)
	{
		self::$dir = $dir;
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}
	}

	public static function backup($data, string $category = 'incoming')
	{
		if (!is_string($data)
			&& !is_array($data)
		) {
			return false;
		}

		if (is_array($data)) {
			$data = Json::encode($data);
		}

		return self::write($data, $category);
	}

	public static function write(string $content, string $category)
	{
		if (empty($content)) {
			return false;
		}
		$path = self::$dir . '/' . $category;
		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}

		go (function () use ($content, $path) {
			\Swoole\Coroutine\System::writeFile(
				$path . '/' . date('Y-m-d') . '.log',
				$content . "\n",
				FILE_APPEND
			);
		});

		return true;
	}
}
