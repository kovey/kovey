<?php
/**
 *
 * @description kovey framework 入口文件，请勿更改
 *
 * @package     Kovey
 *
 * @time        Tue Sep 24 00:27:20 2019
 *
 * @author      kovey
 */
if (!extension_loaded('swoole')) {
    echo PHP_EOL . 'swoole extension not install!' . PHP_EOL
        . 'kovey framwork base on swoole 4.4.x!' . PHP_EOL
        . 'please install swoole-4.4.x first!' . PHP_EOL;
    exit;
}

/**
 * @description 修改进程名称，macos不允许修改进程名称
 *
 * @param string $name
 *
 * @return null
 */
function ko_change_process_name($name)
{
    if (ko_os_is_macos()) {
        return;
    }

    swoole_set_process_name($name);
}

/**
 * @description 判断当前系统是否是mac
 *
 * @return bool
 */
function ko_os_is_macos()
{
    return stristr(PHP_OS, 'DAR') !== false;
}

/**
 * @description 判断系统是否是linux
 *
 * @return bool
 */
function ko_os_is_linux()
{
    return stristr(PHP_OS, 'LINUX') !== false;
}

/**
 * @description 判断系统是否是windows
 *
 * @return bool
 */
function ko_os_is_windows()
{
    return !ko_os_is_macos() && stristr(PHP_OS, 'WIN') !== false;
}

// 定义框架路径
if (!defined('KOVEY_FRAMEWORK_PATH')) {
    define('KOVEY_FRAMEWORK_PATH', __DIR__);
}

// 定义配置ROW
if (!defined('KOVEY_FRAMEWORK_CONFIG_ROWS')) {
    define('KOVEY_FRAMEWORK_CONFIG_ROWS', 1024);
}

if (!defined('KOVEY_CONFIG_MAX_ROWS')) {
    define('KOVEY_CONFIG_MAX_ROWS', 1024);
}

// 如果未定义应用路径，则默认为上级目录
if (!defined('APPLICATION_PATH')) {
    define('APPLICATION_PATH', __DIR__ . '/..');
}
