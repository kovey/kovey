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

class Logger
{
	private static $infoPath;

	private static $exceptionPath;

	private static $errorPath;

	private static $warningPath;

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

	private static function getLog($line, $file, $msg, $type)
	{
		return array('type' => $type, 'line' => $line, 'file' => $file, 'msg' => $msg);
	}

    public static function getInfoLog($line, $file, $msg)
	{
		return self::getLog($line, $file, $msg, 'info');
    }

    public static function getWarningLog($line, $file, $msg)
    {
		return self::getLog($line, $file, $msg, 'warning');
    }

    public static function getErrorLog($line, $file, $msg)
    {
		return self::getLog($line, $file, $msg, 'error');
    }

    public static function getExceptionLog($line, $file, \Throwable $e)
    {
        return array('type' => 'exception', 'e' => array(
            'trace' => $e->getTraceAsString(),
            'message' => $e->getMessage()
        ), 'line' => $line, 'file' => $file, 'msg' => $e->getMessage());
    }

    public static function writeInfoLog($line, $file, $msg)
    {
		go (function () use ($line, $file, $msg) {
			$content = sprintf("[%s][Info Log]\r\nMessage: [%s]\r\nLine: [%s]\r\nFile: [%s]\r\n", date('Y-m-d H:i:s'), $msg, $line, $file);
			System::writeFile(
				self::$infoPath . '/' . date('Y-m-d') . '.log',
				$content,
				FILE_APPEND
			);
		});
    }

    public static function writeErrorLog($line, $file, $msg)
    {
		go (function () use ($line, $file, $msg) {
			$content = sprintf("[%s][Error Log]\r\nMessage: [%s]\r\nLine: [%s]\r\nFile: [%s]\r\n", date('Y-m-d H:i:s'), $msg, $line, $file);
			System::writeFile(
				self::$errorPath . '/' . date('Y-m-d') . '.log',
				$content,
				FILE_APPEND
			);
		});
    }

    public static function writeWarningLog($line, $file, $msg)
    {
		go (function () use ($line, $file, $msg) {
			$content = sprintf("[%s][Warning Log]\r\nMessage: [%s]\r\nLine: [%s]\r\nFile: [%s]\r\n", date('Y-m-d H:i:s'), $msg, $line, $file);
			System::writeFile(
				self::$warningPath . '/' . date('Y-m-d') . '.log',
				$content,
				FILE_APPEND
			);
		});
    }

    public static function writeExceptionLog($line, $file, $e)
    {
		go (function () use ($line, $file, $e) {
			if ($e instanceof \Throwable) {
				$content = sprintf("[%s][Exception Log]\r\nMessage: [%s]\r\nLine: [%s]\r\nFile: [%s]\r\nTrace:\r\n%s\r\n", date('Y-m-d H:i:s'), $e->getMessage(), $line, $file, $e->getTraceAsString());
				System::writeFile(
					self::$exceptionPath . '/' . date('Y-m-d') . '.log',
					$content,
					FILE_APPEND
				);
				return;
			}

			$content = sprintf("[%s][Exception Log]\r\nMessage: [%s]\r\nLine: [%s]\r\nFile: [%s]\r\nTrace:\r\n%s\r\n", date('Y-m-d H:i:s'), $e['message'], $line, $file, $e['trace']);
			System::writeFile(
				self::$exceptionPath . '/' . date('Y-m-d') . '.log',
				$content,
				FILE_APPEND
			);
		});
    }
}
