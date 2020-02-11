<?php
/**
 *
 * @description 中间件管道
 *
 * @package     Middleware
 *
 * @time        2019-10-19 12:34:45
 *
 * @file  vendor/Kovey\Web/Components/Middleware/Middleware.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Http\Pipeline;

use Kovey\Web\App\Http\Request\RequestInterface;
use Kovey\Web\App\Http\Response\ResponseInterface;
use Kovey\Components\Parse\ContainerInterface;

class Pipeline implements PipelineInterface
{
	private $request;

	private $method;

	private $container;

	private $response;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function send(RequestInterface $request, ResponseInterface $response)
	{
		$this->request = $request;
		$this->response = $response;
		return $this;
	}

	public function through(Array $middlewares)
	{
		$this->middlewares = $middlewares;
		return $this;
	}

	public function via(string $method)
	{
		$this->method = $method;
		return $this;
	}

	public function then(callable $destination)
	{
		$pipeline = array_reduce(
            array_reverse($this->middlewares), $this->carry(), $this->prepareDestination($destination)
        );

        return $pipeline($this->request, $this->response);
	}

	protected function carry()
	{
		return function ($stack, $pipe) {
            return function ($request, $response) use ($stack, $pipe) {
                if (is_callable($pipe)) {
                    return call_user_func($pipe, $request, $response, $stack);
                } elseif (! is_object($pipe)) {
                    list($name, $parameters) = $this->parsePipeString($pipe);

                    $pipe = $this->container->get($name);

                    $parameters = array_merge([$request, $response, $stack], $parameters);
                } else {
                    $parameters = [$request, $response, $stack];
                }

                return $pipe->{$this->method}(...$parameters);
            };
        };
	}

	protected function prepareDestination(callable $destination)
    {
        return function ($request, $response) use ($destination) {
            return call_user_func($destination, $request, $response);
        };
    }

	protected function parsePipeString($pipe)
	{
		list($name, $parameters) = array_pad(explode(':', $pipe, 2), 2, []);

		if (is_string($parameters)) {
			$parameters = explode(',', $parameters);
		}

		return [$name, $parameters];
	}
}
