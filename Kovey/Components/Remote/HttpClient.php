<?php
/**
 *
 * @description 访问第三方
 *
 * @package     Components\Remote
 *
 * @time        Tue Sep 24 09:10:34 2019
 *
 * @class       vendor/Kovey/Components/Remote/HttpClient.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Remote;

use Swoole\Coroutine\Http\Client;

class HttpClient
{
	/**
	 * @description 客户端
	 *
	 * @var Swoole\Coroutine\Http\Client
	 */
	private $client;

	/**
	 * @description 头信息
	 *
	 * @var Array
	 */
	private $headers;

	/**
	 * @description 构造对象
	 *
	 * @param string $url
	 */
	public function __construct(string $url)
	{
		$url = parse_url($url);
		$port = $url['port'] ?? 80;
		$ssl = false;
		if (isset($url['scheme'])) {
			if ($url['scheme'] === 'https') {
				$ssl = true;
				if (!isset($url['port'])) {
					$port = 443;
				}
			}
		}

		$this->client = Client($url['host'], $port, $ssl);
		$this->headers = [
			"User-Agent" => 'Chrome/49.0.2587.3',
			'Accept' => 'text/html,application/xhtml+xml,application/xml,application/json',
			'Accept-Encoding' => 'gzip',
		];
	}

	/**
	 * @description 设置超时时间
	 *
	 * @param int $timeout
	 *
	 * @return HttpClient
	 */
	public function setTimeout($timeout = 10)
	{
		$this->client->set(array(
			'timeout' => $timeout
		));
		return $this;
	}

	/**
	 * @description 添加头信息
	 *
	 * @param string $key
	 *
	 * @param mixed $val
	 *
	 * @return HttpClient
	 */
	public function addHeader(string $key, $val)
	{
		$this->headers[strtolower($key)] = $val;
		return $this;
	}

	/**
	 * @description 发送POST请求
	 *
	 * @param string $path
	 *
	 * @param mixed $data
	 *
	 * @return string
	 */
	public function post(string $path, $data)
	{
		if (!empty($this->headers)) {
			$this->client->setHeaders($this->headers);
		}

		$this->client->post($path, $data);
		$result = $this->client->body;
		$this->client->close();

		return $result;
	}

	/**
	 * @description 发送GET请求
	 *
	 * @param string $path
	 *
	 * @param mixed $data
	 *
	 * @return string
	 */
	public function get($path, $data = '')
	{
		if (!empty($this->headers)) {
			$this->client->setHeaders($this->headers);
		}

		$url = $path;
		if (!empty($data)) {
			$url = $url . '?' . (is_array($data) ? http_build_query($data) : $data);
		}

		$this->client->get($url);
		$result = $this->client->body;
		$this->client->close();

		return $result;
	}

	/**
	 * @description 文件上传
	 *
	 * @param string $path
	 *
	 * @param Array $files
	 * 	array(
	 *		array(
	 *			'path' => 'path/to/file',
	 *			'name' => 'filed_name',
	 *			'mimeType' => 'mime type'
	 *		)
	 * 	)
	 *
	 * 	@param mixed $args
	 *
	 * @return string
	 */
	public function upload($path, Array $files, $args = array())
	{
		if (!empty($this->headers)) {
			$this->client->setHeaders($this->headers);
		}

		foreach ($files as $fileInfo) {
			$this->client->addFile($fileInfo['path'], $fileInfo['name'], $fileInfo['mimeType'], basename($fileInfo['path']));
		}

		$this->client->post($path, $args);
		$result = $this->client->body;
		$this->client->close();

		return $result;
	}
}
