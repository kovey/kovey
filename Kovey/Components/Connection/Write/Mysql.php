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
namespace Kovey\Components\Connection\Write;

use Pool\Mysql as MQ;
use Kovey\Components\Connection\Pool;

class Mysql extends Pool
{
    public function __construct($partition = 0)
    {
        parent::__construct(MQ::getWriteName(), $partition);
    }
}
