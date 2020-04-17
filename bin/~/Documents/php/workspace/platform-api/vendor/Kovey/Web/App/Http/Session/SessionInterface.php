<?php
/**
 *
 * @description session 接口
 *
 * @package     Session
 *
 * @time        2019-10-12 23:12:05
 *
 * @file  vendor/Kovey\Web/App/Http/Session/SessionInterface.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Http\Session;

interface SessionInterface
{
	/**
	 * @description 设置值
	 *
	 * @param string $name
	 *
	 * @param mixed $val
	 *
	 * @return null
	 */
	public function set($name, $val);

	/**
	 * @description 获取值
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function get($name);

	/**
	 * @description 设置值
	 *
	 * @param string $name
	 *
	 * @param mixed $val
	 *
	 * @return null
	 */
	public function __set($name, $val);

	/**
	 * @description 获取值
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get($name);

	/**
	 * @description 删除
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function del($name);

	/**
	 * @description 获取sessionID
	 *
	 * @return string
	 */
	public function getSessionId();

	/**
	 * @description 清除
	 *
	 * @return null
	 */
	public function clear();
}
