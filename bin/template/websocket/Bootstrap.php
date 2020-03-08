<?php
/**
 *
 * @description 初始化
 *
 * @package     
 *
 * @time        2019-11-16 22:42:00
 *
 * @file  /Users/kovey/Documents/php/kovey/rpc/application/Bootstrap.php
 *
 * @author      kovey
 */
use Protobuf\Error;
use Kovey\Components\Exception\BusiException;

class Bootstrap
{
	public function __initRequired($app)
	{
		$app->registerLocalLibPath(APPLICATION_PATH . '/application');
	}

	public function __initOn($app)
	{
		$app->on('error', function ($error) {
			$error = new Error();
			$error->setError($error);
			$error->setHandlerMethod('error');
			$error->setHandler('Error');
			return array(
				'name' => $error->getHandler(),
				'body' => $error->serializeToString()
			);
		})
		->on('protobuf', function ($messageName, $body) {
			$class = new $messageName();
			$class->mergeFromString($body);

			return $class;
		})
		->on('run_handler', function ($handler, $method, $message) {
			try {
				$result = $handler->$method($message);
				if (is_array($result)) {
					return $result;
				}

				return array(
					'name' => $error->getHandler(),
					'body' => $error->serializeToString()
				);
			} catch (BusiException $e) {
				$error = new Error();
				$error->setError($e->getMessage());
				$error->setCode($e->getCode());
				$error->setHandlerMethod('error');
				$error->setHandler('Error');
				return array(
					'name' => $error->getHandler(),
					'body' => $error->serializeToString()
				);
			}
		});
	}
}
