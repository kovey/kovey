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
	/**
	 * @description 构造函数
	 *
	 * @param Swoole\Http\Request $request
	 * 
	 * @return Request
	 */
	public function __construct(\Swoole\Http\Request $request);

	/**
	 * @description 判断是否是WEBSocket
	 *
	 * @return bool
	 */
    public function isWebSocket();

	/**
	 * @description 获取客户端IP
	 *
	 * @return string
	 */
    public function getClientIP();

	/**
	 * @description 获取浏览器信息
	 *
	 * @return string
	 */
    public function getBrowser();
	
	/**
	 * @description 获取客户端系统信息
	 *
	 * @return string
	 */
	public function getOS();

	/**
	 * @description 获取POST请求数据
	 *
	 * @param string $name
	 *
	 * @param string $default
	 *
	 * @return string
	 */
	public function getPost($name = '', $default = '');

	/**
	 * @description 获取GET请求数据
	 *
	 * @param string $name
	 *
	 * @param string $default
	 *
	 * @return string
	 */
	public function getQuery($name = '', $default = '');

	/**
	 * @description 获取PUT请求数据
	 *
	 * @param string $name
	 *
	 * @param string $default
	 *
	 * @return string
	 */
	public function getPut($name = '', $default = '');

	/**
	 * @description 获取DELETE请求数据
	 *
	 * @param string $name
	 *
	 * @param string $default
	 *
	 * @return string
	 */
	public function getDelete($name = '', $default = '');

	/**
	 * @description 获取METHOD
	 *
	 * @return string
	 */
	public function getMethod();

	/**
	 * @description 获取URI
	 *
	 * @return string
	 */
	public function getUri();

	/**
	 * @description 获取参数
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getParam($key);

	/**
	 * @description 获取baseurl
	 *
	 * @return string
	 */
	public function getBaseUrl();

	/**
	 * @description 设置控制器
	 *
	 * @param string $controller
	 * 
	 * @return Request
	 */
	public function setController($controller);

	/**
	 * @description 设置Action
	 *
	 * @param string $action
	 * 
	 * @return Request
	 */
	public function setAction($action);

	/**
	 * @description 获取ACTION
	 * 
	 * @return string
	 */
	public function getAction();

	/**
	 * @description 获取控制器
	 * 
	 * @return string
	 */
	public function getController();

	/**
	 * @description 获取原始数据
	 * 
	 * @return string
	 */
	public function getPhpinput();

	/**
	 * @description 获取cookie
	 * 
	 * @return Array
	 */
	public function getCookie();

	/**
	 * @description 获取头信息
	 *
	 * @param string $name
	 * 
	 * @return string
	 */
	public function getHeader($name);

	/**
	 * @description 设置Session
	 *
	 * @param SessionInterface $session
	 * 
	 * @return null
	 */
	public function setSession(SessionInterface $session);

	/**
	 * @description 获取Sesstion
	 * 
	 * @return SessionInterface
	 */
	public function getSession();

    /**
     * @description 获取文件
     *
     * @return Array
     */
    public function getFiles();

    /**
     * @description 跨域攻击处理
     *
     * @return null
     */
    public function processCors();
}
