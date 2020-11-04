<?php
/**
 *
 * @description 
 *
 * @package     
 *
 * @time        2019-12-17 23:12:22
 *
 * @file  /Users/kovey/Documents/php/kovey/bin/command/main.php
 *
 * @author      kovey
 */

define('KOVEY_TOOLS_COMMAND_DIR', __DIR__);

define('KOVEY_TOOLS_BIN', KOVEY_TOOLS_COMMAND_DIR . '/..');

spl_autoload_register(function ($className) {
	try {
		$className = KOVEY_TOOLS_COMMAND_DIR . '/' . str_replace('\\', '/', $className) . '.php';
		if (!is_file($className)) {
			return;
		}

		require_once $className;
	} catch (\Throwable $e) {
		echo $e->getMessage();
	}
});
