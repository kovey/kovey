<?php
/**
 *
 * @description 容器接口
 *
 * @package     Parse
 *
 * @time        2019-10-18 09:15:37
 *
 * @author      kovey
 */

namespace Kovey\Components\Parse;

interface ContainerInterface
{
    /**
     * @description 获取实例
     *
     * @param string $class
     *
     * @param ...mixed $args
     *
     * @return mixed
     */
    public function get(string $class, ...$args);
}
