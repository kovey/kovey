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

use Kovey\Rpc\Application;

class Pool
{
    private $pool;

    private $databse;

    public function __construct($name, $partition = 0)
    {
        $this->pool = Application::getInstance()->getPool($name, $partition);
        if ($this->pool) {
            $this->databse = $this->pool->getDatabase();
        }
    }

    public function getDatabse()
    {
        return $this->databse;
    }

    public function __destruct()
    {
        if (!$this->pool
            || empty($this->databse)
        ) {
            return;
        }

        $this->pool->put($this->databse);
    }
}
