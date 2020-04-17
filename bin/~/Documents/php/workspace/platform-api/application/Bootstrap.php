<?php
/**
 *
 * @description bootstrap 
 *
 * @package     web
 *
 * @time       Tue Sep 24 00:23:45 2019
 *
 * @class      application/Bootstrap.php
 *
 * @author     kovey
 */

class Bootstrap
{
	public function __initLayout($app)
	{
		$app->registerPlugin('Layout');
	}
}
