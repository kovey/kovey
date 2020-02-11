<?php
/**
 *
 * @description 中间件接口
 *
 * @package     Middleware
 *
 * @time        2019-10-19 12:32:28
 *
 * @file  vendor/Kovey\Web/Components/Middleware/MiddlewareInterface.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Middleware;

use Kovey\Web\App\Http\Request\RequestInterface;
use Kovey\Web\App\Http\Response\ResponseInterface;

interface MiddlewareInterface
{
	public function handle(RequestInterface $req, ResponseInterface $res, callable $next);
}
