<?php
/**
 *
 * @description 日志管理类
 *
 * @package     Components\Logger
 *
 * @time        Tue Sep 24 09:06:05 2019
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
    private static $category = '';

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
    public static function writeInfoLog($line, $file, $msg, $traceId = '')
    {
        go (function ($line, $file, $msg, $traceId) {
            $content = array(
                'time' => date('Y-m-d H:i:s'),
                'category' => self::$category,
                'type' => 'Info',
                'message' => $msg,
                'trace' => '',
                'line' => $line,
                'file' => $file,
                'traceId' => $traceId
            );
            System::writeFile(
                self::$infoPath . '/' . date('Y-m-d') . '.log',
                json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL,
                FILE_APPEND
            );
        }, $line, $file, $msg, $traceId);
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
    public static function writeErrorLog($line, $file, $msg, $traceId = '')
    {
        go (function ($line, $file, $msg, $traceId) {
            $content = array(
                'time' => date('Y-m-d H:i:s'),
                'category' => self::$category,
                'type' => 'Error',
                'message' => $msg,
                'trace' => '',
                'line' => $line,
                'file' => $file,
                'traceId' => $traceId
            );
            System::writeFile(
                self::$errorPath . '/' . date('Y-m-d') . '.log',
                json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
                FILE_APPEND
            );
        }, $line, $file, $msg, $traceId);
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
    public static function writeWarningLog($line, $file, $msg, $traceId = '')
    {
        go (function ($line, $file, $msg, $traceId) {
            $content = array(
                'time' => date('Y-m-d H:i:s'),
                'category' => self::$category,
                'type' => 'Warning',
                'message' => $msg,
                'trace' => '',
                'line' => $line,
                'file' => $file,
                'traceId' => $traceId
            );
            System::writeFile(
                self::$warningPath . '/' . date('Y-m-d') . '.log',
                json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL,
                FILE_APPEND
            );
        }, $line, $file, $msg, $traceId);
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
    public static function writeExceptionLog($line, $file, $e, $traceId = '')
    {
        go (function ($line, $file, $e, $traceId) {
            $content = array(
                'time' => date('Y-m-d H:i:s'),
                'category' => self::$category,
                'type' => 'Exception',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $line,
                'file' => $file,
                'traceId' => $traceId
            );
            System::writeFile(
                self::$exceptionPath . '/' . date('Y-m-d') . '.log',
                json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
                FILE_APPEND
            );
        }, $line, $file, $e, $traceId);
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
