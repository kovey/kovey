<?php
/**
 *
 * @description 事件日志，包括请求等
 *
 * @package     
 *
 * @time        2020-01-18 17:56:17
 *
 * @author      kovey
 */
namespace Kovey\Components\Logger;

use Swoole\Coroutine\System;

class Db
{
    /**
     * @description 日志mulu
     *
     * @var string
     */
    private static $logDir;

    /**
     * @description 设置日志目录
     *
     * @param string $logDir
     */
    public static function setLogDir($logDir)
    {
        self::$logDir = $logDir;
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
    }

    /**
     * @description 写入日志
     *
     * @param string $sql
     *
     * @param float $spentTime
     */
    public static function write($sql, $spentTime)
    {
        go (function (string $sql, float $spentTime) {
            $spentTime = round($spentTime * 1000, 2) . 'ms';
            $content = array(
                'time' => date('Y-m-d H:i:s'),
                'sql' => $sql,
                'delay'  => $spentTime
            );
            System::writeFile(
                self::$logDir . '/' . date('Y-m-d') . '.log',
                json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL,
                FILE_APPEND
            );
        }, $sql, $spentTime);
    }
}
