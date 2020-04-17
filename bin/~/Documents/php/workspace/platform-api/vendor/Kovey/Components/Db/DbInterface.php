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
	/**
	 * @description 构造接口
	 *
	 * @param Array $config
	 */
	public function __construct(Array $config);

	/**
	 * @description 连接服务器
	 *
	 * @return bool
	 */
	public function connect() : bool;

	/**
	 * @description 获取错误信息
	 *
	 * @return string
	 */
	public function getError() : string;

	/**
	 * @description 查询数据
	 *
	 * @return mixed
	 */
	public function query($sql);

	/**
	 * @description 事务提交
	 *
	 * @return null
	 */
	public function commit();

	/**
	 * @description 开启事务
	 *
	 * @return bool
	 */
	public function beginTransaction() : bool;

	/**
	 * @description 撤销事务
	 *
	 * @return null
	 */
	public function rollBack();

	/**
	 * @description 获取一行
	 *
	 * @param string $table
	 *
	 * @param Array $condition
	 *
	 * @param Array $columns
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 */
	public function fetchRow($table, Array $condition, Array $columns = array());

	/**
	 * @description 获取所有行
	 *
	 * @param string $table
	 *
	 * @param Array $condition
	 *
	 * @param Array $columns
	 *
	 * @return Array
	 *
	 * @throws Exception
	 */
	public function fetchAll($table, Array $condition = array(), Array $columns = array()) : array;

	/**
	 * @description 更新
	 *
	 * @param Update $update
	 *
	 * @return mixed
	 */
	public function update(Update $update);

	/**
	 * @description 插入
	 *
	 * @param Insert $insert
	 *
	 * @return mixed
	 */
	public function insert(Insert $insert);

	/**
	 * @description 查询
	 *
	 * @param Select $select
	 *
	 * @param int $type
	 *
	 * @return mixed
	 */
	public function select(Select $select, $type = Select::ALL);
}
