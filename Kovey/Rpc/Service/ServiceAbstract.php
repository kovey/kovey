<?php
/**
 * @description Rpc客户端
 *
 * @package
 *
 * @author kovey
 *
 * @time 2019-11-14 22:22:00
 *
 * @file kovey/Kovey\Rpc/Client/Rpc.php
 *
 */
namespace Kovey\Rpc\Service;

use Kovey\Rpc\Protocol\Exception;
use Kovey\Rpc\Client\Client;

abstract class ServiceAbstract
{
    private $cli;

    private $conf;

    public function __construct(Array $conf)
    {
        $this->cli = new Client($conf);
        $this->conf = $conf;
    }

    public function __call($method, $args)
    {
        if (!$this->cli->connect()) {
			throw new Exception($this->cli->getError(), 1002, 'connect_error');
        }

        if (!$this->cli->send(array(
            'p' => $this->getServiceName(),
            'm' => $method,
            'a' => $args
        ))) {
			throw new Exception($this->cli->getError(), 1003, 'send_error');
        }

        $result = $this->cli->recv();
        $this->cli->close();
		if (empty($result)) {
			throw new Exception('resopone is error.', 1000, 'requset_error');
		}

		if ($result['type'] !== 'success') {
			throw new Exception($result['err'], $result['code'], $result['type']);
		}

		return $result['result'];
    }

    abstract protected function getServiceName();
}
