<?php
/**
 *
 * @description 传输协议
 *
 * @package     Protocol
 *
 * @time        2019-11-16 18:14:53
 *
 * @file  /Users/kovey/Documents/php/kovey/rpc/Kovey\Rpc/Protocol/Json.php
 *
 * @author      kovey
 */
namespace Kovey\Rpc\Protocol;

use Kovey\Util\Util;
use Kovey\Rpc\Encryption\Encryption;

class Json implements ProtocolInterface
{
	const PACK_TYPE = 'N';

	const HEADER_LENGTH = 4;

	const MAX_LENGTH = 81920;

	const LENGTH_OFFSET = 0;

	const BODY_OFFSET = 4;

	private $path;

	private $method;

	private $args;

	private $body;

	private $secretKey;

	private $clear;

	private $encryptType;

	private $isPub;

	public function __construct(string $body, string $key, string $type = 'aes', bool $isPub = false)
	{
		$this->body = $body;
		$this->secretKey = $key;
		$this->encryptType = $type;
		$this->isPub = $isPub;
	}

	public function parse()
	{
		$this->clear = self::unpack($this->body, $this->secretKey, $this->encryptType, $this->isPub);
		if (empty($this->clear)) {
			return false;
		}

        if (!is_array($this->clear)) {
            return false;
        }

        if (!isset($this->clear['p'])
			|| !isset($this->clear['m'])
			|| empty($this->clear['p'])
			|| empty($this->clear['m'])
        ) {
            return false;
		}

		if (isset($this->clear['a']) && !is_array($this->clear['a'])) {
			return false;
		}

		$this->path  = $this->clear['p'];
		$this->method = $this->clear['m'];
		$this->args = $this->clear['a'] ?? array();

		return true;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function getMethod()
	{
		return $this->method;
	}

	public function getArgs()
	{
		return $this->args;
	}

	public function getClear()
	{
		return $this->clear;
	}

	public static function pack(Array $packet, string $secretKey, $type = 'aes', $isPub = false)
	{
        $data = Encryption::encrypt(json_encode($packet), $secretKey, $type, $isPub);
        if (!$data) {
            return false;
        }

        return pack(self::PACK_TYPE, strlen($data)) . $data;
	}

	public static function unpack(string $data, string $secretKey, $type = 'aes', $isPub = false)
	{
        $info = unpack(self::PACK_TYPE, substr($data, self::LENGTH_OFFSET, self::HEADER_LENGTH));
        $length = $info[1] ?? 0;

        if (!Util::isNumber($length) || $length < 1) {
            return false;
        }

        $encrypt = substr($data, self::BODY_OFFSET, $length);
        $packet = Encryption::decrypt($encrypt, $secretKey, $type, $isPub);
		if (!$packet) {
			return false;
		}

        return json_decode($packet, true);
	}
}
