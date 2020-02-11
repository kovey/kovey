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

class Client
{
    private $cli;

	private $configs;

	private $conf;

	private $current = 0;

	private $unavailables = array();

	private $error = '';

    public function __construct(Array $configs)
    {
        $this->cli = new \Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
        $this->cli->set(array(
            'open_length_check'     => true,
            'package_length_type'   => Json::PACK_TYPE,
            'package_length_offset' => Json::LENGTH_OFFSET,       //第N个字节是包长度的值
            'package_body_offset'   => Json::BODY_OFFSET,       //第几个字节开始计算长度
            'package_max_length'    => Json::MAX_LENGTH,  //协议最大长度
        ));

        $this->configs = $configs;
    }

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

    public function send(Array $data)
    {
		$data = Json::pack($data, $this->conf['secret_key'], $this->conf['encrypt_type'] ?? 'aes', true);
		if (!$data) {
			return false;
		}
        $result = $this->cli->send($data);
		if (!$result) {
			$this->error = sprintf('send failure to server: %s:%s, error: %s', $this->conf['host'], $this->conf['port'], socket_strerror($this->cli->errCode));
		}

		return $result;
    }

    public function recv()
    {
        $packet = $this->cli->recv();
		if (empty($packet)) {
			return array();
		}

        $packet = Json::unpack($packet, $this->conf['secret_key'], $this->conf['encrypt_type'] ?? 'aes', true);
        if (!is_array($packet)) {
            return array();
        }

        return $packet;
    }

    public function getError()
    {
		return $this->error;
    }

    public function close()
    {
        $this->cli->close();
    }
}
