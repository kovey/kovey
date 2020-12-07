<?php
/**
 * @description
 *
 * @package
 *
 * @author zhayai
 *
 * @time 2020-04-29 17:36:23
 *
 */
namespace Kovey\Tcp\Protobuf;

interface ProtobufInterface
{
    public function getMessage();

    public function getHandler();

    public function getMethod();
}
