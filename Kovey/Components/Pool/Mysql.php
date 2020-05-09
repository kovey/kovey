<?php
/**
 * @description mysql pool
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-05-09 14:41:02
 *
 */
namespace Kovey\Components\Pool;

use Kovey\Components\Db\DbInterface;
use Kovey\Components\Db\Mysql as MSQ;
use Kovey\Config\Manager;

class Mysql implements PoolInterface
{
    /**
     * @description Pool name
     */
    const POOL_NAME = 'pool_mysql';

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
            $db = new MSQ($this->conf);
            if (!$db instanceof DbInterface) {
                $this->errors[] = 'Kovey\Components\Db\Mysql is not implements DbInterface';
                break;
            }

            if (!$db->connect()) {
                $this->errors[] = $db->getError();
                continue;
            }

            $this->put($db);
            $this->count ++;
        }
    }

    public function isEmpty()
    {
        $this->pool->isEmpty();
    }

    public function put($db)
    {
        if (!$db instanceof DbInterface) {
            return;
        }

        $this->pool->push($db);
    }

    public function getDatabase()
    {
        $db = $this->pool->pop(0.1);
        if ($db) {
            return $db;
        }

        if ($this->count >= $this->max) {
            return false;
        }

        $this->errors = array();
        $db = new MSQ($this->conf);
        if (!$db->connect()) {
            $this->errors[] = $db->getError();
            return false;
        }

        $this->count ++;
        return $db;
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
