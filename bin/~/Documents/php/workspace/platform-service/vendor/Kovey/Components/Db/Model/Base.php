<?php
/**
 *
 * @description 对应表
 *
 * @package    Kovey\Components\Db\Model 
 *
 * @time        2020-01-19 17:55:12
 *
 * @file  /Users/kovey/Documents/php/kovey/Kovey/Components/Db/Model/Base.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Db\Model;

use Kovey\Components\Db\Sql\Insert;
use Kovey\Components\Db\Sql\Update;
use Kovey\Components\Db\Sql\Delete;
use Kovey\Components\Db\Sql\BatchInsert;
use Kovey\Components\Db\DbInterface;

abstract class Base
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
	 * @return int
	 *
	 * @throws Exception
	 */
	public function insert(Array $data, DbInterface $db)
	{
		$insert = new Insert($this->tableName);
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
	 * @return int
	 *
	 * @throws Exception
	 */
	public function update(Array $data, Array $condition, DbInterface $db)
	{
		$update = new Update($this->tableName);
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
	 * @return Array
	 *
	 * @throws Exception
	 */
	public function fetchRow(Array $condition, Array $columns, DbInterface $db)
	{
		if (empty($columns)) {
			throw new \Exception('selected columns is not empty.'); 
		}

		return $db->fetchRow($this->tableName, $condition, $columns);
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
	 * @return Array
	 *
	 * @throws Exception
	 */
	public function fetchAll(Array $condition, Array $columns, DbInterface $db)
	{
		if (empty($columns)) {
			throw new \Exception('selected columns is not empty.'); 
		}

		return $db->fetchAll($this->tableName, $condition, $columns);
	}

	/**
	 * @description 批量插入
	 *
	 * @param Array $rows
	 *
	 * @param DbInterface $db
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function batchInsert(Array $rows, DbInterface $db)
	{
		if (empty($rows)) {
			throw new \Exception('rows can not empty');
		}

		$batchInsert = new BatchInsert($this->tableName);
		foreach ($rows as $row) {
			$insert = new Insert($this->tableName);
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
	 * @return int
	 *
	 * @throws Exception
	 */
	public function delete(Array $condition, DbInterface $db)
	{
		$delete = new Delete($this->tableName);
		$delete->where($condition);
		return $db->delete($delete);
	}
}
