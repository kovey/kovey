<?php
/**
 *
 * @description 
 *
 * @package     
 *
 * @time        2019-11-22 23:31:06
 *
 * @author      kovey
 */
namespace Handler;

use Kovey\Rpc\Handler\HandlerAbstract;

class Hello extends HandlerAbstract
{
    public function world($hello, $world)
    {
		return 'Hello World';
    }
}
