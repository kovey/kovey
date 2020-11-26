<?php
/**
 *
 * @description 传输协议
 *
 * @package     Protocol
 *
 * @time        2019-11-16 18:14:53
 *
 * @author      kovey
 */
namespace Protocol;

use Kovey\Library\Util\Util;
use Google\Protobuf\Internal\Message;
use Protocol\Packet;
use Kovey\Library\Exception\CloseConnectionException;

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
        $body = $packet->serializeToString();
        $header = pack('N', $action) . pack('N', strlen($body));

        return $header . $body;
    }

    /**
     * @description 解包
     *
     * @param string $data
     *
     * @return ProtocolInterface
     *
     * @throws Exception
     */
    public static function unpack(string $data)
    {
        $header = unpack('Na/Nb', substr($data, 0, Packet::HEADER_LENGTH));
        if (empty($header)) {
            throw new CloseConnectionException('unpack packet header error'); 
        }

        $body = substr($data, Packet::BODY_OFFSET, $header['b']);
        if (empty($body)) {
            throw new CloseConnectionException('unpack packet body error'); 
        }

        return new Packet($body, $header['a']);
    }
}
