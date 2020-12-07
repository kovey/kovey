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
 */
namespace Kovey\Rpc\Service;

use Kovey\Rpc\Protocol\Exception;
use Kovey\Rpc\Client\Client;

abstract class ServiceAbstract
{
    /**
     * @description 客户端链接
     *
     * @var Kovey\Rpc\Client\Client
     */
    private $cli;

    /**
     * @description 配置文件
     *
     * @var Array
     */
    private $conf;

    /**
     * @description 构造
     *
     * @param Array $conf
     *
     * @return ServiceAbstract
     */
    public function __construct(Array $conf)
    {
        $this->cli = new Client($conf);
        $this->conf = $conf;
    }

    /**
     * @description 调用服务端方法
     *
     * @param string $method
     *
     * @param Array $args
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function __call($method, $args)
    {
        if (!$this->cli->connect()) {
            throw new Exception($this->cli->getError(), 1002, 'connect_error');
        }

        if (!$this->cli->send(array(
            'p' => $this->getServiceName(),
            'm' => $method,
            'a' => $args,
            't' => isset($this->traceId) ? $this->traceId : '',
            'f' => $this->getCurrentServiceName()
        ))) {
            $this->cli->close();
            throw new Exception($this->cli->getError(), 1003, 'send_error');
        }

        $result = $this->cli->recv();
        $this->cli->close();
        if (empty($result)) {
            throw new Exception('resopone is error.', 1000, 'requset_error');
        }

        if ($result['type'] !== 'success') {
            throw new Exception($result['err'], $result['code'], $result['type'], $result['trace'] ?? '');
        }

        return $result['result'];
    }

    /**
     * @description 获取服务名称
     *
     * @return string
     */
    abstract protected function getServiceName();

    /**
     * @description 获取当前服务名称
     *
     * @return string
     */
    abstract protected function getCurrentServiceName();
}
