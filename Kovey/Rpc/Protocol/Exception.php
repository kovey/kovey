<?php
/**
 *
 * @description 
 *
 * @package     
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
	private $errorType;

	public function __construct($msg, $code, $type)
	{
		$this->errorType = $type;

		parent::__construct($msg, $code);
	}

	public function getErrorType()
	{
		return $this->errorType;
	}
}
