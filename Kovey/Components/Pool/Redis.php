<?php
/**
 * @description Redis pool
 *
 * @package
 *
 * @author zhayai
 *
 * @time 2020-05-09 14:45:07
 *
 * @file /Users/zhayai/Documents/php/workspace/kovey/Kovey/Components/Pool/Redis.php
 *
 */
namespace Kovey\Components\Pool;

use Kovey\Components\Cache\Redis as RDS;
use Kovey\Config\Manager;

class Redis implements PoolInterface
{
    const POOL_NAME = 'pool_redis';

    private $pool;

    private $min;

    private $max;

    private $count;

    private $conf;

    private $errors;

    public function __construct(Array $pool, Array $conf)
    {
        $this->min = $pool['min'];
        $this->max = $pool['max'];
        $this->pool = new \chan($this->max);
        $this->conf = $conf;
        $this->errors = array();
        $this->count = 0;
    }

    public function init()
    {
        for ($i = 0; $i < $this->min; $i ++) {
            $redis = new RDS($this->conf);

            if (!$redis->connect()) {
                $this->errors[] = $redis->getError();
                continue;
            }

            $this->put($redis);
            $this->count ++;
        }
    }

    public function isEmpty()
    {
        $this->pool->isEmpty();
    }

    public function put($redis)
    {
        if (empty($redis)) {
            return;
        }

        $this->pool->push($redis);
    }

    public function getDatabase()
    {
        $redis = $this->pool->pop(0.1);
        if ($redis) {
            return $redis;
        }

        if ($this->count >= $this->max) {
            return false;
        }

        $this->errors = array();
        $redis = new RDS($this->conf);
        if (!$redis->connect()) {
            $this->errors[] = $redis->getError();
            return false;
        }

        $this->count ++;
        return $redis;
    }

    public function getErrors() : Array
    {
        return $this->errors;
    }

    public static function getWriteName()
    {
        return self::POOL_NAME . '_write';
    }

    public static function getReadName()
    {
        return self::POOL_NAME . '_read';
    }
}
