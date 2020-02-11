<?php
/**
 *
 * @description Response HTTP数据返回的封装
 *
 * @package     App\Http\Response
 *
 * @time        Tue Sep 24 08:57:32 2019
 *
 * @class       vendor/Kovey\Web/App/Http/Response/Response.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Http\Response;

class Response implements ResponseInterface
{
    private $protocol = 'HTTP/1.1';
    private $status = 200;

    private $head;
    private $cookie;
    private $body;

	public function __construct()
	{
		$this->head = array();
		$this->cookie = array();
		$this->head['Server'] = 'kovey framework';
		$this->head['Connection'] = 'keep-alive';
		$this->head['Content-Type'] = 'text/html; charset=utf-8';
	}

    public function status($code)
    {
        $this->status = $code;
		return $this;
    }

	public function redirect($url)
	{
		$this->status(302);
		$this->setHeader('Location', $url);
		return $this;
	}

    public function setHeader($key, $value)
    {
        $this->head[$key] = $value;
		return $this;
    }

    public function setCookie($name, $value = null, $expire = null, $path = '/', $domain = null, $secure = null, $httponly = null)
    {
        if ($value == null) {
            $value = 'deleted';
        }
        $cookie = "$name=$value";
        if ($expire) {
            $cookie .= "; expires=" . date("D, d-M-Y H:i:s T", $expire);
		}

        if ($path) {
            $cookie .= "; path=$path";
        }
        if ($secure) {
            $cookie .= "; secure";
        }
        if ($domain) {
            $cookie .= "; domain=$domain";
        }
        if ($httponly) {
            $cookie .= '; httponly';
        }
        $this->cookie[] = $cookie;
		return $this;
    }

    public function addHeaders(array $header)
    {
        $this->head = array_merge($this->head, $header);
		return $this;
    }

    public function getHeader($fastcgi = false)
    {
        $out = '';
        if ($fastcgi) {
            $out .= 'Status: '.$this->status.' '.self::$HTTP_HEADERS[$this->http_status]."\r\n";
        } else {
            if (isset($this->head[0])) {
                $out .= $this->head[0]."\r\n";
                unset($this->head[0]);
            } else {
                $out = "HTTP/1.1 200 OK\r\n";
            }
        }

        if (!isset($this->head['Server'])) {
            $this->head['Server'] = 'kovey framework';
        }
        if (!isset($this->head['Content-Type'])) {
            $this->head['Content-Type'] = 'text/html; charset=utf-8';
        }
        if (!isset($this->head['Content-Length'])) {
            $this->head['Content-Length'] = strlen($this->body);
        }
        foreach($this->head as $k=>$v) {
            $out .= $k . ': ' . $v . "\r\n";
        }
        if (!empty($this->cookie) && is_array($this->cookie)) {
            foreach($this->cookie as $v) {
                $out .= "Set-Cookie: $v\r\n";
            }
        }
        $out .= "\r\n";
        return $out;
    }

    public function noCache()
    {
        $this->head['Cache-Control'] = 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0';
        $this->head['Pragma'] = 'no-cache';
    }

	public function getBody()
	{
		return $this->body;
	}

	public function getHead()
	{
		return $this->head;
	}

	public function getCookie()
	{
		return $this->cookie;
	}

	public function setBody($body)
	{
		$this->body = $body;
		$this->head['Content-Length'] = strlen($this->body);
	}

	public function toArray() : array
	{
		return array(
			'httpCode' => $this->status,
			'content' => $this->body,
			'header' => $this->getHead(),
			'cookie' => $this->getCookie()
		);
	}

	public function clearBody()
	{
		$this->body = '';
		$this->head['Content-Length'] = 0;
	}

	public function getHttpCode()
	{
		return $this->status;
	}
}
