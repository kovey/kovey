<?php
/**
 *
 * @description 整个运用启动前的初始化
 *
 * @package     App\Bootstrap
 *
 * @time        Tue Sep 24 09:00:10 2019
 *
 * @class       vendor/Kovey\Rpc/App/Bootstrap/Bootstrap.php
 *
 * @author      kovey
 */
namespace Kovey\Websocket\App\Bootstrap;

use Kovey\Components\Process;
use Kovey\Config\Manager;
use Kovey\Components\Logger\Logger;
use Kovey\Components\Logger\Monitor;
use Kovey\Components\Logger\Db;
use Kovey\Components\Parse\Container;
use Kovey\Websocket\App\App;
use Kovey\Websocket\Server\Server;
use Kovey\Components\Process\UserProcess;

class Bootstrap
{
	/**
	 * @description 初始化日志
	 *
	 * @param App $app
	 *
	 * @return null
	 */
	public function __initLogger(App $app)
	{
		ko_change_process_name(Manager::get('server.websocket.name') . ' websocket root');
		Logger::setLogPath(Manager::get('server.logger.info'), Manager::get('server.logger.exception'), Manager::get('server.logger.error'), Manager::get('server.logger.warning'));
		Db::setLogDir(Manager::get('server.logger.db'));
		Monitor::setLogDir(Manager::get('server.logger.monitor'));
	}

	/**
	 * @description 初始化APP
	 *
	 * @param App $app
	 *
	 * @return null
	 */
	public function __initApp(App $app)
	{
		$app->registerServer(new Server($app->getConfig()['server']))
			->registerContainer(new Container())
			->registerUserProcess(new UserProcess($app->getConfig()['server']['worker_num']));
	}

	/**
	 * @description 初始化自定义进程
	 *
	 * @param App $app
	 *
	 * @return null
	 */
	public function __initProcess(App $app)
	{
		$app->registerProcess('kovey_config', (new Process\Config())->setProcessName(Manager::get('server.websocket.name') . ' config'));
	}

	/**
	 * @description 初始化自定义的Bootsrap
	 *
	 * @param App $app
	 *
	 * @return null
	 */
	public function __initCustomBoot(App $app)
	{
		$bootstrap = $app->getConfig()['websocket']['boot'] ?? 'application/Bootstrap.php';
		$file = APPLICATION_PATH . '/' . $bootstrap;
		if (!is_file($file)) {
			return;
		}

		require_once $file;

		$app->registerCustomBootstrap(new \Bootstrap());
	}
}
