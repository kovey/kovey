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

use Kovey\Components\Db\DbInterface;

abstract class Base extends ShardingBase
{
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
        return parent::insert($data, $db, -1);
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
        return parent::update($data, $condition, $db, -1);
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
        return parent::fetchRow($condition, $columns, $db, -1);
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
        return parent::fetchAll($condition, $columns, $db, -1);
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
        return parent::batchInsert($rows, $db, -1);
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
        return parent::delete($condition, $db, -1);
	}
}
