<?php
/**
 *
 * @description 全局唯一值
 *
 * @package     Components\Uniqid
 *
 * @time        Tue Sep 24 09:13:13 2019
 *
 * @class       vendor/Kovey/Components/Uniqid/Number.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Uniqid;

class Number
{
	/**
	 * @description 唯一值
	 *
	 * @var Swoole\Atomic
	 */
    private $atomic;

	/**
	 * @description 初始值
	 *
	 * @var int
	 */
	const INIT_VALUE = 10000000;

	/**
	 * @description 最大值
	 *
	 * @var int
	 */
	const MAX_VALUE  =  20000000;

	/** 
	 * @description 构造函数
	 *
	 * @return Number
	 */
	public function __construct()
	{
        $this->atomic = new \Swoole\Atomic(self::INIT_VALUE);
	}

	/**
	 * @description 获取值
	 *
	 * @return int
	 */
	public function get()
	{
		$val = $this->atomic->get();
		$this->atomic->add();
		if ($val >= self::MAX_VALUE) {
			$this->atomic->set(self::INIT_VALUE);
            $this->atomic->add();
			$val = self::MAX_VALUE;
		}

		return strval($val);
	}

	/**
	 * @description 订单号
	 *
	 * @return stringl
	 */
	public function getOrderId($size, $pref = '')
	{
		$time = date('YmdHis');
		$start = strlen($pref) + 14 + 8;
		if ($size < $start) {
			return false;
		}

		$id = $pref . $time . $this->get();
		for ($i = $start; $i < $size; $i ++) {
			$id .= strval(random_int(0, 9));
		}

		return $id;
	}
}
