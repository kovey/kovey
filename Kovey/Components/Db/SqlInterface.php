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
	public function getPrepareSql();

	public function getBindData();

	public function toString();
}
