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

use Kovey\Tcp\Handler\HandlerAbstract;
use Protobuf\Hello as PH;

class Hello extends HandlerAbstract
{
    public function world($message)
    {
        $hello = new PH();
        $hello->setHello('hello');
        $hello->setType(5);
        $hello->setWorld('world');
        return array(
            'action' => 1000,
            'message' => $hello
        );
    }
}
