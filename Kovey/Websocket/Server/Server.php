<?php
/**
 * @description Websocket服务器, 基于protobuf
 *
 * @package Server
 *
 * @author kovey
 *
 * @time 2019-11-13 14:43:19
 *
 * @file kovey/Kovey\Websocket/Server/Server.php
 *
 */
namespace Kovey\Websocket\Server;

use Kovey\Components\Logger\Logger;
use Kovey\Websocket\Protocol\Exception;
use Google\Protobuf\Internal\Message;

class Server
{
	/**
	 * @description 服务器
	 *
	 * @var Swoole\Websocket\Server
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
        $this->serv = new \Swoole\WebSocket\Server($this->conf['host'], $this->conf['port']);
        $this->serv->set(array(
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
            'pack' => 1,
            'unpack' => 1,
            'error' => 1
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
        $this->serv->on('open', array($this, 'open'));
        $this->serv->on('message', array($this, 'message'));
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
	 * @return null
	 *
	 */
	public function handleFatal($fd)
	{
		$end = microtime(true);
		$error = error_get_last();
		switch ($error['type'] ?? null) {
			case E_ERROR :
			case E_PARSE :
			case E_CORE_ERROR :
            case E_COMPILE_ERROR :
                if (isset($this->events['error'])) {
                    $this->send(call_user_func($this->events['error']), 500, $fd);
                }
                Logger::writeErrorLog(__LINE__, __FILE__, sprintf('%s in %s on line %s'), $error['message'], $error['file'], $error['line']);
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
     *
     * @throws Exception
	 */
	public function on($event, $call)
	{
		if (!isset($this->allowEevents[$event])) {
			return $this;
		}

		if (!is_callable($call)) {
            throw new \Exception(sprintf('%s event is not callable', $event), 500);
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
	 * @param Swoole\Http\Request $request
	 *
	 * @return null
	 */
    public function open($serv, $request)
    {
    }

	/**
	 * @description 接收回调
	 *
	 * @param Swoole\Server $serv
	 *
	 * @param int $fd
     *
     * @param Frame $frame
	 *
	 * @return null
	 */
    public function message($serv, $frame)
    {
		if ($frame->opcode != SWOOLE_WEBSOCKET_OPCODE_BINARY) {
			$serv->close($frame->fd);
			return;
		}

        if (!isset($this->events['unpack'])) {
			$serv->close($frame->fd);
            return;
        }

		try {
			$protobuf = call_user_func($this->events['unpack'], $frame->data);
            if (empty($protobuf)) {
                throw new Exception('unpack error', 500, 'unpack_exception');
            }

			$this->handler($protobuf, $frame->fd);
		} catch (Exception $e) {
			$serv->close($frame->fd);
			Logger::writeExceptionLog(__LINE__, __FILE__, $e);
		} catch (\Exception $e) {
			Logger::writeExceptionLog(__LINE__, __FILE__, $e);
		} catch (\Throwable $e) {
			Logger::writeExceptionLog(__LINE__, __FILE__, $e);
		}
    }

	/**
	 * @description Hander 处理
	 *
	 * @param Message $packet
	 *
	 * @param int $fd
	 *
	 * @return null
	 */
    private function handler(Message $packet, $fd)
    {
        try {
			if (!isset($this->events['handler'])) {
				$this->serv->close($fd);
				return;
			}

			register_shutdown_function(array($this, 'handleFatal'), $fd);

			$result = call_user_func($this->events['handler'], $packet, $fd, $this->serv->getClientInfo($fd)['remote_ip']);

            if (!isset($result['message']) || !isset($result['action'])) {
                $this->serv->close($fd);
                return;
            }

			if (!$result['message'] instanceof Message) {
				$this->serv->close($fd);
				return;
			}

			$this->send($result['message'], $result['action'], $fd);
		} catch (\Exception $e) {
			Logger::writeExceptionLog(__LINE__, __FILE__, $e);
        } catch (\Throwable $e) {
			Logger::writeExceptionLog(__LINE__, __FILE__, $e);
        }
    }

	/**
	 * @description 发送数据
	 *
	 * @param Message $packet
	 *
	 * @param int $fd
	 *
	 * @return null
	 */
    private function send(Message $packet, int $action, $fd)
    {
        if (!isset($this->events['pack'])) {
            return;
        }

        $this->serv->push($fd, call_user_func($this->events['pack'], $packet, $action), SWOOLE_WEBSOCKET_OPCODE_BINARY);
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