<?php
/**
 *
 * @description kovey framework 入口文件
 *
 * @time       Tue Sep 24 00:20:59 2019
 *
 * @class      index.php
 *
 * @author     kovey
 */
define('APPLICATION_PATH', __DIR__);

require_once APPLICATION_PATH . '/vendor/kovey.php';
require_once KOVEY_FRAMEWORK_PATH . '/Kovey/Web/App/Bootstrap/Autoload.php';

use Kovey\Web\App\Bootstrap\Autoload;
use Kovey\Components\Logger\Logger;
use Kovey\Config\Manager;
use Kovey\Web\App\Application;
use Kovey\Web\App\Bootstrap\Bootstrap;

$autoload = new Autoload();
$autoload->register();

Manager::init(APPLICATION_PATH . '/conf/');
Manager::parse();

Application::getInstance(Manager::get('framework.app'))
	->checkConfig()
	->registerAutoload($autoload)
	->registerBootstrap(new Bootstrap())
	->bootstrap()
	->run();
