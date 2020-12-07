<?php
/**
 * @description process cors
 *
 * @package Middleware
 *
 * @author kovey
 *
 * @time 2020-09-30 10:50:57
 *
 */
namespace Kovey\Components\Middleware;

use Kovey\Web\App\Http\Request\RequestInterface;
use Kovey\Web\App\Http\Response\ResponseInterface;

class Cors implements MiddlewareInterface
{
    /**
     * @description handle
     *
     * @param RequestInterface $req
     *
     * @param ResponseInterface $res
     *
     * @param callable $next
     */
    public function handle(RequestInterface $req, ResponseInterface $res, callable $next)
    {
        $req->processCors();
        return $next($req, $res);
    }
}
