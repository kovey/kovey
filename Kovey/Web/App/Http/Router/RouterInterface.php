<?php
/**
 *
 * @description 
 *
 * @package     
 *
 * @time        2019-10-19 22:09:09
 *
 * @file  vendor/Kovey\Web/App/Http/Router/RouterInterface.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Http\Router;

use Kovey\Components\Middleware\MiddlewareInterface;

interface RouterInterface
{
	public function __construct(string $uri, $fun);

	public function getAction();

	public function getController();

	public function getClassPath();

	public function addMiddleware(MiddlewareInterface $middleware);

	public function getMiddlewares();

	public function isValid();

	public function getClassName();

	public function getActionName();

	public function getViewPath();

	public function getCallable();

	public function getUri();
}	
