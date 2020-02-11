<?php
/**
 *
 * @description kovey framework 入口文件，请勿更改
 *
 * @package     Kovey
 *
 * @time        Tue Sep 24 00:27:20 2019
 *
 * @class       vendor/kovey.php
 *
 * @author      kovey
 */
if (!extension_loaded('swoole')) {
	echo "\n" . 'swoole extension not install!' . "\n"
		. 'kovey framwork base on swoole 4.4.x!' . "\n"
		. 'please install swoole-4.4.x first!' . "\n";
	exit;
}

function ko_change_process_name($name)
{
	if (ko_os_is_macos()) {
		return;
	}

    swoole_set_process_name($name);
}

function ko_os_is_macos()
{
	return stristr(PHP_OS, 'DAR') !== false;
}

function ko_os_is_linux()
{
	return stristr(PHP_OS, 'LINUX') !== false;
}

function ko_os_is_windows()
{
	return !ko_os_is_macos() && stristr(PHP_OS, 'WIN') !== false;
}

if (!defined('KOVEY_FRAMEWORK_PATH')) {
	define('KOVEY_FRAMEWORK_PATH', __DIR__);
}

if (!defined('KOVEY_RPC_ROOT')) {
    define('KOVEY_RPC_ROOT', __DIR__);
}

if (!defined('KOVEY_RPC_CONFIG_ROWS')) {
	define('KOVEY_RPC_CONFIG_ROWS', 1024);
}

if (!defined('APPLICATION_PATH')) {
	define('APPLICATION_PATH', __DIR__ . '/..');
}
