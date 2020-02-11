<?php
/**
 *
 * @description 管道
 *
 * @package     PipelineInterface
 *
 * @time        2019-10-20 17:21:08
 *
 * @file  vendor/Kovey\Web/App/Http/Pipeline/PipelineInterface.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Http\Pipeline;

use Kovey\Components\Parse\ContainerInterface;
use Kovey\Web\App\Http\Request\RequestInterface;
use Kovey\Web\App\Http\Response\ResponseInterface;

interface PipelineInterface
{
	public function __construct(ContainerInterface $container);

	public function send(RequestInterface $request, ResponseInterface $response);

	public function through(Array $middlewares);

	public function via(string $method);

	public function then(callable $destination);
}
