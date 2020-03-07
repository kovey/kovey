<?php
/**
 *
 * @description RPC应用全局大对象
 *
 * @package     Kovey\Rpc
 *
 * @time        2019-11-16 17:28:41
 *
 * @file  /Users/kovey/Documents/php/kovey/rpc/Kovey\Rpc/Application.php
 *
 * @author      kovey
 */
namespace Kovey\Rpc;

use Kovey\Rpc\Handler\HandlerAbstract;
use Kovey\Components\Process\ProcessAbstract;
use Kovey\Components\Pool\PoolInterface;
use Kovey\Components\Parse\ContainerInterface;
use Kovey\Config\Manager;
use Kovey\Rpc\App\Bootstrap\Autoload;
use Kovey\Rpc\Server\Server;
use Kovey\Components\Process\UserProcess;
use Kovey\Components\Logger\Logger;
use Kovey\Components\Logger\Monitor;

class Application
{
	/**
	 * @description Application实例
	 *
	 * @var Application
	 */
	private static $instance;

	/**
	 * @description 服务器
	 *
	 * @var Kovey\Rpc\Server\Server
	 */
	private $server;

	/**
	 * @description 容器对象
	 *
	 * @var Kovey\Components\Parse\ContainerInterface
	 */
	private $container;

	/**
	 * @description 启动处理
	 *
	 * @var Kovey\Rpc\Bootstrap\Bootstrap
	 */
	private $bootstrap;

	/**
	 * @description 自定义启动
	 *
	 * @var mixed
	 */
	private $customBootstrap;

	/**
	 * @description 应用配置
	 *
	 * @var Array
	 */
	private $config;

	/**
	 * @description 用户自定义进程
	 *
	 * @var Kovey\Components\Process\UserProcess
	 */
	private $userProcess;

	/**
	 * @description 连接池
	 *
	 * @var Array
	 */
	private $pools;

	/**
	 * @description 自动加载
	 *
	 * @var Kovey\Rpc\App\Bootstrap\Autoload
	 */
	private $autoload;

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
	 * @description 构造函数
	 *
	 * @return Application
	 */
	private function __construct()
	{
		$this->pools = array();
		$this->events = array();
		$this->globals = array();
	}

	private function __clone()
	{}

	/**
	 * @description 获取Application 的实例
	 *
	 * @return Application
	 */
	public static function getInstance()
	{
		if (!self::$instance instanceof Application) {
			self::$instance = new self();
		}

		return self::$instance;
	}

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
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function getGlobal($name)
	{
		return $this->globals[$name] ?? null;
	}

	/**
	 * @description 事件监听
	 *
	 * @param string $event
	 *
	 * @param callable $callable
	 *
	 * @return Application
	 */
	public function on($event, $callable)
	{
		if (!is_callable($callable)) {
			return $this;
		}

		$this->events[$event] = $callable;
		return $this;
	}

	/**
	 * @description 设置配置
	 *
	 * @param Array $config
	 *
	 * @return Application
	 */
	public function setConfig(Array $config)
	{
		$this->config = $config;
		return $this;
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
	 * @description 启动处理
	 *
	 * @return Application
	 */
	public function bootstrap()
	{
		if (is_object($this->bootstrap)) {
			$btfuns = get_class_methods($this->bootstrap);
			foreach ($btfuns as $fun) {
				if (substr($fun, 0, 6) !== '__init') {
					continue;
				}

				$this->bootstrap->$fun($this);
			}
		}

		if (is_object($this->customBootstrap)) {
			$funs = get_class_methods($this->customBootstrap);
			foreach ($funs as $fun) {
				if (substr($fun, 0, 6) !== '__init') {
					continue;
				}

				$this->customBootstrap->$fun($this);
			}
		}

		return $this;
	}

	/**
	 * @description handler业务
	 *
	 * @param string $class
	 *
	 * @param string $method
	 *
	 * @param Array $args
	 *
	 * @return Array
	 */
	public function handler($class, $method, $args)
	{
		$instance = $this->container->get($this->config['rpc']['handler'] . '\\' . ucfirst($class));
		if (!$instance instanceof HandlerAbstract) {
			return array(
				'err' => sprintf('%s is not extends HandlerAbstract', ucfirst($class)),
				'type' => 'exception',
				'code' => 1,
			);
		}
		if (empty($args)) {
			$result = $instance->$method();
			return array(
				'err' => '',
				'type' => 'success',
				'code' => 0,
				'result' => $result
			);
		}

		$result = $instance->$method(...$args);
		return array(
			'err' => '',
			'type' => 'success',
			'code' => 0,
			'result' => $result
		);
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
	 * @description 注册服务端
	 *
	 * @param Server $server
	 *
	 * @return Application
	 */
	public function registerServer(Server $server)
	{
		$this->server = $server;
		$this->server
			->on('handler', array($this, 'handler'))
			->on('pipeMessage', array($this, 'pipeMessage'))
			->on('initPool', array($this, 'initPool'))
			->on('monitor', array($this, 'monitor'));

		return $this;
	}

	/**
	 * @description 进程间通信
	 *
	 * @param string $path
	 *
	 * @param string $method
	 *
	 * @param Array $args
	 *
	 * @return null
	 */
	public function pipeMessage($path, $method, $args)
	{
		if (!isset($this->events['pipeMessage'])) {
			return;
		}

		try {
			call_user_func($this->events['pipeMessage'], $path, $method, $args);
		} catch (\Exception $e) {
			Logger::writeExceptionLog(__LINE__, __FILE__, $e);
		} catch (\Throwable $e) {
			Logger::writeExceptionLog(__LINE__, __FILE__, $e);
		}
	}

	/**
	 * @description 初始化连接池
	 *
	 * @param Swoole\Server
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
	 * @description 监控
	 *
	 * @param Array $data
	 *
	 * @return null
	 */
	public function monitor(Array $data)
	{
		$this->userProcess->push('monitor', $data);
		Monitor::write($data);
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
	 * @description 检测配置
	 *
	 * @return Application
	 *
	 * @throws Exception
	 */
	public function checkConfig()
	{
		$fields = array(
			'server' => array(
				'host', 'port', 'log_file', 'pid_file'	, 'secret_key'
			), 
			'logger' => array(
				'info', 'exception', 'error', 'warning'
			), 
			'rpc' => array(
				'name', 'handler'
			)
		);

		foreach ($fields as $key => $field) {
			if (!isset($this->config[$key])) {
				throw new \Exception("$key is not exists", 500);
			}

			foreach ($field as $fe) {
				if (!isset($this->config[$key][$fe])) {
					throw new \Exception("$fe of $key is not exists", 500);
				}
			}
		}

		return $this;
	}

	/**
	 * @description 注册启动处理类
	 *
	 * @param mixed Bootstrap
	 *
	 * @return Application
	 */
	public function registerBootstrap($bootstrap)
	{
		$this->bootstrap = $bootstrap;
		return $this;
	}

	/**
	 * @description 注册自定义的启动处理类
	 *
	 * @param mixed Bootstrap
	 *
	 * @return Application
	 */
	public function registerCustomBootstrap($bootstrap)
	{
		$this->customBootstrap = $bootstrap;
	}

	/**
	 * @description 用户自定义进程管理
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

	/**
	 * @description 获取用户自定义进程管理
	 *
	 * @return UserProcess
	 */
	public function getUserProcess()
	{
		return $this->userProcess;
	}

	/**
	 * @description 注册自定义进程
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
	 * @description 注册本地加载路径
	 *
	 * @param string $path
	 *
	 * @return Application
	 */
	public function registerLocalLibPath($path)
	{
		if (!is_object($this->autoload)) {
			return $this;
		}

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
	 * @return PoolInterface | bool
	 */
	public function getPool($name)
	{
		return $this->pools[$name] ?? false;
	}

	/**
	 * @description 获取容器
	 *
	 * @return ContainerInterface
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * @description 运用启动
	 *
	 * @return null
	 *
	 * @throws Exception
	 */
	public function run()
	{
		if (!is_object($this->server)) {
			throw new \Exception('server not register');
		}

		$this->server->start();
	}
}
