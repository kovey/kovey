<?php
/**
 *
 * @description 全局路由接口
 *
 * @package     Router
 *
 * @time        2019-10-20 00:27:28
 *
 * @file  vendor/Kovey\Web/App/Http/Router/Route.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Http\Router;

use Kovey\Web\App\Application;

class Route
{
	private static $app;

	public static function setApp(Application $app)
	{
		self::$app = $app;
	}

	public static function get(string $uri, $fun = null)
	{
		$router = new Router($uri, $fun);
		self::$app->registerGetRouter($uri, $router);
		return $router;
	}

	public static function post(string $uri, $fun = null)
	{
		$router = new Router($uri, $fun);
		self::$app->registerPostRouter($uri, $router);
		return $router;
	}

	public static function put(string $uri, $fun = null)
	{
		$router = new Router($uri, $fun);
		self::$app->registerPutRouter($uri, $router);
		return $router;
	}

	public static function delete(string $uri, $fun = null)
	{
		$router = new Router($uri, $fun);
		self::$app->registerDelRouter($uri, $router);
		return $router;
	}
}
