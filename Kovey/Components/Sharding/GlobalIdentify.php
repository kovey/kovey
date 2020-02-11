<?php
/**
 *
 * @description 全局唯一的ID
 *
 * @package     Components\Sharding
 *
 * @time        Tue Oct  1 00:47:28 2019
 *
 * @class-file  vendor/Kovey/Components/Sharding/GlobalIdentify.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Sharding;

use Kovey\Components\Db\Sql\Update;
use Kovey\Components\Cache\Redis;
use Kovey\Components\Db\Mysql;

class GlobalIdentify
{
	const GLOBAL_IDENTIFY_KEY = 'global-indentify-key';

	private $redis;

	private $mysql;

	private $identifyTable;

	private $identifyField;

	private $primaryField;

	public function __construct(Redis $redis, Mysql $mysql)
	{
		$this->redis = $redis;
		$this->mysql = $mysql;
	}

	public function setTableInfo($identifyTable, $identifyField, $primaryField = 'id')
	{
		$this->identifyField = $identifyField;
		$this->identifyTable = $identifyTable;
		$this->primaryField = $primaryField;
	}

	public function getGlobalIdentify()
	{
		$id = $this->redis->lPop(self::GLOBAL_IDENTIFY_KEY);
		if (!$id) {
			if (!$this->giveIdentifiesAgian()) {
				return false;
			}

			$id = $this->redis->lPop(self::GLOBAL_IDENTIFY_KEY);
		}

		return $id;
	}

	private function giveIdentifiesAgian()
	{
		$row = $this->mysql->fetchRow($this->identifyTable, array($this->primaryField => 1), array($this->identifyField));
		if (!$row) {
			return false;
		}

		try {
			$up = new Update($this->identifyTable);
			$up->where(array(
				$this->primaryField => 1,
				$this->identifyField => $row[$this->identifyField]
			))
			->addSelf($this->identifyField, 20000);
			$this->mysql->update($up);
		} catch (\Throwable $e) {
			return false;
		}

		$max = $row[$this->identifyField] + 20000;
		for ($i = $row[$this->identifyField]; $i < $max; $i ++) {
			$this->redis->lPush(self::GLOBAL_IDENTIFY_KEY, $id);
		}

		return true;
	}
}
