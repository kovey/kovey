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
	/**
	 * @description 内容
	 *
	 * @var Array
	 */
	private $content;

	/**
	 * @description 文件
	 *
	 * @var string
	 */
	private $file;

	/**
	 * @description ID
	 *
	 * @var string
	 */
	private $sessionId;

	/**
	 * @description 目录
	 *
	 * @var string
	 */
	private $dir;

	/**
	 * @description 构造
	 *
	 * @param string $dir
	 *
	 * @param string $sessionId
	 *
	 * @return File
	 */
	public function __construct($dir, $sessionId)
	{
		$this->dir = $dir;

		$this->file = $dir . '/' . str_replace(array('$', '/', '.'), '', $sessionId);

		$this->sessionId = $sessionId;
		$this->content = array();

		$this->init();
	}

	/**
	 * @description 初始化
	 *
	 * @return null
	 */
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

	/**
	 * @description 获取值
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function get($name)
	{
		return $this->content[$name] ?? '';
	}

	/**
	 * @description 设置值
	 *
	 * @param string $name
	 *
	 * @param mixed $val
	 *
	 * @return null
	 */
	public function set($name, $val)
	{
		$this->content[$name] = $val;
	}

	/**
	 * @description 保存到REDIS
	 *
	 * @return null
	 */
	private function saveToFile()
	{
		go (function () {
			System::writeFile($this->file, Json::encode($this->content));
		});
	}

	/**
	 * @description 获取值
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->get($name);
	}

	/**
	 * @description 设置值
	 *
	 * @param string $name
	 *
	 * @param mixed $val
	 *
	 * @return null
	 */
	public function __set($name, $val)
	{
		$this->set($name, $val);
	}

	/**
	 * @description 删除
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function del($name)
	{
		if (!isset($this->content[$name])) {
			return false;
		}

		$this->set($name, null);
		return true;
	}

	/**
	 * @description 获取sessionID
	 *
	 * @return string
	 */
	public function getSessionId()
	{
		if (!empty($this->sessionId)) {
			return $this->sessionId;
		}

		$this->newSessionId();

		return $this->sessionId;
	}

	/**
	 * @description 创建sessionID
	 *
	 * @return null
	 */
	private function newSessionId()
	{
		$this->sessionId = password_hash(uniqid('session', true) . random_int(1000000, 9999999), PASSWORD_DEFAULT);
		$this->file = $this->dir . '/' . str_replace(array('$', '/', '.'), '', $this->sessionId); 
	}

	/**
	 * @description 清除
	 *
	 * @return null
	 */
	public function clear()
	{
		$this->content = array();
	}

	/**
	 * @description 一些处理
	 *
	 * @return null
	 */
	public function __destruct()
	{
		$this->saveToFile();
	}
}
