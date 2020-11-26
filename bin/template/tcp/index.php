<?php
/**
 *
 * @description rpc 入口文件
 *
 * @package     
 *
 * @time        2019-11-16 22:23:08
 *
 * @author      kovey
 */
define('APPLICATION_PATH', __DIR__);

require_once APPLICATION_PATH . '/vendor/autoload.php';

use Kovey\Tcp\App\Bootstrap\Autoload;
use Kovey\Library\Config\Manager;
use Kovey\Tcp\App\App;
use Kovey\Tcp\App\Bootstrap\Bootstrap;
use Swoole\Coroutine;

Coroutine::set(array('hook_flags' => SWOOLE_HOOK_ALL));

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
