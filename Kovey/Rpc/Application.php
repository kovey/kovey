<?php
/**
 *
 * @description RPC应用
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
	private static $instance;

	private $server;

	private $container;

	private $bootstrap;

	private $customBootstrap;

	private $config;

	private $userProcess;

	private $pools;

	private $autoload;

	private $events;

	private function __construct()
	{
		$this->pools = array();
		$this->events = array();
	}

	private function __clone()
	{}

	public static function getInstance()
	{
		if (!self::$instance instanceof Application) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function on($event, $callable)
	{
		if (!is_callable($callable)) {
			return $this;
		}

		$this->events[$event] = $callable;
		return $this;
	}

	public function setConfig(Array $config)
	{
		$this->config = $config;
		return $this;
	}

	public function getConfig()
	{
		return $this->config;
	}

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

	public function registerAutoload(Autoload $autoload)
	{
		$this->autoload = $autoload;
		return $this;
	}

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

	public function monitor(Array $data)
	{
		$this->userProcess->push('monitor', $data);
		Monitor::write($data);
	}

	public function registerContainer(ContainerInterface $container)
	{
		$this->container = $container;
		return $this;
	}

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

	public function registerBootstrap($bootstrap)
	{
		$this->bootstrap = $bootstrap;
		return $this;
	}

	public function registerCustomBootstrap($bootstrap)
	{
		$this->customBootstrap = $bootstrap;
	}

	public function registerUserProcess(UserProcess $userProcess)
	{
		$this->userProcess = $userProcess;
		return $this;
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
		if (!is_object($this->autoload)) {
			return $this;
		}

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

	public function run()
	{
		if (!is_object($this->server)) {
			throw new \Exception('server not register');
		}

		$this->server->start();
	}
}
