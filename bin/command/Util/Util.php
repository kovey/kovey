<?php
/**
 *
 * @description 
 *
 * @package     
 *
 * @time        2019-12-26 01:09:01
 *
 * @author      kovey
 */
namespace Util;

class Util
{
    public static function copy($source, $dest)
    {
        if (is_file($dest)) {
            return false;
        }

        if (is_file($source)) {
            copy($source, $dest);
            return true;
        }

        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $files = scandir($source);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            self::copy($source . '/' . $file, $dest . '/' . $file);
        }

        return true;
    }
}
