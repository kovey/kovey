<?php
/**
 * @description Rpc服务端口
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-03-21 20:27:42
 *
 * @file kovey/Kovey/Rpc/Server/Port.php
 *
 */
namespace Kovey\Rpc\Server;

use Kovey\Components\Server\Base;
use Kovey\Rpc\Protocol\Json;
use Kovey\Rpc\Protocol\ProtocolInterface;
use Kovey\Components\Exception\BusiException;
use Kovey\Components\Logger\Logger;

class Port extends Base
{
    /**
     * @description 允许监听的事件
     */
    protected $allowEvents = array(
        'monitor' => 1,
        'handler' => 1
    );

    /**
     * @description 初始化
     *
     * @return mixed
     */
    protected function init()
    {
        $this->port->set(array(
            'open_length_check' => true,
            'package_max_length' => Json::MAX_LENGTH,
            'package_length_type' => Json::PACK_TYPE,
            'package_length_offset' => Json::LENGTH_OFFSET,
            'package_body_offset' => Json::BODY_OFFSET,
        ));

        $this->port->on('connect', array($this, 'connect'));
        $this->port->on('receive', array($this, 'receive'));
        $this->port->on('close', array($this, 'close'));
    }

    /**
     * @description 是否允许监听事件
     *
     * @return bool
     */
    protected function isAllow($event) : bool
    {
        return isset($this->allowEvents[$event]);
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
            Logger::writeExceptionLog(__LINE__, __FILE__, $e);
            $result = array(
                'err' => $e->getMessage() . PHP_EOL . $e->getTraceAsString(),
                'type' => 'exception',
                'code' => 1000,
                'packet' => $packet->getClear()
            );
        } catch (\Throwable $e) {
            Logger::writeExceptionLog(__LINE__, __FILE__, $e);
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
            Logger::writeExceptionLog(__LINE__, __FILE__, $e);
		} catch (\Throwable $e) {
            Logger::writeExceptionLog(__LINE__, __FILE__, $e);
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
}