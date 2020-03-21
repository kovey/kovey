<?php
/**
 * @description 端口
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-03-21 20:09:02
 *
 * @file kovey/Kovey/Components/Server/Base.php
 *
 */
namespace Kovey\Components\Server;

use Swoole\Server;

abstract class Base implements PortInterface
{
    const TCP_PORT = 1;

    /**
     * @description 服务器
     *
     * @var Swoole\Server
     */
    protected $serv;

    /**
     * @description 端口
     *
     * @var Swoole\Server\Port
     */
    protected $port;

    /**
     * @description 监听的事件
     *
     * @var Array
     */
    protected $events;

    /**
     * @description 配置
     *
     * @var Array
     */
    protected $conf;

    /**
     * @description 构造
     *
     * @param Server $serv
     *
     * @param Array $conf
     *
     * @param int $type
     *
     * @return Base
     */
    final public function __construct(Server $serv, Array $conf, $type = self::TCP_PORT)
    {
        $this->serv = $serv;
        $this->port = $this->serv->listen($conf['host'], $conf['port'], $type == self::TCP_PORT ? SWOOLE_SOCK_TCP : SWOOLE_SOCK_UDP);
        $this->events = array();
        $this->conf = $conf;
        $this->init();
    }

    /**
     * @description 事件监听
     *
     * @param string $event
     *
     * @param callable $callable
     *
     * @return PortInterface
     *
     * @return throws
     */
    public function on(string $event, $callable) : PortInterface
    {
        if (!$this->isAllow($event)) {
            throw new \Exception('unknown event: ' . $event);
        }

        if (!is_callable($callable)) {
            throw new \Exception('callback can not callable');
        }

        $this->events[$event] = $callable;
        return $this;
    }

    /**
     * @description 是否允许监听事件
     *
     * @return bool
     */
    abstract protected function isAllow($event) : bool;

    /**
     * @description 初始化
     *
     * @return mixed
     */
    abstract protected function init();
}
