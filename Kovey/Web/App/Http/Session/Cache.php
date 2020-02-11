<?php
/**
 *
 * @description 放入Redis缓存
 *
 * @package     Session
 *
 * @time        2019-10-12 23:33:43
 *
 * @file  vendor/Kovey\Web/App/Http/Session/Cache.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Http\Session;
use Kovey\Util\Json;
use Kovey\Components\Pool\PoolInterface;

class Cache implements SessionInterface
{
	private $content;

	private $file;

	private $pool;

	const SESSION_KEY = 'kovey-session-';

	public function __construct(PoolInterface $pool, $sessionId)
	{
		$this->file = $sessionId;
		$this->pool = $pool;
		$this->content = array();

		$this->init();
	}

	private function init()
	{
		$redis = $this->pool->getDatabase();
		if (!$redis) {
			return;
		}
		$file = $redis->get(self::SESSION_KEY . $this->file);
		$this->pool->put($redis);
		if ($file === false) {
			$this->newSessionId();
			return;
		}

		$info = Json::decode($file);
		if (empty($info) || !is_array($info)) {
			return;
		}

		$this->content = $info;
	}

	public function get($name)
	{
		return $this->content[$name] ?? '';
	}

	public function set($name, $val)
	{
		$this->content[$name] = $val;
	}

	private function saveToRedis()
	{
		go (function () {
			$redis = $this->pool->getDatabase();
			if (!$redis) {
				return;
			}

			$redis->set(self::SESSION_KEY . $this->file, Json::encode($this->content));
			$this->pool->put($redis);
		});
	}

	public function __get($name)
	{
		return $this->get($name);
	}

	public function __set($name, $val)
	{
		$this->set($name, $val);
	}

	public function del($name)
	{
		if (!isset($this->content[$name])) {
			return false;
		}

		$this->set($name, null);
		return true;
	}

	public function getSessionId()
	{
		if (!empty($this->file)) {
			return $this->file;
		}

		$this->newSessionId();

		return $this->file;
	}

	private function newSessionId()
	{
		$this->file = password_hash(uniqid('session', true) . random_int(1000000, 9999999), PASSWORD_DEFAULT);
	}

	public function clear()
	{
		$this->content = array();
	}

	public function __destruct()
	{
		$this->saveToRedis();
	}
}
