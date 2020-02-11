<?php
/**
 * @description 测试入口
 *
 * @package test
 *
 * @author kovey
 *
 * @time 2019-10-10 14:55:58
 *
 * @file test/autoload.php
 *
 */
define('TEST_ROOT', __DIR__);

spl_autoload_register('autoloader');

function autoloader($class)
{
    $class = str_replace('\\', '/', $class);
    $file = TEST_ROOT . '/' . $class . '.php';
    if (!is_file($file)) {
        return;
    }

    require_once $file;
}
