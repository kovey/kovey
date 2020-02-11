<?php
/**
 *
 * @description 只能是数字
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
	public function handle(RequestInterface $req, ResponseInterface $res, callable $next)
	{
		$userId = $req->getSession()->userId;
		if (empty($userId)) {
			return $res->redirect('/login');
		}

		return $next($req, $res);
	}
}
