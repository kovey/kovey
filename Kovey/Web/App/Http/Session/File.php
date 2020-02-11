<?php
/**
 *
 * @description 文件session
 *
 * @package     Session
 *
 * @time        2019-10-12 23:14:43
 *
 * @file  vendor/Kovey\Web/App/Http/Session/File.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Http\Session;
use Swoole\Coroutine\System;
use Kovey\Util\Json;

class File implements SessionInterface
{
	private $content;

	private $file;

	private $sessionId;

	private $dir;

	public function __construct($dir, $sessionId)
	{
		$this->dir = $dir;

		$this->file = $dir . '/' . str_replace(array('$', '/', '.'), '', $sessionId);

		$this->sessionId = $sessionId;
		$this->content = array();

		$this->init();
	}

	private function init()
	{
		if (!empty($this->sessionId)) {
			if (!is_file($this->file)) {
				$this->newSessionId();
				return;
			}
		}

		$file = System::readFile($this->file);
		if ($file === false) {
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

	private function saveToFile()
	{
		go (function () {
			System::writeFile($this->file, Json::encode($this->content));
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
		if (!empty($this->sessionId)) {
			return $this->sessionId;
		}

		$this->newSessionId();

		return $this->sessionId;
	}

	private function newSessionId()
	{
		$this->sessionId = password_hash(uniqid('session', true) . random_int(1000000, 9999999), PASSWORD_DEFAULT);
		$this->file = $this->dir . '/' . str_replace(array('$', '/', '.'), '', $this->sessionId); 
	}

	public function clear()
	{
		$this->content = array();
	}

	public function __destruct()
	{
		$this->saveToFile();
	}
}
