<?php
/**
 *
 * @description 路由器
 *
 * @package     App\Http\Router
 *
 * @time        Tue Sep 24 08:56:49 2019
 *
 * @class       vendor/Kovey\Web/App/Http/Router/Router.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Http\Router;

class Routers implements RoutersInterface
{
	private $getRoutes;

	private $postRoutes;

	private $putRoutes;

	private $delRoutes;

	private $defaults;

	private $isDisableDefault = false;

	public function __construct()
	{
		$this->getRoutes = array();
		$this->postRoutes = array();
		$this->putRoutes = array();
		$this->delRoutes = array();
	}

	public function getRouter(string $uri, string $method) : ? RouterInterface
	{
		$uri = str_replace(array('//', '\\'), array('/'), $uri);
		if ($uri !== '/') {
			if (!$this->isUri($uri)) {
				return null;
			}
		}

		if ($method === 'get') {
			return $this->getRoutes[$uri] ?? $this->defaultRoute($uri);
		}

		if ($method === 'post') {
			return $this->postRoutes[$uri] ?? $this->defaultRoute($uri);
		}

		if ($method === 'put') {
			return $this->putRoutes[$uri] ?? $this->defaultRoute($uri);
		}

		if ($method === 'delete') {
			return $this->delRoutes[$uri] ?? $this->defaultRoute($uri);
		}

		return null;
	}

	public function isUri(string $uri) : bool
	{
		return (bool)preg_match('/^\/[a-z]+(\/[a-z][a-z0-9]+){0,2}(\/.+){0,1}$/', $uri);
	}

	public function defaultRoute(string $uri) : ? RouterInterface
	{
		if ($this->isDisableDefault) {
			return null;
		}

		$uri = str_replace(array('//', '\\'), array('/'), $uri);

		if (isset($this->defaults[$uri])) {
			return $this->defaults[$uri];
		}

		$router = new Router($uri);
		if (!$router->isValid()) {
			return null;
		}

		$this->defaults[$router->getUri()] = $router;
		return $router;
	}

	public function get(string $uri, RouterInterface $router) : RoutersInterface
	{
		if ($router->isValid()) {
			$this->getRoutes[$uri] = $router;
		}
		return $this;
	}

	public function post(string $uri, RouterInterface $router) : RoutersInterface
	{
		if ($router->isValid()) {
			$this->postRoutes[$uri] = $router;
		}
		return $this;
	}

	public function put(string $uri, RouterInterface $router) : RoutersInterface
	{
		if ($router->isValid()) {
			$this->putRoutes[$uri] = $router;
		}
		return $this;
	}

	public function delete(string $uri, RouterInterface $router) : RoutersInterface
	{
		if ($router->isValid()) {
			$this->delRoutes[$uri] = $router;
		}
		return $this;
	}

	public function disableDefault()
	{
		$this->isDisableDefault = true;
	}
}
