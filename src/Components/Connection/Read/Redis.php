<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-04-20 16:40:05
 *
 */
namespace Kovey\Components\Connection\Read;

use Kovey\Components\Pool\Redis as RD;
use Kovey\Components\Connection\Pool;

class Redis extends Pool
{
    public function __construct($app, $partition = 0)
    {
        parent::__construct($app, RD::getReadName(), $partition);
    }
}
