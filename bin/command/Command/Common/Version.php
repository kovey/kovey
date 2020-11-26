<?php
/**
 *
 * @description 版本 
 *
 * @package     
 *
 * @time        2019-12-17 23:40:19
 *
 * @author      kovey
 */
namespace Command\Common;

use Command\CommandInterface;
use Util\Logo;

class Version implements CommandInterface
{
    const KOVEY_FRAMEWORK_VERSION = '2.0';

    public function run()
    {
        Logo::show(self::KOVEY_FRAMEWORK_VERSION);
        exit;
    }
}
