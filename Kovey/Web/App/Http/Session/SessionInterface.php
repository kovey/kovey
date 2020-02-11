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
	public function set($name, $val);

	public function get($name);

	public function __set($name, $val);

	public function __get($name);

	public function del($name);

	public function getSessionId();

	public function clear();
}
