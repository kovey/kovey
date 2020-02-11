<?php
/**
 *
 * @description 管道
 *
 * @package     PipelineInterface
 *
 * @time        2019-10-20 17:21:08
 *
 * @file  vendor/Kovey/App/Http/Pipeline/PipelineInterface.php
 *
 * @author      kovey
 */
namespace Kovey\App\Pipeline;

use Kovey\Components\Parse\ContainerInterface;
use Kovey\Protocol\ProtocolInterface;

interface PipelineInterface
{
	public function __construct(ContainerInterface $container);

	public function send(ProtocolInterface $request);

	public function through(Array $middlewares);

	public function via(string $method);

	public function then(callable $destination);
}
