<?php
/**
 *
 * @description websocket 服务
 *
 * @package     Server
 *
 * @time        Tue Sep 24 08:54:02 2019
 *
 * @class       vendor/Kovey\Web/Server/Server.php
 *
 * @author      kovey
 * todo 后续完善
 */
namespace Kovey\Web\Server;

class Websocket
{
    private $serv;

    private $config;

    private $userProcess;

	private $pools;

	private $events;

	private $eventsTypes;

	private $staticDir;

    public function __construct()
    {
        $this->config = Manager::get('core.server');
        $this->serv = new \Swoole\Http\Websocket($this->config['host'], intval($this->config['port']));
		$this->events = array();
		$this->eventsTypes = array('startedBefore', 'startedAfter', 'workflow', 'initPool', 'console');
		$this->pools = array();
        $this->userProcess = new UserProcess($this->config['worker_num']);
    }

	public function on(string $name, Callable $callback)
	{
		if (!in_array($name, $this->eventsTypes)) {
			return $this;
		}

		$this->events[$name] = $callback;
	}

    public function init()
    {
		$loggerDefault = APPLICATION_PATH . '/log/server/core.log';
		$logger = Manager::get('core.logger.server');
        $this->serv->set(array(
            'daemonize' => true,
			'http_compression' => true,
			'enable_static_handler' => true,
			'document_root' => APPLICATION_PATH . Manager::get('app.web.root'),
            'pid_file' => $this->config['pid_file'] ?? '/var/run/kovey.pid',
            'log_file' => is_dir($logger) ? $logger . '/core.log' : $loggerDefault,
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
		$dir = APPLICATION_PATH . Manager::get('app.web.root');
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
            $this->writeExceptionLog(__LINE__, __FILE__, $e);
        }
    }

    public function managerStart($serv)
    {
        ko_change_process_name(Manager::get('app.process.name') . ' core master');
    }

    public function workerStart($serv, $workerId)
    {
        ko_change_process_name(Manager::get('app.process.name') . ' core worker');

		if (Manager::get('db.pool.open') !== 'On') {
			return;
		}

		if (!isset($this->events['initPool'])) {
			return;
		}

		call_user_func($this->events['initPool'], $this);
		foreach ($this->pools as $pool) {
			if (!$pool instanceof PoolInterface) {
				continue;
			}

			$pool->init();
			if (count($pool->getErrors()) > 0) {
				$this->writeErrorLog(__LINE__, __FILE__, implode(',', $pool->getErrors()));
			}
		}
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
			$response->status('404');
			$response->header('content-type', 'text/html');
			$response->end('Page not found.');
			return;
		}

		$result = array();
		try {
			$result = call_user_func($this->events['workflow'], $request, $this);
		} catch (\Throwable $e) {
			$this->writeExceptionLog(__LINE__, __FILE__, $e);
			$result = array(
				'httpCode' => 500,
				'header' => array(
					'content-type' => 'text/html'
				),
				'content' => '',
				'cookie' => array()
			);
		}

		$response->status($result['httpCode']);
		foreach ($result['header'] as $k => $v) {
			$response->header($k, $v);
		}

		foreach ($result['cookie'] as $cookie) {
			$response->header('Set-Cookie', $cookie);
		}

        $response->end($result['content']);
    }

    public function start()
    {
        $this->serv->start();
    }

    public function close($server, int $fd, int $reactorId)
    {}

	public function getUserProcess()
	{
		return $this->userProcess;
	}

	public function writeInfoLog($line, $file, $msg)
	{
		$this->userProcess->push('logger', Logger::getInfoLog($line, $file, $msg));
	}

	public function writeWarningLog($line, $file, $msg)
	{
		$this->userProcess->push('logger', Logger::getWarningLog($line, $file, $msg));
	}

	public function writeErrorLog($line, $file, $msg)
	{
		$this->userProcess->push('logger', Logger::getErrorLog($line, $file, $msg));
	}

	public function writeExceptionLog($line, $file, \Throwable $e)
	{
		$this->userProcess->push('logger', Logger::getExceptionLog($line, $file, $e));
	}

	public function addPool($name, PoolInterface $pool)
	{
		$this->pools[$name] = $pool;
	}

	public function getPool($name)
	{
		return $this->pools[$name] ?? null;
	}

	public function addProcess($name, ProcessAbstract $process)
	{
		$process->setServer($this->serv);

		$this->userProcess->addProcess($name, $process);
	}
}
