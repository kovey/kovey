<?php
/**
 *
 * @description 中间件，检测登录, 这只是一个示例
 *
 * @package     Middleware
 *
 * @time        2019-10-20 00:41:53
 *
 * @file  vendor/Kovey\Web/Components/Middleware/Number.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Middleware;

use Kovey\Web\App\Http\Request\RequestInterface;
use Kovey\Web\App\Http\Response\ResponseInterface;
use Kovey\Util\Json;

class CheckLogin implements MiddlewareInterface
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
		$userId = $req->getSession()->userId;
		if (empty($userId)) {
			return $res->redirect('/login');
		}

		return $next($req, $res);
	}
}
