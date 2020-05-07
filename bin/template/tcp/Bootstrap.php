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
use Kovey\Components\Exception\BusiException;
use Protobuf\Error;
use Kovey\Config\Manager;
use Protocol\Protobuf;

class Bootstrap
{
	public function __initRequired($app)
	{
		$app->registerLocalLibPath(APPLICATION_PATH . '/application');
	}

	public function __initOn($app)
	{
		$app->on('protobuf', function ($packet, $action) {
            $messageName = Manager::get('protocol.' . $action . '.class');
			$class = new $messageName();
			$class->mergeFromString($packet);

            return array(
                'handler' => Manager::get('protocol.' . $action . '.handler'),
                'method' => Manager::get('protocol.' . $action . '.method'),
                'message' => $class
            );
		})
		->on('run_handler', function ($handler, $method, $message, $fd, $ip) {
			try {
				return $handler->$method($message, $fd, $ip);
			} catch (BusiException $e) {
                $error = new Error();
                $error->setError($e->getMessage())
                    ->setCode($e->getCode());
                return array(
                    'action' => 500,
                    'message' => $error
                );
			}
        })
	    ->on('error', function ($msg) {
			$error = new Error();
            $error->setError($msg)
                ->setCode(500);
            return array(
                'action' => 500,
                'message' => $error
            );
		})
        ->serverOn('error', function () {
			$error = new Error();
            return $error->setError('Internal Error!')
                ->setCode(500);
        })
        ->serverOn('pack', function ($packet, $action) {
            return Protobuf::pack($packet, $action);
        })
        ->serverOn('unpack', function ($data) {
            return Protobuf::unpack($data);
        });
	}
}
