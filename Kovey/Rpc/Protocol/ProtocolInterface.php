<?php
/**
 *
 * @description 协议接口
 *
 * @package     Protocol
 *
 * @time        2019-11-16 21:18:40
 *
 * @file  /Users/kovey/Documents/php/kovey/rpc/Kovey\Rpc/Protocol/ProtocolInterface.php
 *
 * @author      kovey
 */
namespace Kovey\Rpc\Protocol;

interface ProtocolInterface
{
	public function __construct(string $data, string $key);

	public function parse();

	public function getPath();

	public function getMethod();

	public function getArgs();

	public function getClear();

	public static function pack(Array $packet, string $secretKey);

	public static function unpack(string $data, string $secretKey);
}
