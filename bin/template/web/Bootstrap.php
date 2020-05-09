<?php
/**
 *
 * @description bootstrap 
 *
 * @package     web
 *
 * @time       Tue Sep 24 00:23:45 2019
 *
 * @class      application/Bootstrap.php
 *
 * @author     kovey
 */
use Kovey\Components\Pool\Redis;
use Kovey\Components\Pool\Mysql;

class Bootstrap
{
	public function __initLayout($app)
	{
		$app->registerPlugin('Layout');
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
}
