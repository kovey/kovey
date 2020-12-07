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
namespace Kovey\Rpc\Handler;

abstract class HandlerAbstract
{
    protected $clientIp = '';

    public function setClientIp(string $clientIp)
    {
        $this->clientIp = $clientIp;
    }
}
