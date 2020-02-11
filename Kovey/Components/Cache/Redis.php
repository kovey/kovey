<?php
/**
 *
 * @description Redis 客户端封装，基于\Swoole\Coroutine\Redis
 *
 * @package     Components\Cache
 *
 * @time        Tue Sep 24 09:01:39 2019
 *
 * @class       vendor/Kovey/Components/Cache/Redis.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Cache;

class Redis
{
	private $connection;

	private $config;

	public function __construct(Array $config)
	{
		$this->config = $config;
		$this->connection = new \Swoole\Coroutine\Redis();
		$this->connection->setOptions(array(
			'compatibility_mode' => true
		));
	}

	public function connect()
	{
		if (!$this->connection->connect($this->config['host'], $this->config['port'])) {
			return false;
		}

		return $this->connection->select($this->config['db']);
	}

	public function __call($name, $params)
	{
		if (!$this->connection->connected) {
			$this->connect();
		}

		return $this->connection->$name(...$params);
	}

	public function getError()
	{
		return sprintf('[%s]%s', $this->connection->errCode, $this->connection->errMsg);
	}
}
