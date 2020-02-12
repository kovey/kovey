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

class Server
{
    private $serv;

    private $conf;

	private $events;

	private $allowEevents;

    public function __construct(Array $conf)
    {
        $this->conf = $conf;
        $this->serv = new \Swoole\Server($this->conf['host'], $this->conf['port']);
        $this->serv->set(array(
            'open_length_check' => true,
            'package_max_length' => Json::MAX_LENGTH,
            'package_length_type' => Json::PACK_TYPE,
            'package_length_offset' => Json::LENGTH_OFFSET,
            'package_body_offset' => Json::BODY_OFFSET,
			'enable_coroutine' => true,
			'worker_num' => $this->conf['worker_num'],
            'daemonize' => true,
            'pid_file' => $this->conf['pid_file'],
            'log_file' => $this->conf['log_file'],
        ));


		$logDir = dirname($this->conf['log_file']);
		if (!is_dir($logDir)) {
			mkdir($logDir, 0777, true);
		}

		$this->initAllowEvents()
			->initCallback();
    }

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

    public function managerStart($serv)
    {
        ko_change_process_name($this->conf['name'] . ' master');
    }

    public function workerStart($serv, $workerId)
    {
        ko_change_process_name($this->conf['name'] . ' worker');

		if (!isset($this->events['initPool'])) {
			return;
		}

		try {
			call_user_func($this->events['initPool'], $this);
		} catch (\Exception $e) {
			echo $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
		} catch (\Throwable $e) {
			echo $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
		}
    }

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

    public function pipeMessage($serv, $workerId, $data)
    {
        try {
			if (!isset($this->events['pipeMessage'])) {
				return;
			}

			call_user_func($this->events['pipeMessage'], $data['p'] ?? '', $data['m'] ?? '', $data['a'] ?? array());
        } catch (\Throwable $e) {
			echo $e->getMessage() . "\n" .
				$e->getTraceAsString() . "\n";
		} catch (\Exception $e) {
			echo $e->getMessage() . "\n" .
				$e->getTraceAsString() . "\n";
		}
    }

    public function connect($serv, $fd)
    {
    }

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
            $result = array(
                'err' => $e->getMessage() . "\n" . $e->getTraceAsString(),
                'type' => 'exception',
                'code' => 1000,
                'packet' => $packet->getClear()
            );
        } catch (\Throwable $e) {
			echo $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
            $result = array(
                'err' => $e->getMessage() . "\n" . $e->getTraceAsString(),
                'type' => 'exception',
                'code' => 1000,
                'packet' => $packet->getClear()
            );
        }

        $this->send($result, $fd);
		$end = microtime(true);
		$this->monitor($begin, $end, $packet, $reqTime, $result, $fd);
    }

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
			echo $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
		} catch (\Throwable $e) {
			echo $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
		}
	}

    private function send(Array $packet, $fd)
    {
		$data = Json::pack($packet, $this->conf['secret_key'], $this->conf['encrypt_type'] ?? 'aes');
		if (!$data) {
			return false;
		}

        $this->serv->send($fd, $data);
    }

    public function close($serv, $fd)
    {
    }

    public function start()
    {
        $this->serv->start();
    }

	public function getServ()
	{
		return $this->serv;
	}
}
