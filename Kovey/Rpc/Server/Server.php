<?php
/**
 * @description 短连接服务端
 *
 * @package Server
 *
 * @author kovey
 *
 * @time 2019-11-13 14:43:19
 *
 * @file kovey/Kovey\Rpc/Server/Server.php
 *
 */
namespace Kovey\Rpc\Server;

use Kovey\Rpc\Protocol\Json;
use Kovey\Rpc\Protocol\ProtocolInterface;
use Kovey\Components\Exception\BusiException;
use Kovey\Components\Logger\Logger;

class Server
{
	/**
	 * @description 服务器
	 *
	 * @var Swoole\Server
	 */
    private $serv;

	/**
	 * @description 配置
	 *
	 * @var Array
	 */
    private $conf;

	/**
	 * @description 事件
	 *
	 * @var Array
	 */
	private $events;

	/**
	 * @description 允许的事件
	 *
	 * @var Array
	 */
	private $allowEevents;

	/**
	 * @description 是否运行在docker中
	 *
	 * @var bool
	 */
	private $isRunDocker;

	/**
	 * @description 构造函数
	 *
	 * @param Array $conf
	 *
	 * @return Server
	 */
    public function __construct(Array $conf)
    {
        $this->conf = $conf;
		$this->isRunDocker = ($this->conf['run_docker'] ?? 'Off') === 'On';
        $this->serv = new \Swoole\Server($this->conf['host'], $this->conf['port']);
        $this->serv->set(array(
            'open_length_check' => true,
            'package_max_length' => Json::MAX_LENGTH,
            'package_length_type' => Json::PACK_TYPE,
            'package_length_offset' => Json::LENGTH_OFFSET,
            'package_body_offset' => Json::BODY_OFFSET,
			'enable_coroutine' => true,
			'worker_num' => $this->conf['worker_num'],
            'daemonize' => !$this->isRunDocker,
            'pid_file' => $this->conf['pid_file'],
            'log_file' => $this->conf['log_file'],
        ));


		$logDir = dirname($this->conf['log_file']);
		if (!is_dir($logDir)) {
			mkdir($logDir, 0777, true);
		}
		$pidDir = dirname($this->conf['pid_file']);
		if (!is_dir($pidDir)) {
			mkdir($pidDir, 0777, true);
		}

		$this->initAllowEvents()
			->initCallback();
    }

	/**
	 * @description 初始化允许的事件
	 *
	 * @return Server
	 */
	private function initAllowEvents()
	{
		$this->allowEevents = array(
			'handler' => 1,
			'pipeMessage' => 1,
			'initPool' => 1,
			'monitor' => 1
		);

		return $this;
	}

	/**
	 * @description 初始化回调
	 *
	 * @return Server
	 */
    private function initCallback()
    {
        $this->serv->on('connect', array($this, 'connect'));
        $this->serv->on('receive', array($this, 'receive'));
        $this->serv->on('close', array($this, 'close'));
        $this->serv->on('pipeMessage', array($this, 'pipeMessage'));
        $this->serv->on('workerStart', array($this, 'workerStart'));
        $this->serv->on('managerStart', array($this, 'managerStart'));
		return $this;
    }

	/**
	 * @description 致命错误处理
	 *
	 * @param int $fd
	 *
	 * @param ProtocolInterface $packet
	 *
	 * @param float $begin
	 *
	 * @param int $reqTime
	 * 
	 * @return null
	 */
	public function handleFatal($fd, $packet, $begin, $reqTime)
	{
		$end = microtime(true);
		$error = error_get_last();
		switch ($error['type'] ?? null) {
			case E_ERROR :
			case E_PARSE :
			case E_CORE_ERROR :
			case E_COMPILE_ERROR :
				$result = array(
					'err' => sprintf('%s in %s on line %s', $error['message'], $error['file'], $error['line']),
					'type' => 'fatal',
					'code' => 1000,
					'packet' => $packet->getClear()
				);

				$this->send($result, $fd);
				$this->monitor($begin, $end, $packet, $reqTime, $result, $fd);
				$this->serv->close($fd);
				break;
		}
	}

	/**
	 * @description manager 启动回调
	 *
	 * @param Swoole\Server $serv
	 *
	 * @return null
	 */
    public function managerStart($serv)
    {
        ko_change_process_name($this->conf['name'] . ' master');
    }

	/**
	 * @description worker 启动回调
	 *
	 * @param Swoole\Server $serv
	 *
	 * @param int $workerId
	 *
	 * @return null
	 */
    public function workerStart($serv, $workerId)
    {
        ko_change_process_name($this->conf['name'] . ' worker');

		if (!isset($this->events['initPool'])) {
			return;
		}

		try {
			call_user_func($this->events['initPool'], $this);
		} catch (\Exception $e) {
			if ($this->isRunDocker) {
				Logger::writeExceptionLog(__LINE__, __FILE__, $e);
			} else {
				echo $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
			}
		} catch (\Throwable $e) {
			if ($this->isRunDocker) {
				Logger::writeExceptionLog(__LINE__, __FILE__, $e);
			} else {
				echo $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
			}
		}
    }

	/**
	 * @description 添加事件
	 *
	 * @param string $events
	 *
	 * @param callable $cal
	 *
	 * @return Server
	 */
	public function on($event, $call)
	{
		if (!isset($this->allowEevents[$event])) {
			return $this;
		}

		if (!is_callable($call)) {
			return $this;
		}

		$this->events[$event] = $call;
		return $this;
	}

	/**
	 * @description 管道事件回调
	 *
	 * @param Swoole\Server $serv
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
			if (!isset($this->events['pipeMessage'])) {
				return;
			}

			call_user_func($this->events['pipeMessage'], $data['p'] ?? '', $data['m'] ?? '', $data['a'] ?? array());
        } catch (\Throwable $e) {
			if ($this->isRunDocker) {
				Logger::writeExceptionLog(__LINE__, __FILE__, $e);
			} else {
				echo $e->getMessage() . PHP_EOL .
					$e->getTraceAsString() . PHP_EOL;
			}
		} catch (\Exception $e) {
			if ($this->isRunDocker) {
				Logger::writeExceptionLog(__LINE__, __FILE__, $e);
			} else {
				echo $e->getMessage() . PHP_EOL .
					$e->getTraceAsString() . PHP_EOL;
			}
		}
    }

	/**
	 * @description 链接回调
	 *
	 * @param Swoole\Server $serv
	 *
	 * @param int $fd
	 *
	 * @return null
	 */
    public function connect($serv, $fd)
    {
    }

	/**
	 * @description 接收回调
	 *
	 * @param Swoole\Server $serv
	 *
	 * @param int $fd
	 *
	 * @param int $reactor_id
	 *
	 * @param mixed $data
	 *
	 * @return null
	 */
    public function receive($serv, $fd, $reactor_id, $data)
    {
		$proto = new Json($data, $this->conf['secret_key'], $this->conf['encrypt_type'] ?? 'aes');
		if (!$proto->parse()) {
			$this->send(array(
				'err' => 'parse data error',
				'type' => 'exception',
				'code' => 1000,
				'packet' => $data
			), $fd);
            $serv->close($fd);
			return;
		}

        $this->handler($proto, $fd);

        $serv->close($fd);
    }

	/**
	 * @description Hander 处理
	 *
	 * @param ProtocolInterface $packet
	 *
	 * @param int $fd
	 *
	 * @return null
	 */
    private function handler(ProtocolInterface $packet, $fd)
    {
		$begin = microtime(true);
		$reqTime = time();
		$result = null;

        try {
			if (!isset($this->events['handler'])) {
				$this->send(array(
					'err' => 'handler events is not register',
					'type' => 'exception',
					'code' => 1000,
					'packet' => $packet->getClear()
				), $fd);
				return;
			}

			register_shutdown_function(array($this, 'handleFatal'), $fd, $packet, $begin, $reqTime);

			$result = call_user_func($this->events['handler'], $packet->getPath(), $packet->getMethod(), $packet->getArgs());
			if ($result['code'] > 0) {
				$result['packet'] = $packet->getClear();
			}
		} catch (BusiException $e) {
            $result = array(
                'err' => $e->getMessage(),
                'type' => 'busi_exception',
                'code' => $e->getCode(),
                'packet' => $packet->getClear()
            );
        } catch (\Exception $e) {
			if ($this->isRunDocker) {
				Logger::writeExceptionLog(__LINE__, __FILE__, $e);
			} else {
				echo $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
			}
            $result = array(
                'err' => $e->getMessage() . PHP_EOL . $e->getTraceAsString(),
                'type' => 'exception',
                'code' => 1000,
                'packet' => $packet->getClear()
            );
        } catch (\Throwable $e) {
			if ($this->isRunDocker) {
				Logger::writeExceptionLog(__LINE__, __FILE__, $e);
			} else {
				echo $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
			}
            $result = array(
                'err' => $e->getMessage() . PHP_EOL . $e->getTraceAsString(),
                'type' => 'exception',
                'code' => 1000,
                'packet' => $packet->getClear()
            );
        }

        $this->send($result, $fd);
		$end = microtime(true);
		$this->monitor($begin, $end, $packet, $reqTime, $result, $fd);
    }

	/**
	 * @description 监控
	 *
	 * @param float $begin
	 *
	 * @param float $end
	 *
	 * @param ProtocolInterface $packet
	 *
	 * @param int $reqTime
	 *
	 * @param Array $result
	 *
	 * @param int $fd
	 *
	 * @return null
	 */
	private function monitor($begin, $end, $packet, $reqTime, $result, $fd)
	{
		if (!isset($this->events['monitor'])) {
			return;
		}

		try {
			call_user_func($this->events['monitor'], array(
				'delay' => round(($end - $begin) * 1000, 2),
				'type' => $result['type'],
				'err' => $result['err'],
				'service' => $this->conf['name'],
				'class' => $packet->getPath(),
				'method' => $packet->getMethod(),
				'args' => $packet->getArgs(),
				'ip' => $this->serv->getClientInfo($fd)['remote_ip'],
				'time' => $reqTime,
				'timestamp' => date('Y-m-d H:i:s', $reqTime),
				'minute' => date('YmdHi', $reqTime),
			));
		} catch (\Exception $e) {
			if ($this->isRunDocker) {
				Logger::writeExceptionLog(__LINE__, __FILE__, $e);
			} else {
				echo $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
			}
		} catch (\Throwable $e) {
			if ($this->isRunDocker) {
				Logger::writeExceptionLog(__LINE__, __FILE__, $e);
			} else {
				echo $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
			}
		}
	}

	/**
	 * @description 发送数据
	 *
	 * @param Array $packet
	 *
	 * @param int $fd
	 *
	 * @return null
	 */
    private function send(Array $packet, $fd)
    {
		$data = Json::pack($packet, $this->conf['secret_key'], $this->conf['encrypt_type'] ?? 'aes');
		if (!$data) {
			return false;
		}

        $this->serv->send($fd, $data);
    }

	/**
	 * @description 关闭链接
	 *
	 * @param Swoole\Server $serv
	 *
	 * @param int $fd
	 *
	 * @return null
	 */
    public function close($serv, $fd)
    {
    }

	/**
	 * @description 启动服务
	 *
	 * @return null
	 */
    public function start()
    {
        $this->serv->start();
    }

	/**
	 * @description 获取底层服务
	 *
	 * @return Swoole\Server
	 */
	public function getServ()
	{
		return $this->serv;
	}
}
