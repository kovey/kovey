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
    private $time;

    private $remote_ip;

    private $remote;
	
	private $req;

	private $request;

	private $body;

	private $server;

	private $controller;

	private $action;

	private $params;

	private $post;

	private $get;

	private $put;

	private $delete;

	private $session;

	public function __construct(\Swoole\Http\Request $request)
	{
		$this->req = $request;
		$this->server = $this->req->server;
		$this->parseData();
		$this->setGlobal();
		$this->params = array();
		$this->processParams();
	}

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

	private function parseData()
	{
		$_GET = array();
		$_POST = array();

		$cType = explode(';', $this->req->header['content-type'] ?? '')[0];
		$method = $this->getMethod();
		if ($cType === 'application/json') {
			$data = Json::decode($this->req->rawContent());
			if (empty($data)) {
				$data = array();
			}
			if ($method === 'get') {
				$this->get = $data;
				$_GET = $data;
				return;
			}

			if ($method === 'post') {
				$this->post = $data;
				$_POST = $data;
				return;
			}

			if ($method === 'put') {
				$this->put = $data;
				$_POST = $data;
				return;
			}

			if ($method === 'delete') {
				$this->delete = $data;
				$_POST = $data;
			}

			return;
		}

		if ($method === 'get') {
			$this->get = is_array($this->req->get) ? $this->req->get : array(); 
			$_GET = $this->get;
		} else if ($method === 'post') {
			$this->post = is_array($this->req->post) ? $this->req->post : array(); 
			$_POST = $this->post;
		} else if ($method === 'put') {
			$this->put = is_array($this->req->post) ? $this->req->post : array(); 
			$_POST = $this->put;
		} else if ($method === 'delete') {
			$this->delete = is_array($this->req->post) ? $this->req->post : array(); 
			$_POST = $this->delete;
		}

	}

    private function setGlobal()
    {
        foreach ($this->req->header as $key => $value) {
            $_key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
            $this->server[$_key] = $value;
        }

        $_FILES = $this->req->files == null ? array() : $this->req->files;
        $_COOKIE = $this->req->cookie == null ? array() : $this->req->cookie;
        $_SERVER = $this->server;
        $this->request = $_REQUEST = array_merge($_GET, $_POST, $_COOKIE);
    }

    public function unsetGlobal()
    {
        $_REQUEST = $_SESSION = $_COOKIE = $_FILES = $_POST = $_SERVER = $_GET = array();
    }

    public function isWebSocket()
    {
        return isset($this->header['Upgrade']) && strtolower($this->header['Upgrade']) == 'websocket';
    }

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

	public function getPost($name = '', $default = '')
	{
		if (empty($name)) {
			return $this->post;
		}

		return $this->post[$name] ?? $default;
	}

	public function getQuery($name = '', $default = '')
	{
		if (empty($name)) {
			return $this->get;
		}

		return $this->get[$name] ?? $default;
	}

	public function getPut($name = '', $default = '')
	{
		if (empty($name)) {
			return $this->put;
		}

		return $this->put[$name] ?? $default;
	}

	public function getDelete($name = '', $default = '')
	{
		if (empty($name)) {
			return $this->delete;
		}

		return $this->delete[$name] ?? $default;
	}

	public function getMethod()
	{
		return strtolower($this->server['request_method']);
	}

	public function getUri()
	{
		return $this->server['request_uri'] ?? '/';
	}

	public function getParam($key)
	{
		return $this->params[$key] ?? '';
	}

	public function getBaseUrl()
	{
		return $this->server['HTTP_HOST'] ?? '';
	}

	public function setController($controller)
	{
		$this->controller = $controller;
		return $this;
	}

	public function setAction($action)
	{
		$this->action = $action;
		return $this;
	}

	public function getAction()
	{
		return $this->action;
	}

	public function getController()
	{
		return $this->controller;
	}

	public function getPhpinput()
	{
		return $this->req->rawContent();
	}

	public function getCookie()
	{
		return $this->req->cookie;
	}

	public function getHeader($name)
	{
		return $this->req->header[strtolower($name)] ?? '';
	}

	public function setSession(SessionInterface $session)
	{
		$this->session = $session;
	}

	public function getSession()
	{
		return $this->session;
	}
}
