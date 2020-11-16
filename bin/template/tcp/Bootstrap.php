<?php
/**
 *
 * @description 初始化
 *
 * @package     
 *
 * @time        2019-11-16 22:42:00
 *
 * @author      kovey
 */
use Kovey\Library\Exception\BusiException;
use Protobuf\Error;
use Kovey\Library\Config\Manager;
use Protocol\Protobuf;
use Kovey\Connection\Pool\Redis;
use Kovey\Connection\Pool\Mysql;
use Kovey\Sharding\Mysql as SM;
use Kovey\Sharding\Redis as SR;
use Kovey\Sharding\Sharding\GlobalIdentify;

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

    public function __initPool($app)
    {
        $pool = Manager::get('redis.pool');
        if (!is_array($pool) || empty($pool)) {
            return;
        }

        $configs = Manager::get('redis.write');
        if (is_array($pool) && !empty($pool)) {
            if (!empty($configs)) {
                foreach ($configs as $name => $conf) {
                    if (!is_array($conf) || empty($conf)) {
                        continue;
                    }

                    $app->registerPool(Redis::getWriteName(), new Redis($pool, $conf), $name);
                }
            }
        }

        $configs = Manager::get('redis.read');
        if (is_array($pool) && !empty($pool)) {
            if (!empty($configs)) {
                foreach ($configs as $name => $conf) {
                    if (!is_array($conf) || empty($conf)) {
                        continue;
                    }

                    $app->registerPool(Redis::getReadName(), new Redis($pool, $conf), $name);
                }
            }
        }

        $pool = Manager::get('db.pool');
        if (!is_array($pool) || empty($pool)) {
            return;
        }
        $configs = Manager::get('db.write');
        if (!empty($configs)) {
            foreach ($configs as $name => $conf) {
                if (!is_array($conf) || empty($conf)) {
                    continue;
                }

                $app->registerPool(Mysql::getWriteName(), new Mysql($pool, $conf), $name);
            }
        }

        $configs = Manager::get('db.read');
        if (!empty($configs)) {
            foreach ($configs as $name => $conf) {
                if (!is_array($conf) || empty($conf)) {
                    continue;
                }

                $app->registerPool(Mysql::getReadName(), new Mysql($pool, $conf), $name);
            }
        }
    }

    public function __initEvents($app)
    {
        $app->getContainer()
            ->on('Database', function ($poolName) use ($app) {
                return $app->getPool($poolName)->getConnection();
            })
            ->on('Redis', function ($poolName) use ($app) {
                return $app->getPool($poolName)->getConnection();
            })
            ->on('ShardingDatabase', function ($poolName) use ($app) {
                return new SM(32, function ($shardingKey) use ($app, $poolName) {
                    return $app->getPool($poolName, $shardingKey);
                });
            })
            ->on('ShardingRedis', function ($poolName) use ($app) {
                return new SR(32, function ($shardingKey) use ($app, $poolName) {
                    return $app->getPool($poolName, $shardingKey);
                });
            })
            ->on('GlobalId', function ($dbPool, $redisPool, $table, $field, $primary) use ($app) {
                $gl = new GlobalIdentify($app->getPool($redisPool)->getConnection(), $app->getPool($dbPool)->getConnection());
                $gl->setTableInfo($table, $field, $primary);
                return $gl->getGlobalIdentify();
            });
    }
}
