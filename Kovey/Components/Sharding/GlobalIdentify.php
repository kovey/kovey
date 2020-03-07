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
	/**
	 * @description 缓存
	 *
	 * @var string
	 *
	 */
	const GLOBAL_IDENTIFY_KEY = 'global_indentify_key';

	/**
	 * @description redis
	 *
	 * @var Kovey\Components\Cache\Redis
	 */
	private $redis;

	/**
	 * @description mysql链接
	 *
	 * @var Kovey\Components\Db\Mysql
	 */
	private $mysql;

	/**
	 * @description 表
	 *
	 * @var string
	 */
	private $identifyTable;

	/**
	 * @description 字段
	 *
	 * @var string
	 */
	private $identifyField;

	/**
	 * @description 主键
	 *
	 * @var string
	 */
	private $primaryField;

	/**
	 * @description 构造
	 *
	 * @param Kovey\Components\Cache\Redis $redis
	 *
	 * @param Kovey\Components\Db\Mysql $mysql
	 *
	 * @return GlobalIdentify
	 */
	public function __construct(Redis $redis, Mysql $mysql)
	{
		$this->redis = $redis;
		$this->mysql = $mysql;
	}

	/**
	 * @description 设置表信息
	 *
	 * @param string $identifyTable
	 *
	 * @param string $identifyField
	 *
	 * @param string $primaryField
	 *
	 * @return null
	 */
	public function setTableInfo($identifyTable, $identifyField, $primaryField = 'id')
	{
		$this->identifyField = $identifyField;
		$this->identifyTable = $identifyTable;
		$this->primaryField = $primaryField;
	}

	/**
	 * @description 获取唯一键
	 *
	 * @return int
	 */
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

	/**
	 * @description 重新分配
	 *
	 * @return bool
	 */
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
