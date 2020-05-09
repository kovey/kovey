<?php
/**
 * @description sharding
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-05-09 16:07:46
 *
 */
namespace Kovey\Components\Db\Model;

use Kovey\Components\Db\Sql\Insert;
use Kovey\Components\Db\Sql\Update;
use Kovey\Components\Db\Sql\Delete;
use Kovey\Components\Db\Sql\BatchInsert;
use Kovey\Components\Db\DbInterface;
use Kovey\Components\Sharding\Database;

abstract class ShardingBase
{
	/**
	 * @description 表名称
	 *
	 * @var string
	 */
	protected $tableName;

	/**
	 * @description 插入数据
	 *
	 * @param Array $data
	 *
	 * @param DbInterface $db
     *
     * @param mixed $shardingKey
	 *
	 * @return int
	 *
	 * @throws Exception
	 */
	public function insert(Array $data, DbInterface $db, $shardingKey)
	{
        $shardingKey = $this->getShardingKey($shardingKey);
		$insert = new Insert($this->getTableName($shardingKey));
		foreach ($data as $key => $val) {
			$insert->$key = $val;
		}

		return $db->insert($insert);
	}

	/**
	 * @description 更新数据
	 *
	 * @param Array $data
	 *
	 * @param Array $condition
	 *
	 * @param DbInterface $db
     *
     * @param mixed $shardingKey
	 *
	 * @return int
	 *
	 * @throws Exception
	 */
	public function update(Array $data, Array $condition, DbInterface $db, $shardingKey)
	{
        $shardingKey = $this->getShardingKey($shardingKey);
		$update = new Update($this->getTableName($shardingKey));
		foreach ($data as $key => $val) {
			$update->$key = $val;
		}

		$update->where($condition);
		return $db->update($update);
	}

	/**
	 * @description 获取一行数据
	 *
	 * @param Array $condition
	 *
	 * @param Array $columns
	 *
	 * @param DbInterface $db
     *
     * @param mixed $shardingKey
	 *
	 * @return Array
	 *
	 * @throws Exception
	 */
	public function fetchRow(Array $condition, Array $columns, DbInterface $db, $shardingKey)
	{
		if (empty($columns)) {
			throw new \Exception('selected columns is not empty.'); 
		}

        $shardingKey = $this->getShardingKey($shardingKey);
		return $db->fetchRow($this->getTableName($shardingKey), $condition, $columns);
	}

	/**
	 * @description 获取所有数据
	 *
	 * @param Array $condition
	 *
	 * @param Array  $columns
	 *
	 * @param DbInterface $db
     *
     * @param mixed $shardingKey
	 *
	 * @return Array
	 *
	 * @throws Exception
	 */
	public function fetchAll(Array $condition, Array $columns, DbInterface $db, $shardingKey)
	{
		if (empty($columns)) {
			throw new \Exception('selected columns is not empty.'); 
		}

        $shardingKey = $this->getShardingKey($shardingKey);
		return $db->fetchAll($this->getTableName($shardingKey), $condition, $columns);
	}

	/**
	 * @description 批量插入
	 *
	 * @param Array $rows
	 *
	 * @param DbInterface $db
     *
     * @param mixed $shardingKey
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function batchInsert(Array $rows, DbInterface $db, $shardingKey)
	{
		if (empty($rows)) {
			throw new \Exception('rows can not empty');
		}

        $shardingKey = $this->getShardingKey($shardingKey);
		$batchInsert = new BatchInsert($this->getTableName($shardingKey));
		foreach ($rows as $row) {
			$insert = new Insert($this->getTableName($shardingKey));
			foreach ($row as $key => $val) {
				$insert->$key = $val;
			}

			$batchInsert->add($insert);
		}

		return $db->batchInsert($batchInsert);
	}

	/**
	 * @description 删除数据
	 *
	 * @param Array $data
	 *
	 * @param Array $condition
	 *
	 * @param DbInterface $db
     *
     * @param mixed $shardingKey
	 *
	 * @return int
	 *
	 * @throws Exception
	 */
	public function delete(Array $condition, DbInterface $db, $shardingKey)
	{
        $shardingKey = $this->getShardingKey($shardingKey);
		$delete = new Delete($this->getTableName($shardingKey));
		$delete->where($condition);
		return $db->delete($delete);
	}

    /**
     * @description 获取表名称
     *
     * @param int $shardingKey
     *
     * @return string
     */
    public function getTableName(int $shardingKey = -1)
    {
        if ($shardingKey < 0) {
            return $this->tableName;
        }

        return $this->tableName . '_' . $shardingKey;
    }

    public function getShardingKey($shardingKey)
    {
        if (is_numeric($shardingKey) && $shardingKey < 0) {
            return $shardingKey;
        }

        $database = new Database($this->databaseCount);
        return $database->getShardingKey($shardingKey);
    }
}
