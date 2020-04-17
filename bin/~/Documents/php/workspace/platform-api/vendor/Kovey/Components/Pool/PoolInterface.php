<?php
/**
 *
 * @description 连接池接口
 * 连接池
 * swoole中，在work中使用连接池，每个进程是不共享的
 * 因为协程的channel不共享
 *
 * @package     Components\Pool
 *
 * @time        Tue Sep 24 09:06:42 2019
 *
 * @class       vendor/Kovey/Components/Pool/PoolInterface.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Pool;

interface PoolInterface
{
	/**
	 * @description 初始化连接池
	 *
	 * @return null
	 */
	public function init();

	/**
	 * @description 检测连接池是否为空
	 *
	 * @return bool
	 */
	public function isEmpty();

	/**
	 * @description 放回连接池
	 *
	 * @return null
	 */
	public function put($db);

	/**
	 * @description 从连接池中获取连接
	 *
	 * @return mixed
	 */
	public function getDatabase();

	/**
	 * @description 获取错误
	 *
	 * @return Array
	 */
	public function getErrors();
}
