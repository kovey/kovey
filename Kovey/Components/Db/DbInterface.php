<?php
/**
 *
 * @description 数据库接口
 *
 * @package     Components\Db
 *
 * @time        Tue Sep 24 09:03:29 2019
 *
 * @class       vendor/Kovey/Components/Db/DbInterface.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Db;
use Kovey\Components\Db\Sql\Update;
use Kovey\Components\Db\Sql\Insert;
use Kovey\Components\Db\Sql\Select;

interface DbInterface
{
	public function __construct(Array $config);

	public function connect();

	public function getError();

	public function query($sql);

	public function commit();

	public function beginTransaction();

	public function rollBack();

	public function fetchRow($table, Array $condition, Array $columns = array());

	public function fetchAll($table, Array $condition = array(), Array $columns = array());

	public function update(Update $update);

	public function insert(Insert $insert);

	public function select(Select $select, $type = Select::ALL);
}
