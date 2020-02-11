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
    private $atomic;

	const INIT_VALUE = 10000000;
	const MAX_VALUE  =  20000000;

	public function __construct()
	{
        $this->atomic = new \Swoole\Atomic(self::INIT_VALUE);
	}

	public function get()
	{
		$val = $this->atomic->get();
		if ($val >= self::MAX_VALUE) {
			$this->atomic->set(self::INIT_VALUE);
			$val = self::MAX_VALUE;
		}

		$this->atomic->add();
		return strval($val);
	}

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
