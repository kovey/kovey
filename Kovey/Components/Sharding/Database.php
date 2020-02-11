<?php
/**
 *
 * @description 根据ID获取分库名称
 *
 * @package     Components\Sharding
 *
 * @time        Tue Oct  1 00:22:54 2019
 *
 * @class-file  vendor/Kovey/Components/Share/Database.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Sharding;

use Kovey\Util;

class Database
{
	private $maxCount;

	public function __construct()
	{
		$this->maxCount = 128;
	}

	public function getShardingKey($id)
	{
		if (!Util::isNumber($id)) {
			$id = hexdec(hash('crc32', $id));
		} else {
			$id = intval($id);
		}

		return $id % $this->maxCount;
	}
}
