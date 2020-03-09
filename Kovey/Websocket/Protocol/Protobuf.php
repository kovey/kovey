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
namespace Kovey\Websocket\Protocol;

use Kovey\Util\Util;

class Protobuf
{
	/**
	 * @description 打包类型
	 *
	 * @var string
	 */
	const PACK_NUM_TYPE = 'N';

	/**
	 * @description 字符打包类型
	 *
	 * @var string
	 */
	const PACK_STR_TYPE = 'a*';

	/**
	 * @description MessageName
	 *
	 * @var string
	 */
	private $messageName;

	/**
	 * @description 包体类容
	 *
	 * @var string
	 */
	private $body;

	/**
	 * @description 构造
	 *
	 * @param string $messageName
	 *
	 * @param string $body
	 *
	 * @return Protobuf
	 */
	public function __construct($messageName, $body)
	{
		$this->messageName = $messageName;
		$this->body = $body;
	}

	/**
	 * @description 协议名称
	 *
	 * @return string
	 */
	public function getMessageName()
	{
		return $this->messageName;
	}

	/**
	 * @description 获取BODY
	 *
	 * @return string
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * @description 打包
	 *
	 * @param Protobuf $packet
	 *
	 * @return string | bool
	 */
	public static function pack(Protobuf $packet)
	{
		$msg = pack(self::PACK_STR_TYPE, $packet->getMessageName());

        return pack(self::PACK_NUM_TYPE, strlen($msg)) .  $msg . $packet->getBody();
	}

	/**
	 * @description 解包
	 *
	 * @param string $data
	 *
	 * @return Protobuf
	 *
	 * @throws Exception
	 */
	public static function unpack(string $data)
	{
        $info = unpack(self::PACK_NUM_TYPE, substr($data, 0, 4));
        $length = $info[1] ?? 0;

        if (!Util::isNumber($length) || $length < 1) {
			throw new Exception('packet header format error', 1001, 'unpack_header_error');
        }

        $info = unpack(self::PACK_STR_TYPE, substr($data, 4, $length));
		$messageName = $info[1] ?? '';

		if (empty($messageName)) {
			throw new Exception('packet message name format error', 1002, 'unpack_message_name_error');
		}

		$body = substr($data, 4 + $length);
		if (empty($body)) {
			throw new Exception('packet body format error', 1003, 'unpack_body_error');
		}

		return new self($messageName, $body);
	}
}
