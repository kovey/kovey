<?php
/**
 *
 * @description 容器接口
 *
 * @package     Parse
 *
 * @time        2019-10-18 09:15:37
 *
 * @file  vendor/Kovey/Components/Parse/ContainerInterface.php
 *
 * @author      kovey
 */

namespace Kovey\Components\Parse;

interface ContainerInterface
{
    public function get(string $class, ...$args);
}
