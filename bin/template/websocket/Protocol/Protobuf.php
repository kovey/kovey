<?php
/**
 *
 * @description 传输协议
 *
 * @package     Protocol
 *
 * @time        2019-11-16 18:14:53
 *
 * @file  /Users/kovey/Documents/php/kovey/websocket/Kovey\Rpc/Protocol/Json.php
 *
 * @author      kovey
 */
namespace Protocol;

use Kovey\Util\Util;
use Protobuf\BaseMessage;
use Google\Protobuf\Internal\Message;

class Protobuf
{
    /**
     * @description 打包
     *
     * @param Protobuf $packet
     *
     * @param int $action
     *
     * @return string
     */
    public static function pack(Message $packet, int $action)
    {
        $base = new BaseMessage();
        $base->setAction($action)
            ->setData($packet->serializeToString());
        return $base->serializeToString();
    }

    /**
     * @description 解包
     *
     * @param string $data
     *
     * @return BaseMessage
     *
     * @throws Exception
     */
    public static function unpack(string $data)
    {
        $base = new BaseMessage();
        $base->mergeFromString($data);
        if (empty($base->getAction())
            || empty($base->getData())
        ) {
            throw new Exception('protocol format error', 1001, 'unpack_exception');
        }

        return $base;
    }
}
