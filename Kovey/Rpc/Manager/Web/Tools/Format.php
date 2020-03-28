<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-03-28 11:09:42
 *
 * @file rpc-test/vendor/Kovey/Rpc/Manager/Web/Tools/Format.php
 *
 */
namespace Kovey\Rpc\Manager\Web\Tools;

class Format
{
    public static function exception($message)
    {
        $lines = explode(PHP_EOL, $message);
        array_walk($lines, function(&$line) {
            $line = '<p>' . $line . '</p>';
        });

        return implode('', $lines);
    }
}
