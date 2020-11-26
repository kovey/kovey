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
use Kovey\Connection\Pool\Redis;
use Kovey\Connection\Pool\Mysql;
use Kovey\Library\Config\Manager;
use Kovey\Sharding\Mysql as SM;
use Kovey\Sharding\Redis as SR;
use Kovey\Sharding\Sharding\GlobalIdentify;
use Kovey\Connection\Pool;

class Bootstrap
{
    public function __initRequired($app)
    {
        $app->registerLocalLibPath(APPLICATION_PATH . '/application');
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
            ->on('database', function ($poolName) use ($app) {
                return new Pool($app->getPool($poolName));
            })
            ->on('redis', function ($poolName) use ($app) {
                return new Pool($app->getPool($poolName));
            })
            ->on('shardingDatabase', function ($poolName) use ($app) {
                return new SM(32, function ($shardingKey) use ($app, $poolName) {
                    return $app->getPool($poolName, $shardingKey);
                });
            })
            ->on('shardingRedis', function ($poolName) use ($app) {
                return new SR(32, function ($shardingKey) use ($app, $poolName) {
                    return $app->getPool($poolName, $shardingKey);
                });
            })
            ->on('globalId', function ($dbPool, $redisPool, $table, $field, $primary) use ($app) {
                $gl = new GlobalIdentify((new Pool($app->getPool($redisPool)))->getConnection(), (new Pool($app->getPool($dbPool)))->getConnection());
                $gl->setTableInfo($table, $field, $primary);
                return $gl->getGlobalIdentify();
            });
    }
}
