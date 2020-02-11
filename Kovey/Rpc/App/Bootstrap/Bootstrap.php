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
namespace Kovey\Rpc\App\Bootstrap;

use Kovey\Rpc\Process;
use Kovey\Config\Manager;
use Kovey\Components\Logger\Logger;
use Kovey\Components\Logger\Monitor;
use Kovey\Components\Logger\Db;
use Kovey\Components\Parse\Container;
use Kovey\Rpc\Application;
use Kovey\Rpc\Server\Server;
use Kovey\Components\Process\UserProcess;

class Bootstrap
{
	public function __initLogger(Application $app)
	{
		ko_change_process_name(Manager::get('server.rpc.name') . ' rpc root');
		Logger::setLogPath(Manager::get('server.logger.info'), Manager::get('server.logger.exception'), Manager::get('server.logger.error'), Manager::get('server.logger.warning'));
		Db::setLogDir(Manager::get('server.logger.db'));
		Monitor::setLogDir(Manager::get('server.logger.monitor'));
	}

	public function __initApp(Application $app)
	{
		$app->registerServer(new Server($app->getConfig()['server']))
			->registerContainer(new Container())
			->registerUserProcess(new UserProcess($app->getConfig()['server']['worker_num']));
	}

	public function __initProcess(Application $app)
	{
		$app->registerProcess('config', new Process\Config());
			//->registerProcess('cron', new Process\Cron())
			//->registerProcess('logger', new Process\Logger());
	}

	public function __initCustomBoot(Application $app)
	{
		$bootstrap = $app->getConfig()['rpc']['boot'] ?? 'application/Bootstrap.php';
		$file = APPLICATION_PATH . '/' . $bootstrap;
		if (!is_file($file)) {
			return;
		}

		require_once $file;

		$app->registerCustomBootstrap(new \Bootstrap());
	}
}
