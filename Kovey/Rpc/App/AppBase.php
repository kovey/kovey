<?php
/**
 * @description App基类，用于多端口监听
 *
 * @package Kovey\Rpc\App
 *
 * @author kovey
 *
 * @time 2020-03-21 18:24:46
 *
 * @file kovey/Kovey/Rpc/App/AppBase.php
 *
 */
namespace Kovey\Rpc\App;

use Kovey\Rpc\Handler\HandlerAbstract;
use Kovey\Components\Parse\ContainerInterface;
use Kovey\Config\Manager;
use Kovey\Rpc\App\Bootstrap\Autoload;
use Kovey\Components\Server\PortInterface;
use Kovey\Components\Logger\Monitor;

class AppBase
{
	/**
	 * @description 服务器
	 *
	 * @var Kovey\Components\Server\ServerInterface
	 */
	protected $server;

	/**
	 * @description 容器对象
	 *
	 * @var Kovey\Components\Parse\ContainerInterface
	 */
	protected $container;

	/**
	 * @description 应用配置
	 *
	 * @var Array
	 */
	protected $config;

	/**
	 * @description 自动加载
	 *
	 * @var Kovey\Rpc\App\Bootstrap\Autoload
	 */
	protected $autoload;

	/**
	 * @description 事件
	 *
	 * @var Array
	 */
	protected $events;

	/**
	 * @description 构造函数
	 *
	 * @return AppBase
	 */
	public function __construct()
	{
		$this->events = array();
	}

	/**
	 * @description 事件监听
	 *
	 * @param string $event
	 *
	 * @param callable $callable
	 *
	 * @return AppBase
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
	 * @return AppBase
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
	 * @return AppBase
	 */
	public function registerAutoload(Autoload $autoload)
	{
		$this->autoload = $autoload;
		return $this;
	}

	/**
	 * @description 注册服务端
	 *
	 * @param PortInterface $server
	 *
	 * @return AppBase
	 */
	public function registerServer(PortInterface $server)
	{
		$this->server = $server;
		$this->server
			->on('handler', array($this, 'handler'))
			->on('monitor', array($this, 'monitor'));

		return $this;
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
		Monitor::write($data);
	}

	/**
	 * @description 注册容器
	 *
	 * @param ContainerInterface $container
	 *
	 * @return AppBase
	 */
	public function registerContainer(ContainerInterface $container)
	{
		$this->container = $container;
		return $this;
	}

	/**
	 * @description 检测配置
	 *
	 * @return AppBase
	 *
	 * @throws Exception
	 */
	public function checkConfig()
	{
		$fields = array(
			'server' => array(
				'host', 'port', 'log_file', 'pid_file'	, 'secret_key'
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
	 * @description 注册本地加载路径
	 *
	 * @param string $path
	 *
	 * @return AppBase
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
	 * @description 获取容器
	 *
	 * @return ContainerInterface
	 */
	public function getContainer()
	{
		return $this->container;
	}
}
