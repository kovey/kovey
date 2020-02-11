<?php
/**
 *
 * @description 初始化
 *
 * @package     
 *
 * @time        2019-11-16 22:42:00
 *
 * @file  /Users/kovey/Documents/php/kovey/rpc/application/Bootstrap.php
 *
 * @author      kovey
 */
class Bootstrap
{
	public function __initRequired($app)
	{
		$app->registerLocalLibPath(APPLICATION_PATH . '/application');
	}
}
