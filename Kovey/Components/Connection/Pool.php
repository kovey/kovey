<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-04-20 16:32:41
 *
 */
namespace Kovey\Components\Connection;

class Pool
{
    private $pool;

    private $database;

    public function __construct($app, $name, $partition = 0)
    {
        $this->pool = $app->getPool($name, $partition);
        if ($this->pool) {
            $this->database = $this->pool->getDatabase();
        }
    }

    public function getDatabase()
    {
        return $this->database;
    }

    public function __destruct()
    {
        if (!$this->pool
            || empty($this->database)
        ) {
            return;
        }

        $this->pool->put($this->database);
    }
}
