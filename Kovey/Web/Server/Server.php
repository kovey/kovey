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
	/**
	 * @description 服务器
	 *
	 * @var Swoole\Http\Server
	 */
    private $serv;

	/**
	 * @description 配置
	 *
	 * @var Array
	 */
    private $config;

	/**
	 * @description 事件
	 *
	 * @var Array
	 */
	private $events;

	/**
	 * @description 允许的事件类型
	 *
	 * @var Array
	 */
	private $eventsTypes;

	/**
	 * @description 静态目录
	 *
	 * @var Array
	 */
	private $staticDir;

	/**
	 * @description 是否在docker中运行
	 *
	 * @var bool
	 */
	private $isRunDocker;

	/**
	 * @description 构造
	 *
	 * @param Array $config
	 *
	 * @return Server
	 */
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
            'console' => 1,
            'monitor' => 1
		);

		$this->init();
    }

	/**
	 * @description 事件监听
	 *
	 * @param string $name
	 *
	 * @param $callback
	 *
	 * @return Server
	 */
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

	/**
	 * @description 初始化
	 *
	 * @return Server
	 */
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
            'max_coroutine' => $this->config['max_co'],
            'package_max_length' => $this->getBytes($this->config['package_max_length'])
        ));

		$this->scanStaticDir();

		if (isset($this->events['startedBefore'])) {
			call_user_func($this->events['startedBefore'], $this);
		}

        $this->initCallBack();
		return $this;
    }

    private function getBytes($num)
    {
        $unit = strtoupper(substr($num, -1));
        $num = intval(substr($num, 0, -1));
        if ($unit === 'B') {
            return $num;
        }

        if ($unit === 'K') {
            return $num * 1024;
        }

        if ($unit === 'M') {
            return $num * 1024 * 1024;
        }

        if ($unit === 'G') {
            return $num * 1024 * 1024 * 1024;
        }

        return 0;
    }

	/**
	 * @description 扫描静态资源目录
	 *
	 * @return null
	 */
	private function scanStaticDir()
	{
		$this->staticDir = array();

		$dir = APPLICATION_PATH . $this->config['document_root'];
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

	/**
	 * @description 初始化回调
	 *
	 * @return Server
	 */
    private function initCallBack()
    {
        $this->serv->on('workerStart', array($this, 'workerStart'));
        $this->serv->on('managerStart', array($this, 'managerStart'));
        $this->serv->on('request', array($this, 'request'));
        $this->serv->on('close', array($this, 'close'));
        $this->serv->on('pipeMessage', array($this, 'pipeMessage'));

		return $this;
    }

	/**
	 * @description 监听进程间通讯
	 *
	 * @param Swoole\Http\Server $serv
	 *
	 * @param int $workerId
	 *
	 * @param mixed $data
	 *
	 * @return null
	 */
    public function pipeMessage($serv, $workerId, $data)
    {
        try {
			if (!isset($this->events['console'])) {
				return;
			}

			go(function () use ($data, $workerId) {
				call_user_func($this->events['console'], $data['p'] ?? '', $data['m'] ?? '', $data['a'] ?? array());
			});
        } catch (\Throwable $e) {
			if ($this->isRunDocker) {
				Logger::writeExceptionLog(__LINE__, __FILE__, $e);
			} else {
				echo $e->getMessage() . PHP_EOL . $e->getTraceAsString();
			}
        }
    }

	/**
	 * @description Manager 进程启动
	 *
	 * @param Swoole\Http\Server $serv
	 *
	 * @return null
	 */
    public function managerStart($serv)
    {
		ko_change_process_name($this->config['name'] . ' master');
    }

	/**
	 * @description Worker 进程启动
	 *
	 * @param Swoole\Http\Server $serv
	 *
	 * @param int $workerId
	 *
	 * @return null
	 */
    public function workerStart($serv, $workerId)
    {
        ko_change_process_name($this->config['name'] . ' worker');

		if (!isset($this->events['init'])) {
			return;
		}

		call_user_func($this->events['init'], $this);
    }

	/**
	 * @description 判断是否是静态资源目录
	 *
	 * @param string $uri
	 *
	 * @return bool
	 */
	private function isStatic($uri)
	{
		$info = explode('/', $uri);
		if (count($info) < 2) {
			return false;
		}

		return in_array($info[1], $this->staticDir, true);
	}

	/**
	 * @description Worker 进程启动
	 *
	 * @param Swoole\Http\Request $request
	 *
	 * @param Swoole\Http\Response $response
	 *
	 * @return null
	 */
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

        $begin = microtime(true);
        $time = time();

		$result = array();
		try {
			$result = call_user_func($this->events['workflow'], $request);
		} catch (\Throwable $e) {
			if ($this->isRunDocker) {
				Logger::writeExceptionLog(__LINE__, __FILE__, $e);
			} else {
				echo $e->getMessage() . PHP_EOL . $e->getTraceAsString();
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
        $this->monitor($begin, microtime(true), $request->server['request_uri'] ?? '/', $request->rawContent(), $request->server['remote_addr'] ?? '', $time, $httpCode);
    }

    private function monitor($begin, $end, $uri, $params, $ip, $time, $code)
    {
        if (!isset($this->events['monitor'])) {
            return;
        }

        try {
            call_user_func($this->events['monitor'], array(
                'delay' => round(($end - $begin) * 1000, 2),
                'path' => $uri,
                'params' => $params,
                'ip' => $ip,
                'time' => $time,
                'timestamp' => date('Y-m-d H:i:s', $time),
                'minute' => date('YmdHi', $time),
                'http_code' => $code
            ));
        } catch (\Throwable $e) {
            Logger::writeExceptionLog(__LINE__, __FILE__, $e);
        }
    }

	/**
	 * @description 启动
	 *
	 * @return null
	 */
    public function start()
    {
        $this->serv->start();
    }

	/**
	 * @description 链接关闭
	 *
	 * @param Swoole\Http\Server $server
	 *
	 * @param int $fd
	 *
	 * @param int $reactorId
	 *
	 * @return null
	 */
    public function close($server, int $fd, int $reactorId)
    {}

	/**
	 * @description 获取服务器对象
	 *
	 * @return Swoole\Http\Server
	 */
	public function getServ()
	{
		return $this->serv;
	}
}
