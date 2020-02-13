<?php
/**
 *
 * @description http 服务
 *
 * @package     Server
 *
 * @time        Tue Sep 24 08:54:02 2019
 *
 * @class       vendor/Kovey\Web/Server/Server.php
 *
 * @author      kovey
 */
namespace Kovey\Web\Server;

use Swoole\Http\Response;
use Kovey\Components\Logger\Logger;

class Server
{
    private $serv;

    private $config;

	private $events;

	private $eventsTypes;

	private $staticDir;

	private $isRunDocker;

	public function __construct(Array $config)
	{
		$this->config = $config;
		$this->isRunDocker = ($this->config['run_docker'] ?? 'Off') === 'On';
        $this->serv = new \Swoole\Http\Server($this->config['host'], intval($this->config['port']));
		$this->events = array();
		$this->eventsTypes = array(
			'startedBefore' => 1, 
			'startedAfter' => 1, 
			'workflow' => 1, 
			'init' => 1, 
			'console' => 1
		);

		$this->init();
    }

	public function on(string $name, $callback)
	{
		if (!isset($this->eventsTypes[$name])) {
			return $this;
		}

		if (!is_callable($callback)) {
			return $this;
		}

		$this->events[$name] = $callback;
		return $this;
	}

    private function init()
    {
		$logDir = dirname($this->config['log_file']);
		if (!is_dir($logDir)) {
			mkdir($logDir, 0777, true);
		}

		$logDir = dirname($this->config['pid_file']);
		if (!is_dir($logDir)) {
			mkdir($logDir, 0777, true);
		}

        $this->serv->set(array(
            'daemonize' => !$this->isRunDocker,
			'http_compression' => true,
			'enable_static_handler' => true,
			'document_root' => APPLICATION_PATH . $this->config['document_root'],
            'pid_file' => $this->config['pid_file'] ?? '/var/run/kovey.pid',
            'log_file' => $this->config['log_file'],
            'worker_num' => $this->config['worker_num'],
			'enable_coroutine' => true,
			'max_coroutine' => $this->config['max_co']
        ));

		$this->scanStaticDir();

		if (isset($this->events['startedBefore'])) {
			call_user_func($this->events['startedBefore'], $this);
		}

        $this->initCallBack();
		return $this;
    }

	private function scanStaticDir()
	{
		$this->staticDir = array();

		$dir = $this->config['document_root'];
		if (!is_dir($dir)) {
			return;
		}

		foreach (scandir($dir) as $d) {
			if ($d === '.' || $d === '..') {
				continue;
			}

			$this->staticDir[] = $d;
		}
	}

    private function initCallBack()
    {
        $this->serv->on('workerStart', array($this, 'workerStart'));
        $this->serv->on('managerStart', array($this, 'managerStart'));
        $this->serv->on('request', array($this, 'request'));
        $this->serv->on('close', array($this, 'close'));
        $this->serv->on('pipeMessage', array($this, 'pipeMessage'));
    }

	public function handleFatal($response)
	{
		$error = error_get_last();
		switch ($error['type'] ?? null) {
			case E_ERROR :
			case E_PARSE :
			case E_CORE_ERROR :
			case E_COMPILE_ERROR :
				$response->status(500);
				$response->end(ErrorTemplate::getContent(500));
				break;
		}
	}

    public function pipeMessage($serv, $workerId, $data)
    {
        try {
			if (!isset($this->events['console'])) {
				return;
			}

			go(function () use ($data, $workerId) {
				call_user_func($this->events['console'], $data);
			});
        } catch (\Throwable $e) {
			if ($this->isRunDocker) {
				Logger::writeExceptionLog(__LINE__, __FILE__, $e);
			} else {
				echo $e->getMessage() . "\n" . $e->getTraceAsString();
			}
		} catch (\Exception $e) {
			if ($this->isRunDocker) {
				Logger::writeExceptionLog(__LINE__, __FILE__, $e);
			} else {
				echo $e->getMessage() . "\n" . $e->getTraceAsString();
			}
		}
    }

    public function managerStart($serv)
    {
		ko_change_process_name($this->config['name'] . ' master');
    }

    public function workerStart($serv, $workerId)
    {
        ko_change_process_name($this->config['name'] . ' worker');

		if (!isset($this->events['init'])) {
			return;
		}

		call_user_func($this->events['init'], $this);
    }

	private function isStatic($uri)
	{
		$info = explode('/', $uri);
		if (count($info) < 2) {
			return false;
		}

		return in_array($info[1], $this->staticDir, true);
	}

    public function request($request, $response)
	{
		if ($this->isStatic($request->server['request_uri'] ?? '')) {
			return;
		}

		if (!isset($this->events['workflow'])) {
			$response->status(500);
			$response->header('content-type', 'text/html');
			$response->end(ErrorTemplate::getContent(500));
			return;
		}

		register_shutdown_function(array($this, 'handleFatal'), $response);

		$result = array();
		try {
			$result = call_user_func($this->events['workflow'], $request);
		} catch (\Exception $e) {
			if ($this->isRunDocker) {
				Logger::writeExceptionLog(__LINE__, __FILE__, $e);
			} else {
				echo $e->getMessage() . "\n" . $e->getTraceAsString();
			}

			$result = array(
				'httpCode' => 500,
				'header' => array(
					'content-type' => 'text/html'
				),
				'content' => ErrorTemplate::getContent(500),
				'cookie' => array()
			);
		} catch (\Throwable $e) {
			if ($this->isRunDocker) {
				Logger::writeExceptionLog(__LINE__, __FILE__, $e);
			} else {
				echo $e->getMessage() . "\n" . $e->getTraceAsString();
			}
			$result = array(
				'httpCode' => 500,
				'header' => array(
					'content-type' => 'text/html'
				),
				'content' => ErrorTemplate::getContent(500),
				'cookie' => array()
			);
		}

		$httpCode = $result['httpCode'] ?? 500;
		$response->status($httpCode);

		$header = $result['header'] ?? array();
		foreach ($header as $k => $v) {
			$response->header($k, $v);
		}

		$cookie = $result['cookie'] ?? array();
		foreach ($cookie as $cookie) {
			$response->header('Set-Cookie', $cookie);
		}

        $response->end($httpCode == 200 ? $result['content'] : ErrorTemplate::getContent($httpCode));
    }

    public function start()
    {
        $this->serv->start();
    }

    public function close($server, int $fd, int $reactorId)
    {}

	public function getServ()
	{
		return $this->serv;
	}
}
