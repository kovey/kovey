<?php
/**
 *
 * @description SQL接口
 *
 * @package     Components\Db
 *
 * @time        Tue Sep 24 09:02:25 2019
 *
 * @class       vendor/Kovey/Components/Db/SqlInterface.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Db;

interface SqlInterface
{
	/**
	 * @description 准备好的SQL
	 *
	 * @return string | bool
	 */
	public function getPrepareSql();

	/**
	 * @description 获取绑定的数据
	 *
	 * @return Array
	 */
	public function getBindData();

	/**
	 * @description 格式化sql
	 *
	 * @return string
	 */
	public function toString();
}
