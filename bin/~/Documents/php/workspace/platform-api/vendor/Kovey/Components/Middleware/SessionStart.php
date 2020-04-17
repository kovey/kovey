<?php
/**
 *
 * @description 简单的开启session中间件
 *
 * @package     
 *
 * @time        2019-10-20 20:15:25
 *
 * @file  vendor/Kovey\Web/Components/Middleware/SessionStart.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Middleware;

use Kovey\Web\App\Http\Session\File;
use Kovey\Web\App\Http\Request\RequestInterface;
use Kovey\Web\App\Http\Response\ResponseInterface;
use Kovey\Config\Manager;

class SessionStart implements MiddlewareInterface
{
	/**
	 * @description 中间件的具体实现
	 *
	 * @param RequestInterface $req
	 *
	 * @param ResponseInterface $res
	 *
	 * @param callable $next
	 */
	public function handle(RequestInterface $req, ResponseInterface $res, callable $next)
	{
		$cookie = $req->getCookie();
		$sessionId = $cookie['kovey_session_id'] ?? '';
		$session = new File(Manager::get('server.session.dir'), $sessionId);
		$res->setCookie('kovey_session_id', $session->getSessionId(), strtotime('+1 Hour'));
		$req->setSession($session);

		return $next($req, $res);
	}
}
