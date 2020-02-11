<?php
/**
 *
 * @description 
 * todo 内部调用
 * 未来实现RPC
 * 协程Client
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

class HttpClient
{
	private $client;

	public function __construct($host, $port)
	{
		$this->client = Swoole\Coroutine\Http\Client($host, $port);
		$this->client->setHeaders([
			"User-Agent" => 'Chrome/49.0.2587.3',
			'Accept' => 'text/html,application/xhtml+xml,application/xml,application/json',
			'Accept-Encoding' => 'gzip',
		]);
	}

	public function post($url, $data)
	{
		$this->client->post($url, $data);
	}

	public function __destruct()
	{
		$this->client->close();
	}
}
