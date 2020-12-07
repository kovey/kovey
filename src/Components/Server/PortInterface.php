<?php
/**
 * @description 服务器接口
 *
 * @package Kovey\Components\Server
 *
 * @author kovey
 *
 * @time 2020-03-21 18:45:48
 *
 */
namespace Kovey\Components\Server;

interface PortInterface
{
    /**
     * @description 数据包最大长度
     *
     * @var int
     */
    const PACKET_MAX_LENGTH = 2097152;

    /**
     * @description 事件监听
     *
     * @param string $event
     *
     * @param callable $callable
     *
     * @return mixed
     */
    public function on(string $event, $callable) : PortInterface;
}
