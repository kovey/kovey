<?php
/**
 *
 * @description 整个运用启动前的初始化
 *
 * @package     App\Bootstrap
 *
 * @time        Tue Sep 24 09:00:10 2019
 *
 * @class       vendor/Kovey\Web/App/Bootstrap/Bootstrap.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Bootstrap;

use Kovey\Web\Process;
use Kovey\Config\Manager;
use Kovey\Web\App\Application;
use Kovey\Web\Server\Server;
use Kovey\Components\Process\UserProcess;
use Kovey\Web\App\Http\Request\Request;
use Kovey\Web\App\Http\Request\RequestInterface;
use Kovey\Web\App\Http\Response\Response;
use Kovey\Web\App\Http\Response\ResponseInterface;
use Kovey\Components\Parse\Container;
use Kovey\Components\Middleware\SessionStart;
use Kovey\Web\App\Http\Router\Routers;
use Kovey\Web\App\Http\Router\Router;
use Kovey\Web\App\Http\Router\Route;
use Kovey\Web\App\Http\Router\RouterInterface;
use Kovey\Web\App\Mvc\View\Sample;
use Kovey\Web\App\Mvc\Controller\ControllerInterface;
use Kovey\Web\App\Http\Pipeline\Pipeline;
use Kovey\Components\Logger\Logger;
use Kovey\Components\Logger\Db;
use Kovey\Components\Logger\Monitor;

class Bootstrap
{
	public function __initLogger(Application $app)
	{
		ko_change_process_name(Manager::get('app.process.name') . ' root');
		Logger::setLogPath(Manager::get('server.logger.info'), Manager::get('server.logger.exception'), Manager::get('server.logger.error'), Manager::get('server.logger.warning'));
		Monitor::setLogDir(Manager::get('server.logger.monitor'));
		Db::setLogDir(Manager::get('server.logger.db'));

		if (Manager::get('server.session.type') === 'file') {
			if (!is_dir(Manager::get('server.session.dir'))) {
				mkdir(Manager::get('server.session.dir'), 0777, true);
			}
		}
	}

	public function __initApp(Application $app)
	{
		$app->registerServer(new Server(Manager::get('server.server')))
			->registerContainer(new Container())
			->registerRouters(new Routers())
			->registerMiddleware(new SessionStart())
			->registerUserProcess(new UserProcess(Manager::get('server.server.worker_num')));
	}

	public function __initEvents(Application $app)
	{
		$app->on('request', function ($request) {
				return new Request($request);
			})
			->on('response', function () {
				return new Response();
			})
			->on('view', function (ControllerInterface $con, $template) {
				$con->setView(new Sample($con->getResponse(), $template));
			})
			->on('pipeline', function (RequestInterface $req, ResponseInterface $res, RouterInterface $router) use($app) {
				return (new Pipeline($app->getContainer()))
					->via('handle')
					->send($req, $res)
					->through(array_merge($app->getDefaultMiddlewares(), $router->getMiddlewares()))
					->then(function (RequestInterface $req, ResponseInterface $res) use ($router, $app) {
						return $app->runAction($req, $res, $router);
					});
			});
	}

	public function __initProcess(Application $app)
	{
		$app->registerProcess('config', new Process\Config());

		if (Manager::get('server.session.type') === 'file') {
			$app->registerProcess('session', new Process\ClearSession());
		}
	}

	public function __initCustomBoot(Application $app)
	{
		$bootstrap = $app->getConfig()['boot'] ?? 'application/Bootstrap.php';
		$file = APPLICATION_PATH . '/' . $bootstrap;
		if (!is_file($file)) {
			return $this;
		}

		require_once $file;

		$app->registerCustomBootstrap(new \Bootstrap());
	}

	public function __initRouters(Application $app)
	{
		$path = APPLICATION_PATH . '/' . $app->getConfig()['routers'];
		if (!is_dir($path)) {
			return;
		}

		Route::setApp($app);

		$files = scandir($path);
		foreach ($files as $file) {
			if (substr($file, -3) !== 'php') {
				continue;
			}

			require_once($path . '/' . $file);
		}
	}
}
