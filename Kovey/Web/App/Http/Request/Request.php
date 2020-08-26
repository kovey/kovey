<?php
/**
 *
 * @description HTTP请求的封装
 *
 * @package     App\Http\Request
 *
 * @time        Tue Sep 24 08:58:22 2019
 *
 * @class       vendor/Kovey\Web/App/Http/Request/Request.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Http\Request;

use Kovey\Util\Json;
use Kovey\Web\App\Http\Session\SessionInterface;

class Request implements RequestInterface
{
	/**
	 * @description 请求时间
	 *
	 * @var int
	 */
    private $time;

	/**
	 * @description 请求IP
	 *
	 * @var string
	 */
    private $remote_ip;

	/**
	 * @description 远程信息
	 *
	 * @var Array
	 */
    private $remote;

	/**
	 * @description 原始请求对象
	 *
	 * @var Swoole\Http\Request
	 */
	private $req;

	/**
	 * @description 请求信息
	 *
	 * @var Array
	 *
	 */
	private $request;

	/**
	 * @description 请求BODY
	 *
	 * @var string
	 */
	private $body;

	/**
	 * @description 服务器信息
	 *
	 * @var Array
	 */
	private $server;

	/**
	 * @description 控制器
	 *
	 * @var string
	 */
	private $controller;

	/**
	 * @description ACTION
	 *
	 * @var string
	 */
	private $action;

	/**
	 * @description 请求参数
	 *
	 * @var Array
	 */
	private $params;

	/**
	 * @description post请求参数
	 *
	 * @var Array
	 */
	private $post;

	/**
	 * @description GET请求参数
	 *
	 * @var Array
	 */
	private $get;

	/**
	 * @description PUT请求参数
	 *
	 * @var Array
	 */
	private $put;

	/**
	 * @description DELETE 请求参数
	 *
	 * @var Array
	 */
	private $delete;

	/**
	 * @description 绘画
	 *
	 * @var SessionInterface
	 */
	private $session;

	/**
	 * @description 构造函数
	 *
	 * @param Swoole\Http\Request $request
	 * 
	 * @return Request
	 */
	public function __construct(\Swoole\Http\Request $request)
	{
		$this->req = $request;
		$this->server = $this->req->server;
		$this->parseData();
		$this->setGlobal();
		$this->params = array();
		$this->processParams();
	}

	/**
	 * @description 构造函数
	 * 
	 * @return null
	 */
	private function processParams()
	{
		$info = explode('/', $this->getUri());
		$len = count($info);
		if ($len < 5) {
			return;
		}

		for ($i = 3; $i < $len;) {
			$this->params[$info[$i]] = $info[$i + 1];
			$i += 2;
		}
	}

	/**
	 * @description 解析数据
	 * 
	 * @return null
	 */
	private function parseData()
	{
		$cType = explode(';', $this->req->header['content-type'] ?? '')[0];
		$method = $this->getMethod();
		if ($cType === 'application/json') {
			$data = Json::decode($this->req->rawContent());
			if (empty($data)) {
				$data = array();
			}
			if ($method === 'get') {
				$this->get = $data;
				return;
			}

			if ($method === 'post') {
				$this->post = $data;
				return;
			}

			if ($method === 'put') {
				$this->put = $data;
				return;
			}

			if ($method === 'delete') {
				$this->delete = $data;
			}

			return;
		}

		if ($method === 'get') {
			$this->get = is_array($this->req->get) ? $this->req->get : array(); 
		} else if ($method === 'post') {
			$this->post = is_array($this->req->post) ? $this->req->post : array(); 
		} else if ($method === 'put') {
			$this->put = is_array($this->req->post) ? $this->req->post : array(); 
		} else if ($method === 'delete') {
			$this->delete = is_array($this->req->post) ? $this->req->post : array(); 
		}

	}

	/**
	 * @description 设置全局参数
	 * 
	 * @return null
	 */
    private function setGlobal()
    {
        foreach ($this->req->header as $key => $value) {
            $_key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
            $this->server[$_key] = $value;
        }
    }

	/**
	 * @description 销毁全局参数
	 *
	 * @return null
	 */
    public function unsetGlobal()
    {
        $_REQUEST = $_SESSION = $_COOKIE = $_FILES = $_POST = $_SERVER = $_GET = array();
    }

	/**
	 * @description 判断是否是WEBSocket
	 *
	 * @return bool
	 */
    public function isWebSocket()
    {
        return isset($this->header['Upgrade']) && strtolower($this->header['Upgrade']) == 'websocket';
    }

	/**
	 * @description 获取客户端IP
	 *
	 * @return string
	 */
    public function getClientIP()
    {
        if (isset($this->server['HTTP_X_REAL_IP']) and strcasecmp($this->server['HTTP_X_REAL_IP'], 'unknown')) {
            return $this->server['HTTP_X_REAL_IP'];
        }
        if (isset($this->server['HTTP_CLIENT_IP']) and strcasecmp($this->server['HTTP_CLIENT_IP'], 'unknown')) {
            return $this->server['HTTP_CLIENT_IP'];
        }
        if (isset($this->server['HTTP_X_FORWARDED_FOR']) and strcasecmp($this->server['HTTP_X_FORWARDED_FOR'], 'unknown')) {
            return $this->server['HTTP_X_FORWARDED_FOR'];
        }
        if (isset($this->server['REMOTE_ADDR'])) {
            return $this->server['REMOTE_ADDR'];
        }

		if (isset($this->server['remote_addr'])) {
			return $this->server['remote_addr'];
		}

        return '';
    }

	/**
	 * @description 获取浏览器信息
	 *
	 * @return string
	 */
    public function getBrowser()
    {
        $sys = $this->server['HTTP_USER_AGENT'];
        if (stripos($sys, "Firefox/") > 0) {
            preg_match("/Firefox\/([^;)]+)+/i", $sys, $b);
            $exp[0] = "Firefox";
            $exp[1] = $b[1];
        } else if (stripos($sys, "Maxthon") > 0) {
            preg_match("/Maxthon\/([\d\.]+)/", $sys, $aoyou);
            $exp[0] = "傲游";
            $exp[1] = $aoyou[1];
        } else if (stripos($sys, "MSIE") > 0) {
            preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
            $exp[0] = "IE";
            $exp[1] = $ie[1];
        } else if (stripos($sys, "OPR") > 0) {
            preg_match("/OPR\/([\d\.]+)/", $sys, $opera);
            $exp[0] = "Opera";
            $exp[1] = $opera[1];
        }
        else if (stripos($sys, "Edge") > 0)
        {
            preg_match("/Edge\/([\d\.]+)/", $sys, $Edge);
            $exp[0] = "Edge";
            $exp[1] = $Edge[1];
        }
        else if (stripos($sys, "Chrome") > 0)
        {
            preg_match("/Chrome\/([\d\.]+)/", $sys, $google);
            $exp[0] = "Chrome";
            $exp[1] = $google[1];
        }
        else if (stripos($sys, 'rv:') > 0 && stripos($sys, 'Gecko') > 0)
        {
            preg_match("/rv:([\d\.]+)/", $sys, $IE);
            $exp[0] = "IE";
            $exp[1] = $IE[1];
        }
        else
        {
            $exp[0] = "Unkown";
            $exp[1] = "";
        }

        return $exp[0] . '(' . $exp[1] . ')';
    }
	
	/**
	 * @description 获取客户端系统信息
	 *
	 * @return string
	 */
	public function getOS()
    {
        $agent = $this->server['HTTP_USER_AGENT'];
        if (preg_match('/win/i', $agent) && strpos($agent, '95'))
        {
            $os = 'Windows 95';
        }
        else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90'))
        {
            $os = 'Windows ME';
        }
        else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent))
        {
            $os = 'Windows 98';
        }
        else if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent))
        {
            $os = 'Windows Vista';
        }
        else if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent))
        {
            $os = 'Windows 7';
        }
        else if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent))
        {
            $os = 'Windows 8';
        }
        else if (preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent))
        {
            $os = 'Windows 10';
        }
        else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent))
        {
            $os = 'Windows XP';
        }
        else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent))
        {
            $os = 'Windows 2000';
        }
        else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent))
        {
            $os = 'Windows NT';
        }
        else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent))
        {
            $os = 'Windows 32';
        }
        else if (preg_match('/linux/i', $agent))
        {
            $os = 'Linux';
        }
        else if (preg_match('/unix/i', $agent))
        {
            $os = 'Unix';
        }
        else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent))
        {
            $os = 'SunOS';
        }
        else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent))
        {
            $os = 'IBM OS/2';
        }
        else if (preg_match('/Mac/i', $agent) && preg_match('/PC/i', $agent))
        {
            $os = 'Macintosh';
        }
        else if (preg_match('/PowerPC/i', $agent))
        {
            $os = 'PowerPC';
        }
        else if (preg_match('/AIX/i', $agent))
        {
            $os = 'AIX';
        }
        else if (preg_match('/HPUX/i', $agent))
        {
            $os = 'HPUX';
        }
        else if (preg_match('/NetBSD/i', $agent))
        {
            $os = 'NetBSD';
        }
        else if (preg_match('/BSD/i', $agent))
        {
            $os = 'BSD';
        }
        else if (preg_match('/OSF1/i', $agent))
        {
            $os = 'OSF1';
        }
        else if (preg_match('/IRIX/i', $agent))
        {
            $os = 'IRIX';
        }
        else if (preg_match('/FreeBSD/i', $agent))
        {
            $os = 'FreeBSD';
        }
        else if (preg_match('/teleport/i', $agent))
        {
            $os = 'teleport';
        }
        else if (preg_match('/flashget/i', $agent))
        {
            $os = 'flashget';
        }
        else if (preg_match('/webzip/i', $agent))
        {
            $os = 'webzip';
        }
        else if (preg_match('/offline/i', $agent))
        {
            $os = 'offline';
        }
        else
        {
            $os = 'Unknown';
        }

        return $os;
    }

	/**
	 * @description 获取POST请求数据
	 *
	 * @param string $name
	 *
	 * @param string $default
	 *
	 * @return string
	 */
	public function getPost($name = '', $default = '')
	{
		if (empty($name)) {
			return $this->post;
		}

		return $this->post[$name] ?? $default;
	}

	/**
	 * @description 获取GET请求数据
	 *
	 * @param string $name
	 *
	 * @param string $default
	 *
	 * @return string
	 */
	public function getQuery($name = '', $default = '')
	{
		if (empty($name)) {
			return $this->get;
		}

		return $this->get[$name] ?? $default;
	}

	/**
	 * @description 获取PUT请求数据
	 *
	 * @param string $name
	 *
	 * @param string $default
	 *
	 * @return string
	 */
	public function getPut($name = '', $default = '')
	{
		if (empty($name)) {
			return $this->put;
		}

		return $this->put[$name] ?? $default;
	}

	/**
	 * @description 获取DELETE请求数据
	 *
	 * @param string $name
	 *
	 * @param string $default
	 *
	 * @return string
	 */
	public function getDelete($name = '', $default = '')
	{
		if (empty($name)) {
			return $this->delete;
		}

		return $this->delete[$name] ?? $default;
	}

	/**
	 * @description 获取METHOD
	 *
	 * @return string
	 */
	public function getMethod()
	{
		return strtolower($this->server['request_method']);
	}

	/**
	 * @description 获取URI
	 *
	 * @return string
	 */
	public function getUri()
	{
		return $this->server['request_uri'] ?? '/';
	}

	/**
	 * @description 获取参数
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getParam($key)
	{
		return $this->params[$key] ?? '';
	}

	/**
	 * @description 获取baseurl
	 *
	 * @return string
	 */
	public function getBaseUrl()
	{
		return $this->server['HTTP_HOST'] ?? '';
	}

	/**
	 * @description 设置控制器
	 *
	 * @param string $controller
	 * 
	 * @return Request
	 */
	public function setController($controller)
	{
		$this->controller = $controller;
		return $this;
	}

	/**
	 * @description 设置Action
	 *
	 * @param string $action
	 * 
	 * @return Request
	 */
	public function setAction($action)
	{
		$this->action = $action;
		return $this;
	}

	/**
	 * @description 获取ACTION
	 * 
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * @description 获取控制器
	 * 
	 * @return string
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * @description 获取原始数据
	 * 
	 * @return string
	 */
	public function getPhpinput()
	{
		return $this->req->rawContent();
	}

	/**
	 * @description 获取cookie
	 * 
	 * @return Array
	 */
	public function getCookie()
	{
		return $this->req->cookie;
	}

	/**
	 * @description 获取头信息
	 *
	 * @param string $name
	 * 
	 * @return string
	 */
	public function getHeader($name)
	{
		return $this->req->header[strtolower($name)] ?? '';
	}

	/**
	 * @description 设置Session
	 *
	 * @param SessionInterface $session
	 * 
	 * @return null
	 */
	public function setSession(SessionInterface $session)
	{
		$this->session = $session;
	}

	/**
	 * @description 获取Sesstion
	 * 
	 * @return SessionInterface
	 */
	public function getSession()
	{
		return $this->session;
	}
}
