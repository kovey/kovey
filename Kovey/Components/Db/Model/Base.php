<?php
/**
 *
 * @description 对应表
 *
 * @package     
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
use Kovey\Components\Db\DbInterface;

abstract class Base
{
	protected $tableName;

	public function insert(Array $data, DbInterface $db)
	{
		$insert = new Insert($this->tableName);
		foreach ($data as $key => $val) {
			$insert->$key = $val;
		}

		return $db->insert($insert);
	}

	public function update(Array $data, Array $condition, DbInterface $db)
	{
		$update = new Update($this->tableName);
		foreach ($data as $key => $val) {
			$update->$key = $val;
		}

		$update->where($condition);
		return $db->update($update);
	}

	public function fetchRow(Array $condition, Array $columns, DbInterface $db)
	{
		if (empty($columns)) {
			throw new \Exception('selected columns is not empty.'); 
		}

		return $db->fetchRow($this->tableName, $condition, $columns);
	}

	public function fetchAll(Array $condition, Array $columns, DbInterface $db)
	{
		if (empty($columns)) {
			throw new \Exception('selected columns is not empty.'); 
		}

		return $db->fetchAll($this->tableName, $condition, $columns);
	}
}
