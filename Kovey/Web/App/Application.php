<?php
/**
 *
 * @description Application global
 *
 * @package     App
 *
 * @time        Tue Sep 24 00:28:03 2019
 *
 * @class       vendor/Kovey\Web/App/Application.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App;

use Kovey\Components\Pool\PoolInterface;
use Kovey\Web\App\Http\Request\RequestInterface;
use Kovey\Web\App\Http\Response\ResponseInterface;
use Kovey\Web\App\Http\Router\RouterInterface;
use Kovey\Web\App\Http\Router\RoutersInterface;
use Kovey\Web\App\Mvc\Controller\ControllerInterface;
use Kovey\Web\App\Mvc\View\ViewInterface;
use Kovey\Components\Process\ProcessAbstract;
use Kovey\Web\App\Http\SessionInterface;
use Kovey\Components\Parse\ContainerInterface;
use Kovey\Components\Middleware\MiddlewareInterface;
use Kovey\Config\Manager;
use Kovey\Web\App\Bootstrap\Autoload;
use Kovey\Web\Server\Server;
use Kovey\Components\Process\UserProcess;
use Kovey\Components\Logger\Logger;
use Kovey\Components\Logger\Monitor;

class Application
{
	private $config;

	private $server;

	private $routers;

	private $plugins;

	private $autoload;

	private $pools;

	private $container;

	private $defaultMiddlewares;

	private $userProcess;

	private static $instance = null;

	private $events;

	private $globals;

	public static function getInstance(Array $config = array())
	{
		if (self::$instance == null) {
			self::$instance = new self($config);
		}

		return self::$instance;
	}

	private function __construct(Array $config)
	{
		$this->config = $config;
		$this->plugins = array();
		$this->pools = array();
		$this->defaultMiddlewares = array();
		$this->events = array();
		$this->globals = array();
	}

	private function __clone()
	{}

	public function registerGlobal($name, $val)
	{
		$this->globals[$name] = $val;
		return $this;
	}

	public function getGlobal($name)
	{
		return $this->globals[$name] ?? null;
	}

	public function registerAutoload(Autoload $autoload)
	{
		$this->autoload = $autoload;
		return $this;
	}

	public function registerMiddleware(MiddlewareInterface $middleware)
	{
		$this->defaultMiddlewares[] = $middleware;
		return $this;
	}

	public function getDefaultMiddlewares()
	{
		return $this->defaultMiddlewares;
	}

	public function registerRouters(RoutersInterface $routers)
	{
		$this->routers = $routers;
		return $this;
	}

	public function registerServer(Server $server)
	{
		$this->server = $server;
		$this->server->on('workflow', array($this, 'workflow'))
			->on('init', array($this, 'init'))
			->on('console', array($this, 'console'));

		return $this;
	}

	public function console($path, $method, $args)
	{
		if (!isset($this->events['console'])) {
			return;
		}

		try {
			call_user_func($this->events['console'], $path, $method, $args);
		} catch (\Exception $e) {
			Logger::writeExceptionLog(__LINE__, __FILE__, $e);
		} catch (\Throwable $e) {
			Logger::writeExceptionLog(__LINE__, __FILE__, $e);
		}
	}

	public function registerContainer(ContainerInterface $container)
	{
		$this->container = $container;
		return $this;
	}

	public function checkConfig()
	{
		$fields = array(
			'controllers', 'views', 'boot', 'template'
		);

		foreach ($fields as $field) {
			if (!isset($this->config[$field])) {
				throw new \Exception('config is error', 500);
			}
		}

		return $this;
	}

	public function workflow(\Swoole\Http\Request $request)
	{
		$begin = microtime(true);
		$reqTime = time();

		if (!isset($this->events['request'])
			|| !isset($this->events['response'])
		) {
			Logger::writeErrorLog(__LINE__, __FILE__, 'request or response events is not exits.');
			return array();
		}

		$req = call_user_func($this->events['request'], $request);
		if (!$req instanceof RequestInterface) {
			Logger::writeErrorLog(__LINE__, __FILE__, 'request is not implements Kovey\Web\App\Http\Request\RequestInterface.');
			return array();
		}
		$res = call_user_func($this->events['response']);
		if (!$res instanceof ResponseInterface) {
			Logger::writeErrorLog(__LINE__, __FILE__, 'request is not implements Kovey\Web\App\Http\Responset\ResponseInterface.');
			return array();
		}
		$uri = trim($req->getUri());
		$router = $this->routers->getRouter($uri, $req->getMethod());
		if ($router === null) {
			Logger::writeErrorLog(__LINE__, __FILE__, 'router is error, uri: ' . $uri);
			$res->status('405');
			return $res->toArray();
		}

		$req->setController($router->getController())
			->setAction($router->getAction());

		$result = null;
		$params = $req->getMethod() === 'post' ? $req->getPost() : $req->getQuery();

		if (isset($this->events['pipeline'])) {
			$result = call_user_func($this->events['pipeline'], $req, $res, $router);
			if ($result instanceof ResponseInterface) {
				$result = $result->toArray();
			}
		} else {
			$result = $this->runAction($req, $res, $router);
		}

		$end = microtime(true);

		Monitor::write(array(
			'delay' => round(($end - $begin) * 1000, 2),
			'path' => $uri,
			'params' => $params,
			'ip' => $req->getClientIP(),
			'time' => $reqTime,
			'timestamp' => date('Y-m-d H:i:s', $reqTime),
			'minute' => date('YmdHi', $reqTime)
		));

		return $result;
	}

	public function on($type, $fun)
	{
		if (!is_callable($fun)) {
			return $this;
		}

		$this->events[$type] = $fun;
		return $this;
	}

	public function run()
	{
		if (!is_object($this->server)) {
			throw new \Exception('server not register');
		}

		$this->server->start();
	}

	public function registerBootstrap($bootstrap)
	{
		$this->bootstrap = $bootstrap;
		return $this;
	}

	public function registerCustomBootstrap($bootstrap)
	{
		$this->customBootstrap = $bootstrap;
	}

	public function bootstrap()
	{
		$btfuns = get_class_methods($this->bootstrap);
		foreach ($btfuns as $fun) {
			if (substr($fun, 0, 6) !== '__init') {
				continue;
			}

			$this->bootstrap->$fun($this);
		}

		$funs = get_class_methods($this->customBootstrap);
		foreach ($funs as $fun) {
			if (substr($fun, 0, 6) !== '__init') {
				continue;
			}

			$this->customBootstrap->$fun($this);
		}

		return $this;
	}

	public function runAction(RequestInterface $req, ResponseInterface $res, RouterInterface $router)
	{
		if (!empty($router->getCallable())) {
			$res->setBody(call_user_func($router->getCallable(), $req, $res));
			$res->status(200);
			return $res->toArray();
		}

		$conFile = APPLICATION_PATH . '/' . $this->config['controllers'] . $router->getClassPath();

		if (!is_file($conFile)) {
			Logger::writeErrorLog(__LINE__, __FILE__, "file of " . $router->getController() . " is not exists, controller file \" $conFile\".");
			$res->status(404);
			return $res->toArray();
		}

		$template = APPLICATION_PATH . '/' . $this->config['views'] . '/' . $router->getViewPath() . '.' . $this->config['template'];
		$obj = $this->container->get($router->getClassName(), $req, $res, $template, $this->plugins);
		if (!$obj instanceof ControllerInterface) {
			Logger::writeErrorLog(__LINE__, __FILE__, "class \"$controller\" is not extends Kovey\Web\App\Mvc\Controller\ControllerInterface.");
			$res->status(404);
			return $res->toArray();
		}

		$action = $router->getActionName();
		if (!method_exists($obj, $action)) {
			Logger::writeErrorLog(__LINE__, __FILE__, "action \"$action\" is not exists.");
			$res->status(404);
			return $res->toArray();
		}

		$httpCode = $obj->getResponse()->getHttpCode();
		if ($httpCode == 201 
			|| ($httpCode > 300 && $httpCode < 400)
		) {
			return $obj->getResponse()->toArray();
		}

		if (!$obj->isViewDisabled() && isset($this->events['view'])) {
			call_user_func($this->events['view'], $obj, $template);
		}

		$content = '';

		if (isset($this->events['run_action'])) {
			$content = call_user_func($this->events['run_action'], $obj, $action);
		} else {
			$content = $obj->$action();
		}

		if ($obj->isViewDisabled()) {
			$res->setBody($content);
			$res->status(200);
			return $res->toArray();
		}

		$httpCode = $obj->getResponse()->getHttpCode();
		if ($httpCode == 201 
			|| ($httpCode > 300 && $httpCode < 400)
		) {
			return $obj->getResponse()->toArray();
		}

		if (!is_file($template)) {
			Logger::writeErrorLog(__LINE__, __FILE__, "template \"$template\" is not exists.");
			$res->status(404);
			$res->setBody('');
			return $res->toArray();
		}

		$obj->render();
		$res = $obj->getResponse();

		if (!$obj->isPluginDisabled()) {
			foreach ($obj->getPlugins() as $plugin) {
				$plugin->loopShutdown($req, $res);
			}
		}

		$res->status(200);
		return $res->toArray();
	}

	public function getConfig()
	{
		return $this->config;
	}

	public function getServer()
	{
		return $this->server;
	}

	public function registerGetRouter(string $uri, RouterInterface $router)
	{
		$this->routers->get($uri, $router);
		return $this;
	}

	public function registerPostRouter(string $uri, RouterInterface $router)
	{
		$this->routers->post($uri, $router);
		return $this;
	}

	public function registerPutRouter(string $uri, RouterInterface $router)
	{
		$this->routers->put($uri, $router);
		return $this;
	}

	public function registerDelRouter(string $uri, RouterInterface $router)
	{
		$this->routers->delete($uri, $router);
		return $this;
	}

	public function registerPlugin($plugin)
	{
		$this->plugins[$plugin] = $plugin;
		return $this;
	}

	public function initPool(Server $serv)
	{
		try {
			foreach ($this->pools as $pool) {
				$pool->init();
				if (count($pool->getErrors()) > 0) {
					Logger::writeErrorLog(__LINE__, __FILE__, implode(';', $pool->getErrors()));
				}
			}
		} catch (\Exception $e) {
			Logger::writeExceptionLog(__LINE__, __FILE__, $e);
		} catch (\Throwable $e) {
			Logger::writeExceptionLog(__LINE__, __FILE__, $e);
		}
	}

	public function getUserProcess()
	{
		return $this->userProcess;
	}

	public function registerProcess($name, ProcessAbstract $process)
	{
		if (!is_object($this->server)) {
			return $this;
		}

		$process->setServer($this->server->getServ());
		$this->userProcess->addProcess($name, $process);
		return $this;
	}

	public function registerLocalLibPath($path)
	{
		$this->autoload->addLocalPath($path);
		return $this;
	}

	public function registerPool($name, PoolInterface $pool)
	{
		$this->pools[$name] = $pool;
		return $this;
	}

	public function getPool($name)
	{
		return $this->pools[$name] ?? false;
	}

	public function getContainer()
	{
		return $this->container;
	}

	public function registerUserProcess(UserProcess $userProcess)
	{
		$this->userProcess = $userProcess;
		return $this;
	}
}
