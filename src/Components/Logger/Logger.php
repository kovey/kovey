<?php
/**
 *
 * @description 日志管理类
 *
 * @package     Components\Logger
 *
 * @time        Tue Sep 24 09:06:05 2019
 *
 * @class       vendor/Kovey/Components/Logger/Logger.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Logger;

use Swoole\Coroutine\System;
use Kovey\Util\Json;

class Logger
{
	/**
	 * @description INFO日志路径
	 *
	 * @var string
	 */
	private static $infoPath;

	/**
	 * @description Exception日志路径
	 *
	 * @var string
	 */
	private static $exceptionPath;

	/**
	 * @description Error日志路径
	 *
	 * @var string
	 */
	private static $errorPath;

	/**
	 * @description Warning日志路径
	 *
	 * @var string
	 */
	private static $warningPath;

    /**
     * @description 日志分类
     *
     * @var string
     */
    private static $category;

	/**
	 * @description 设置日志路径
	 *
	 * @param string $info
	 *
	 * @param string $exception
	 *
	 * @param string $error
	 *
	 * @param string $warning
	 *
	 * @return null
	 */
	public static function setLogPath($info, $exception, $error, $warning)
	{
		self::$infoPath = $info;
		if (!is_dir($info)) {
			mkdir($info, 0777, true);
		}

		self::$exceptionPath = $exception;
		if (!is_dir($exception)) {
			mkdir($exception, 0777, true);
		}

		self::$errorPath = $error;
		if (!is_dir($error)) {
			mkdir($error, 0777, true);
		}

		self::$warningPath = $warning;
		if (!is_dir($warning)) {
			mkdir($warning, 0777, true);
		}
	}

	/**
	 * @description 写入日志
	 *
	 * @param int $line
	 *
	 * @param string $file
	 *
	 * @param mixed $msg
	 *
	 * @return Array
	 */
    public static function writeInfoLog($line, $file, $msg)
    {
		go (function ($line, $file, $msg) {
			$content = sprintf("[%s][%s][Info Log]\r\nMessage: [%s]\r\nLine: [%s]\r\nFile: [%s]\r\n", self::$category, date('Y-m-d H:i:s'), is_array($msg) ? Json::encode($msg) : $msg, $line, $file);
			System::writeFile(
				self::$infoPath . '/' . date('Y-m-d') . '.log',
				$content,
				FILE_APPEND
			);
		}, $line, $file, $msg);
    }

	/**
	 * @description 写入错误日志
	 *
	 * @param int $line
	 *
	 * @param string $file
	 *
	 * @param mixed $msg
	 *
	 * @return Array
	 */
    public static function writeErrorLog($line, $file, $msg)
    {
		go (function ($line, $file, $msg) {
			$content = sprintf("[%s][%s][Error Log]\r\nMessage: [%s]\r\nLine: [%s]\r\nFile: [%s]\r\n", self::$category, date('Y-m-d H:i:s'), is_array($msg) ? Json::encode($msg) : $msg, $line, $file);
			System::writeFile(
				self::$errorPath . '/' . date('Y-m-d') . '.log',
				$content,
				FILE_APPEND
			);
		}, $line, $file, $msg);
    }

	/**
	 * @description 写入警告日志
	 *
	 * @param int $line
	 *
	 * @param string $file
	 *
	 * @param mixed $msg
	 *
	 * @return Array
	 */
    public static function writeWarningLog($line, $file, $msg)
    {
		go (function ($line, $file, $msg) {
			$content = sprintf("[%s][%s][Warning Log]\r\nMessage: [%s]\r\nLine: [%s]\r\nFile: [%s]\r\n", self::$category, date('Y-m-d H:i:s'), is_array($msg) ? Json::encode($msg) : $msg, $line, $file);
			System::writeFile(
				self::$warningPath . '/' . date('Y-m-d') . '.log',
				$content,
				FILE_APPEND
			);
		}, $line, $file, $msg);
    }

	/**
	 * @description 写入异常日志
	 *
	 * @param int $line
	 *
	 * @param string $file
	 *
	 * @param mixed $e
	 *
	 * @return Array
	 */
    public static function writeExceptionLog($line, $file, $e)
    {
		go (function ($line, $file, $e) {
			if ($e instanceof \Throwable) {
				$content = sprintf("[%s][%s][Exception Log]\r\nMessage: [%s]\r\nLine: [%s]\r\nFile: [%s]\r\nTrace:\r\n%s\r\n", self::$category, date('Y-m-d H:i:s'), $e->getMessage(), $line, $file, $e->getTraceAsString());
				System::writeFile(
					self::$exceptionPath . '/' . date('Y-m-d') . '.log',
					$content,
					FILE_APPEND
				);
				return;
			}

			$content = sprintf("[%s][%s][Exception Log]\r\nMessage: [%s]\r\nLine: [%s]\r\nFile: [%s]\r\nTrace:\r\n%s\r\n", self::$category, date('Y-m-d H:i:s'), $e['message'], $line, $file, $e['trace']);
			System::writeFile(
				self::$exceptionPath . '/' . date('Y-m-d') . '.log',
				$content,
				FILE_APPEND
			);
		}, $line, $file, $e);
    }

    /**
     * @description 设置日志分类
     *
     * @param string $category
     *
     * @return null
     */
    public static function setCategory(string $category)
    {
        self::$category = $category;
    }
}