<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-04-20 16:42:01
 *
 */
namespace Kovey\Components\Connection\Read;

use Kovey\Components\Pool\Mysql as MQ;
use Kovey\Components\Connection\Pool;

class Mysql extends Pool
{
    public function __construct($app, $partition = 0)
    {
        parent::__construct($app, MQ::getReadName(), $partition);
    }
}
