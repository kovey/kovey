<?php
/**
 *
 * @description 简单的CURL封装，必须有curl扩展
 *
 * @package     Components\Remote
 *
 * @time        Tue Sep 24 09:09:54 2019
 *
 * @class       vendor/Kovey/Components/Remote/Curl.php
 *
 * @author      kovey
 */
namespace Kovey\Remote;

use Kovey\Components\Result\Error;
use Kovey\Components\Result\ErrorCode;
use Kovey\Components\Result\Success;

class Curl
{
    private $ch;

    private $userAgent;

    private $contentType;

	private $headers;

    public function __construct()
    {
        $this->ch = curl_init();
        $this->userAgent = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:53.0) Gecko/20100101 Firefox/53.0';
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 5);
		$this->headers = array();
    }

    public function post($url, $params)
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->userAgent);

        return $this->run($url, $params);
    }

    public function get($url, $params = false)
    {
        if ($params !== false) {
            curl_setopt($this->ch, CURLOPT_URL, $url . '?' . $params);
        } else {
            curl_setopt($this->ch, CURLOPT_URL, $url);
        }
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->userAgent);
        return $this->run($url, $params);
    }

    public function setContentType($type, $charset = 'UTF-8')
    {
		$this->headers[] = 'Content-Type:' . $type . ';charset=' . $charset;
    }

	public function addHeader($key, $val)
	{
		$this->headers[] = $key . ':' . $val;
	}

    public function setReferer($referer)
    {
        curl_setopt($this->ch, CURLOPT_REFERER, $referer);
    }

    public function setReqContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    private function run($url, $params)
    {
		if (count($this->headers) > 0) {
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
		}

        $result = curl_exec($this->ch);
        if ($result === false || curl_errno($this->ch) != 0) {
            return Error::getArray(ErrorCode::CURL_RUN_ERROR, 'request remote error: ' . curl_error($this->ch));
        }

        if (empty($result)) {
            return Error::getArray(ErrorCode::CURL_RUN_ERROR, 'response is empty');
        }

        return Success::getArray($result);
    }

    public function setProxy($url, $port = 80, $user = false, $pass = false)
    {
        curl_setopt($this->ch, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($this->ch, CURLOPT_PROXYPORT, $port);
        curl_setopt($this->ch, CURLOPT_PROXY, $url);
        if (!$user || !$pass) {
            curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $user . ':' . $pass);
        }
    }

    public function __destruct()
    {
        curl_close($this->ch);
    }
}
