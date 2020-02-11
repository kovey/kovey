<?php
/**
 *
 * @description 请求接口
 *
 * @package     Request
 *
 * @time        2019-10-17 23:41:10
 *
 * @file  vendor/Kovey\Web/App/Http/Request/RequestInterface.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Http\Request;

use Kovey\Web\App\Http\Session\SessionInterface;

interface RequestInterface
{
	public function __construct(\Swoole\Http\Request $request);

    public function unsetGlobal();

    public function isWebSocket();

    public function getClientIP();

    public function getBrowser();
	
	public function getOS();

	public function getPost($name = '', $default = '');

	public function getQuery($name = '', $default = '');

	public function getPut($name = '', $default = '');

	public function getDelete($name = '', $default = '');

	public function getMethod();

	public function getUri();

	public function getParam($key);

	public function getBaseUrl();

	public function setController($controller);

	public function setAction($action);

	public function getAction();

	public function getController();

	public function getPhpinput();

	public function getCookie();

	public function getHeader($name);

	public function setSession(SessionInterface $session);

	public function getSession();
}
