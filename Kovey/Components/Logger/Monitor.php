<?php
/**
 *
 * @description 监控日志
 *
 * @package     
 *
 * @time        2020-01-18 17:48:50
 *
 * @file  /Users/kovey/Documents/php/kovey/Kovey/Components/Logger/Monitor.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Logger;

use Swoole\Coroutine\System;
use Kovey\Util\Json;

class Monitor
{
	private static $logDir;

	public static function setLogDir($logDir)
	{
		self::$logDir = $logDir;
		if (!is_dir($logDir)) {
			mkdir($logDir, 0777, true);
		}
	}

	public static function write(Array $content)
	{
		go (function () use ($content) {
			$content = Json::encode($content);
			System::writeFile(
				self::$logDir . '/' . date('Y-m-d') . '.log',
				$content . "\n",
				FILE_APPEND
			);
		});
	}
}
