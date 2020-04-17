<?php
/**
 *
 * @description 中间件管道
 *
 * @package     Middleware
 *
 * @time        2019-10-19 12:34:45
 *
 * @file  vendor/Kovey/Components/Middleware/Middleware.php
 *
 * @author      kovey
 */
namespace Kovey\App\Pipeline;

use Kovey\Protocol\ProtocolInterface;
use Kovey\Components\Parse\ContainerInterface;

class Pipeline implements PipelineInterface
{
	/**
	 * @description 请求对象
	 *
	 * @var ProtocolInterface
	 */
	private $request;

	/**
	 * @description 请求方法
	 *
	 * @var string
	 */
	private $method;

	/**
	 * @description 容器
	 *
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * @description 构造函数
	 *
	 * @param ContainerInterface $container
	 *
	 * @return Pipeline
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * @description 发送数据
	 *
	 * @param ProtocolInterface $request
	 *
	 * @return Pipeline
	 */
	public function send(ProtocolInterface $request)
	{
		$this->request = $request;
		return $this;
	}

	/**
	 * @description 设置中间件
	 *
	 * @param Array $middlewares
	 *
	 * @return Pipeline
	 */
	public function through(Array $middlewares)
	{
		$this->middlewares = $middlewares;
		return $this;
	}

	/**
	 * @description 设置方法
	 *
	 * @param string $method
	 *
	 * @return Pipeline
	 */
	public function via(string $method)
	{
		$this->method = $method;
		return $this;
	}

	/**
	 * @description 设置最终调用方法
	 *
	 * @param callable $description
	 *
	 * @return mixed
	 */
	public function then(callable $destination)
	{
		$pipeline = array_reduce(
            array_reverse($this->middlewares), $this->carry(), $this->prepareDestination($destination)
        );

        return $pipeline($this->request);
	}

	/**
	 * @description 中间件处理函数
	 *
	 * @return callable
	 */
	protected function carry()
	{
		return function ($stack, $pipe) {
            return function ($request) use ($stack, $pipe) {
                if (is_callable($pipe)) {
                    return call_user_func($pipe, $request, $stack);
                } elseif (! is_object($pipe)) {
                    list($name, $parameters) = $this->parsePipeString($pipe);

                    $pipe = $this->container->get($name);

                    $parameters = array_merge([$request, $stack], $parameters);
                } else {
                    $parameters = [$request, $stack];
                }

                return $pipe->{$this->method}(...$parameters);
            };
        };
	}

	/**
	 * @description 准备下一次调用
	 *
	 * @param callable $descriptio
	 *
	 * @return callable
	 */
	protected function prepareDestination(callable $destination)
    {
        return function ($request) use ($destination) {
            return call_user_func($destination, $request);
        };
    }

	/**
	 * @description 解析参数
	 *
	 * @param string $pipe
	 *
	 * @return Array
	 */
	protected function parsePipeString($pipe)
	{
		list($name, $parameters) = array_pad(explode(':', $pipe, 2), 2, []);

		if (is_string($parameters)) {
			$parameters = explode(',', $parameters);
		}

		return [$name, $parameters];
	}
}
