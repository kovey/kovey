<?php
/**
 *
 * @description 路由接口
 *
 * @package     Router
 *
 * @time        2019-10-17 23:27:59
 *
 * @file  vendor/Kovey\Web/App/Http/Router/RouterInterface.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Http\Router;

interface RoutersInterface
{
	public function getRouter(string $uri, string $method) : ? RouterInterface;

	public function isUri(string $uri) : bool;

	public function defaultRoute(string $uri) : ? RouterInterface;

	public function get(string $uri, RouterInterface $router) : RoutersInterface;

	public function post(string $uri, RouterInterface $router) : RoutersInterface;

	public function put(string $uri, RouterInterface $router) : RoutersInterface;

	public function delete(string $uri, RouterInterface $router) : RoutersInterface;

	public function disableDefault();
}
