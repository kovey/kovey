<?php
/**
 *
 * @description 
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
	public function init();

	public function isEmpty();

	public function put($db);

	public function getDatabase();

	public function getErrors();
}
