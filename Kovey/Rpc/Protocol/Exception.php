<?php
/**
 *
 * @description 协议异常
 *
 * @package     Rpc\Protocol
 *
 * @time        2019-11-16 18:13:16
 *
 * @file  /Users/kovey/Documents/php/kovey/rpc/Kovey\Rpc/Protocol/Exception.php
 *
 * @author      kovey
 */
namespace Kovey\Rpc\Protocol;

class Exception extends \Exception
{
	/**
	 * @description 错误类型
	 *
	 * @var string
	 */
	private $errorType;

	/**
	 * @description 构造函数
	 *
	 * @param string $msg
	 *
	 * @param int $code
	 *
	 * @param string $type
	 *
	 * @return Exception
	 */
	public function __construct($msg, $code, $type)
	{
		$this->errorType = $type;

		parent::__construct($msg, $code);
	}

	/**
	 * @description 获取错误类型
	 *
	 * @return string
	 */
	public function getErrorType()
	{
		return $this->errorType;
	}
}
