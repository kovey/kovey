<?php
/**
 *
 * @description 路由对象
 *
 * @package     Router
 *
 * @time        2019-10-19 21:34:55
 *
 * @file  vendor/Kovey\Web/App/Http/Router/Router.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Http\Router;

use Kovey\Components\Middleware\MiddlewareInterface;

class Router implements RouterInterface
{
	private $uri;

	private $middlewares;

	private $action;

	private $controller;

	private $classPath;

	private $isValid;

	private $className;

	private $viewPath;

	private $actionName;

	private $callable;

	public function __construct(string $uri, $fun = null)
	{
		$this->uri = str_replace('//', '/', $uri);
		$this->middlewares = array();
		$this->classPath = '';
		$this->isValid = true;
		if (is_callable($fun)) {
			$this->callable = $fun;
			return;
		}

		if (!empty($fun)) {
			$info = explode('@', $fun);
			if (count($info) != 2) {
				$this->isValid = false;
				return;
			}

			$this->uri = '/' . $info[1] . '/' . $info[0];
		}

		$this->callable = null;

		$this->parseRoute();
		$this->className = str_replace('/', '\\', $this->classPath) . '\\' . ucfirst($this->controller) . 'Controller';
		$this->viewPath = strtolower($this->classPath) . '/' . strtolower($this->controller) . '/' . strtolower($this->action);
		$this->classPath = $this->classPath . '/' . ucfirst($this->controller) . '.php';
		$this->actionName = $this->action . 'Action';
	}

	private function parseRoute()
	{
		if ($this->uri === '/') {
			$this->controller = 'index';
			$this->action = 'index';
			return;
		}

		if (!$this->isUri($this->uri)) {
			$this->isValid = false;
			return;
		}

		$info = explode('/', $this->uri);
		$count = count($info);
		if ($count < 2) {
			$this->controller = 'index';
			$this->action = 'index';
			return;
		}

		if ($count == 2) {
			if (empty($info[1])) {
				$this->controller = 'index';
			} else {
				$this->controller = $info[1];
			}

			$this->action = 'index';

			return;
		}

		if ($count == 3) {
			$this->controller = $info[1];
			$this->action = $info[2];
			return;
		}

		$this->classPath = '/' . ucfirst($info[1]);
		$this->controller = $info[2];
		$this->action = $info[3];
	}

	public function getAction()
	{
		return $this->action;
	}

	public function getController()
	{
		return $this->controller;
	}

	public function getClassPath()
	{
		return $this->classPath;
	}

	public function addMiddleware(MiddlewareInterface $middleware)
	{
		$this->middlewares[] = $middleware;
		return $this;
	}

	public function getMiddlewares()
	{
		return $this->middlewares;
	}

	private function isUri($uri)
	{
		return (bool)preg_match('/^\/[a-zA-Z]+(\/[a-zA-Z][a-zA-Z0-9]+){0,3}(\/.+){0,1}$/', $uri);
	}

	public function isValid()
	{
		return $this->isValid;
	}

	public function getClassName()
	{
		return $this->className;
	}

	public function getActionName()
	{
		return $this->actionName;
	}

	public function getViewPath()
	{
		return $this->viewPath;
	}

	public function getCallable()
	{
		return $this->callable;
	}

	public function getUri()
	{
		return $this->uri;
	}
}
