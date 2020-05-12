<?php
/**
 * @description 客户端
 *
 * @package
 *
 * @author kovey
 *
 * @time 2019-11-14 20:02:44
 *
 * @file kovey/Kovey\Rpc/Client/Client.php
 *
 */
namespace Kovey\Rpc\Client;

use Kovey\Rpc\Protocol\Json;
use Kovey\Rpc\Protocol\ProtocolInterface;

class Client
{
    const PACKET_MAX_LENGTH = 2097152;

    const TIME_OUT = 30;

    /**
     * @description 事件监听
     *
     * @var Array
     */
    private $events;

	/**
	 * @description 底层客户端
	 *
	 * @var Swoole\Coroutine\Client
	 */
    private $cli;

	/**
	 * @description 服务端配置
	 *
	 * @var Array
	 */
	private $configs;

	/**
	 * @description 客户端配置
	 *
	 * @var Array
	 */
	private $conf;

	/**
	 * @description 当前使用配置
	 *
	 * @var int
	 */
	private $current = 0;

	/**
	 * @description 不可用的配置
	 *
	 * @var Array
	 */
	private $unavailables = array();

	/**
	 * @description 错误信息
	 *
	 * @var string
	 */
	private $error = '';

	/**
	 * @description 构造函数
	 *
	 * @param Array $configs
	 *
	 * @return Client
	 */
    public function __construct(Array $configs)
    {
        $this->cli = new \Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
        $this->cli->set(array(
            'open_length_check'     => true,
            'package_length_type'   => ProtocolInterface::PACK_TYPE,
            'package_length_offset' => ProtocolInterface::LENGTH_OFFSET,       //第N个字节是包长度的值
            'package_body_offset'   => ProtocolInterface::BODY_OFFSET,       //第几个字节开始计算长度
            'package_max_length'    => self::PACKET_MAX_LENGTH,  //协议最大长度
        ));

        $this->configs = $configs;
        $this->events = array();
    }

    /**
     * @description 事件监听
     *
     * @param string $event
     *
     * @param callable $callable
     */
    public function on(string $event, $callable)
    {
        if (!is_callable($callable)) {
            return $this;
        }

        $this->events[$event] = $callable;
        return $this;
    }

	/**
	 * @description 链接服务器端
	 *
	 * @return bool
	 */
    public function connect()
    {
		$count = 0;
		do {
			$count ++;
			$conf = $this->getConf();
			if (empty($conf)) {
				$this->error = 'connected failure to server, available config not found';
				return false;
			}

			$this->conf = $conf;
			$result = $this->cli->connect($this->conf['host'], $this->conf['port']);
			if ($result || intval($this->cli->errCode) == 0) {
				return true;
			}

			$this->error = sprintf('connected failure to server: %s:%s,error: %s', $this->conf['host'], $this->conf['port'], socket_strerror($this->cli->errCode));
			$this->unavailables[$this->current] = 1;
		} while ($count < 3);

		return false;
    }

	/**
	 * @description 获取可用的服务端配置
	 *
	 * @return Array
	 */
	private function getConf()
	{
		$this->current = array_rand($this->configs, 1);
		if (!isset($this->unavailables[$this->current])) {
			return $this->configs[$this->current];
		}

		foreach ($this->configs as $index => $conf) {
			if (isset($this->unavailables[$index])) {
				continue;
			}

			$this->current = $index;
			return $conf;
		}

		return false;
	}

	/**
	 * @description 向服务端发送数据
	 *
	 * @param Array $data
	 *
	 * @return bool
	 */
    public function send(Array $data)
    {
        if (isset($this->events['pack'])) {
            $data = call_user_func($this->events['pack'], $data, $this->conf['secret_key'], $this->conf['encrypt_type'] ?? 'aes', true);
        } else {
            $data = Json::pack($data, $this->conf['secret_key'], $this->conf['encrypt_type'] ?? 'aes', true);
        }

		if (!$data) {
			return false;
		}
        $result = $this->cli->send($data);
		if (!$result) {
			$this->error = sprintf('send failure to server: %s:%s, error: %s', $this->conf['host'], $this->conf['port'], socket_strerror($this->cli->errCode));
		}

		return $result;
    }

	/**
	 * @description 接收数据
	 *
	 * @return Array
	 */
    public function recv()
    {
        $packet = $this->cli->recv(self::TIME_OUT);
		if (empty($packet)) {
			return array();
		}

        if (isset($this->events['unpack'])) {
            $packet = call_user_func($this->events['unpack'], $this->conf['secret_key'], $this->conf['encrypt_type'] ?? 'aes', true);
        } else {
            $packet = Json::unpack($packet, $this->conf['secret_key'], $this->conf['encrypt_type'] ?? 'aes', true);
        }

        if (!is_array($packet)) {
            return array();
        }

        return $packet;
    }

	/**
	 * @description 获取错误信息
	 *
	 * @return string
	 */
    public function getError()
    {
		return $this->error;
    }

	/**
	 * @description 关闭链接
	 *
	 * @return null
	 */
    public function close()
    {
        $this->cli->close();
    }
}
