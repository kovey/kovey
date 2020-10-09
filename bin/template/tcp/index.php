<?php
/**
 *
 * @description rpc 入口文件
 *
 * @package     
 *
 * @time        2019-11-16 22:23:08
 *
 * @file  /Users/kovey/Documents/php/kovey/rpc/main/index.php
 *
 * @author      kovey
 */
define('APPLICATION_PATH', __DIR__);

require_once APPLICATION_PATH . '/vendor/autoload.php';

use Kovey\Tcp\App\Bootstrap\Autoload;
use Kovey\Config\Manager;
use Kovey\Tcp\App\App;
use Kovey\Tcp\App\Bootstrap\Bootstrap;

$autoload = new Autoload();
$autoload->register();
Manager::init(APPLICATION_PATH . '/conf');

App::getInstance()
	->setConfig(Manager::get('server'))
	->checkConfig()
	->registerAutoload($autoload)
	->registerBootstrap(new Bootstrap())
	->bootstrap()
	->run();
