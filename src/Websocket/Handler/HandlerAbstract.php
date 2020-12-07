<?php
/**
 * @description 对外接口基类
 *
 * @package
 *
 * @author kovey
 *
 * @time 2019-11-14 22:58:02
 *
 */
namespace Kovey\Websocket\Handler;

abstract class HandlerAbstract
{
    protected $clientIp = '';

    public function setClientIp($clientIp)
    {
        $this->clientIp = $clientIp;
    }
}
