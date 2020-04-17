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
	/**
	 * @description 配置
	 *
	 * @var Array
	 */
	private $config;

	/**
	 * @description 服务器
	 *
	 * @var Server
	 */
	private $server;

	/**
	 * @description 路由
	 *
	 * @var RoutersInterface
	 */
	private $routers;

	/**
	 * @description 插件
	 *
	 * @var Array
	 */
	private $plugins;

	/**
	 * @description 自动加载
	 *
	 * @var Autoload
	 */
	private $autoload;

	/**
	 * @description 连接池
	 *
	 * @var Array
	 */
	private $pools;

	/**
	 * @description 容器
	 *
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * @description 默认中间件
	 *
	 * @var Array
	 */
	private $defaultMiddlewares;

	/**
	 * @description 用户进程管理
	 *
	 * @var UserProcess
	 */
	private $userProcess;

	/**
	 * @description 对象实例
	 *
	 * @var Application
	 */
	private static $instance = null;

	/**
	 * @description 事件
	 *
	 * @var Array
	 */
	private $events;

	/**
	 * @description 全局变量
	 *
	 * @var Array
	 */
	private $globals;

	/**
	 * @description 获取对象实例
	 *
	 * @param Array $config
	 *
	 * @return Application
	 */
	public static function getInstance(Array $config = array())
	{
		if (self::$instance == null) {
			self::$instance = new self($config);
		}

		return self::$instance;
	}

	/**
	 * @description 构造
	 *
	 * @param Array $config
	 *
	 * @return Application
	 */
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

	/**
	 * @description 注册全局变量
	 *
	 * @param string $name
	 *
	 * @param mixed $val
	 *
	 * @return Application
	 */
	public function registerGlobal($name, $val)
	{
		$this->globals[$name] = $val;
		return $this;
	}

	/**
	 * @description 获取全局变量
	 *
	 * @return mixed
	 */
	public function getGlobal($name)
	{
		return $this->globals[$name] ?? null;
	}

	/**
	 * @description 注册自动加载
	 *
	 * @param Autoload $autoload
	 *
	 * @return Application
	 */
	public function registerAutoload(Autoload $autoload)
	{
		$this->autoload = $autoload;
		return $this;
	}

	/**
	 * @description 注册中间件
	 *
	 * @param MiddlewareInterface $middleware
	 *
	 * @return Application
	 */
	public function registerMiddleware(MiddlewareInterface $middleware)
	{
		$this->defaultMiddlewares[] = $middleware;
		return $this;
	}

	/**
	 * @description 获取默认的中间件
	 *
	 * @return Array
	 */
	public function getDefaultMiddlewares()
	{
		return $this->defaultMiddlewares;
	}

	/**
	 * @description 注册路由
	 *
	 * @param RoutersInterface $routers
	 *
	 * @return Application
	 */
	public function registerRouters(RoutersInterface $routers)
	{
		$this->routers = $routers;
		return $this;
	}

	/**
	 * @description 注册服务器
	 *
	 * @param Server $server
	 *
	 * @return Application
	 */
	public function registerServer(Server $server)
	{
		$this->server = $server;
        $this->server
            ->on('workflow', array($this, 'workflow'))
			->on('init', array($this, 'init'))
            ->on('console', array($this, 'console'))
            ->on('monitor', array($this, 'monitor'));

		return $this;
	}

	/**
	 * @description 处理console事件
	 *
	 * @param string $path
	 *
	 * @param string $method
	 *
	 * @param mixed $args
	 *
	 * @return null
	 */
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

	/**
	 * @description 注册容器
	 *
	 * @param ContainerInterface $container
	 *
	 * @return Application
	 */
	public function registerContainer(ContainerInterface $container)
	{
		$this->container = $container;
		return $this;
	}

	/**
	 * @description 检查配置
	 *
	 * @return Application
	 *
	 * @throws Exception
	 */
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

	/**
	 * @description 工作流
	 *
	 * @param Swoole\Http\Request $request
	 *
	 * @return Array
	 */
	public function workflow(\Swoole\Http\Request $request)
	{
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
		if (isset($this->events['pipeline'])) {
			$result = call_user_func($this->events['pipeline'], $req, $res, $router);
			if ($result instanceof ResponseInterface) {
				$result = $result->toArray();
			}
		} else {
			$result = $this->runAction($req, $res, $router);
		}

		return $result;
	}

    public function monitor(Array $data)
    {
		$this->userProcess->push('monitor', $data);
		Monitor::write($data);
    }

	/**
	 * @description 事件监听
	 *
	 * @param string $type
	 *
	 * @param callable $fun
	 *
	 * @return Application
	 */
	public function on($type, $fun)
	{
		if (!is_callable($fun)) {
			return $this;
		}

		$this->events[$type] = $fun;
		return $this;
	}

	/**
	 * @description 运用启动
	 *
	 * @return null
	 */
	public function run()
	{
		if (!is_object($this->server)) {
			throw new \Exception('server not register');
		}

		$this->server->start();
	}

	/**
	 * @description 注册启动类
	 *
	 * @param mixed $bootstrap
	 *
	 * @return Application
	 */
	public function registerBootstrap($bootstrap)
	{
		$this->bootstrap = $bootstrap;
		return $this;
	}

	/**
	 * @description 注册自定义启动
	 *
	 * @param mixed $bootstrap
	 *
	 * @return Application
	 */
	public function registerCustomBootstrap($bootstrap)
	{
		$this->customBootstrap = $bootstrap;
		return $this;
	}

	/**
	 * @description 启动前初始化
	 *
	 * @return Application
	 */
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

	/**
	 * @description 执行Action
	 *
	 * @param RequestInterface $req
	 *
	 * @param ResponseInterface $res
	 *
	 * @param RouterInterface $router
	 *
	 * @return Array
	 */
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

	/**
	 * @description 获取配置
	 *
	 * @return Array
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * @description 获取服务器
	 *
	 * @return Server
	 */
	public function getServer()
	{
		return $this->server;
	}

	/**
	 * @description 注册GET路由
	 *
	 * @param string $uri
	 *
	 * @param RouterInterface $router
	 *
	 * @return Application
	 */
	public function registerGetRouter(string $uri, RouterInterface $router)
	{
		$this->routers->get($uri, $router);
		return $this;
	}

	/**
	 * @description 注册POST路由
	 *
	 * @param string $uri
	 *
	 * @param RouterInterface $router
	 *
	 * @return Application
	 */
	public function registerPostRouter(string $uri, RouterInterface $router)
	{
		$this->routers->post($uri, $router);
		return $this;
	}

	/**
	 * @description 注册PUT路由
	 *
	 * @param string $uri
	 *
	 * @param RouterInterface $router
	 *
	 * @return Application
	 */
	public function registerPutRouter(string $uri, RouterInterface $router)
	{
		$this->routers->put($uri, $router);
		return $this;
	}

	/**
	 * @description 注册DEL路由
	 *
	 * @param string $uri
	 *
	 * @param RouterInterface $router
	 *
	 * @return Application
	 */
	public function registerDelRouter(string $uri, RouterInterface $router)
	{
		$this->routers->delete($uri, $router);
		return $this;
	}

	/**
	 * @description 注册插件
	 *
	 * @param PluginInterface $plugin
	 *
	 * @return Application
	 */
	public function registerPlugin(PluginInterface $plugin)
	{
		$this->plugins[$plugin] = $plugin;
		return $this;
	}

	/**
	 * @description 初始化连接池
	 *
	 * @param Server $serv
	 *
	 * @return null
	 */
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

	/**
	 * @description 获取用户进程管理
	 *
	 * @return UserProcess
	 */
	public function getUserProcess()
	{
		return $this->userProcess;
	}

	/**
	 * @description 注册进程
	 *
	 * @param string $name
	 *
	 * @param ProcessAbstract $process
	 *
	 * @return Application
	 */
	public function registerProcess($name, ProcessAbstract $process)
	{
		if (!is_object($this->server)) {
			return $this;
		}

		$process->setServer($this->server->getServ());
		$this->userProcess->addProcess($name, $process);
		return $this;
	}

	/**
	 * @description 注册本地类库
	 *
	 * @param string $path
	 *
	 * @return Application
	 */
	public function registerLocalLibPath($path)
	{
		$this->autoload->addLocalPath($path);
		return $this;
	}

	/**
	 * @description 注册连接池
	 *
	 * @param string $name
	 *
	 * @param PoolInterface $pool
	 *
	 * @return Application
	 */
	public function registerPool($name, PoolInterface $pool)
	{
		$this->pools[$name] = $pool;
		return $this;
	}

	/**
	 * @description 获取连接池
	 *
	 * @param string $name
	 *
	 * @return PoolInterface | null
	 */
	public function getPool($name)
	{
		return $this->pools[$name] ?? false;
	}

	/**
	 * @description 获取容器
	 *
	 *
	 * @return ControllerInterface
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * @description 注册进程管理
	 *
	 * @param UserProcess $userProcess
	 *
	 * @return Application
	 */
	public function registerUserProcess(UserProcess $userProcess)
	{
		$this->userProcess = $userProcess;
		return $this;
	}
}
